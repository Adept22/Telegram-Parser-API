import signal
import logging
import os
import asyncio
import json
import sys
import celery
from enum import Enum, auto
import psycopg2
import psycopg2.extensions


class Action(Enum):
    insert = auto()
    update = auto()
    delete = auto()


class Table(Enum):
    links = auto()
    chats_tasks = auto()
    phones = auto()
    phones_tasks = auto()


class TaskStatus(Enum):
    created = 0
    started = 1
    success = 2
    failure = 3
    revoked = 4


class ChatTaskType(Enum):
    task_member = 0
    task_message = 1
    task_monitoring = 2
    task_chat_media = 3


class PhoneTaskType(Enum):
    task_auth = 0


class PGNotify:
    class Payload:
        def __init__(self, table, action, schema, record, data):
            self.table = Table[table]
            self.action = Action[action]
            self.schema = schema
            self.record = record
            self.data = data

    def __init__(self) -> None:
        self.logger = self._init_logger()

        self.logger.info("Initialization PGNotify service instance")

        self.loop = asyncio.new_event_loop()
        self.loop.set_exception_handler(self._ex_handler)

        signal.signal(signal.SIGTERM, self._handle_sig)
        signal.signal(signal.SIGINT, self._handle_sig)

        self.settings, self.models = self._init_django()

        self.connection = self._init_db_connection()
        self.app = self._init_celery_app()

        self.logger.info("PGNotify instance created")

    def _init_logger(self):
        logger = logging.getLogger(__name__)
        logger.setLevel(logging.DEBUG)

        formatter = logging.Formatter("%(asctime)s %(levelname)s %(message)s")

        stdout_handler = logging.StreamHandler(sys.stdout)
        stdout_handler.setLevel(logging.INFO)
        stdout_handler.setFormatter(formatter)

        logger.addHandler(stdout_handler)

        return logger

    def _handle_sig(self, sig, frame):
        self.logger.warning(f"{signal.Signals(sig).name} received...")

        self.stop()

    def _init_django(self):
        import django

        self.logger.info("Django setup...")

        os.environ["DJANGO_SETTINGS_MODULE"] = "project.settings"
        os.environ["DJANGO_ALLOW_ASYNC_UNSAFE"] = "true"

        django.setup()

        from base import models
        from django.conf import settings

        return settings, models

    def _init_db_connection(self):
        from django.db import connection

        self.logger.info("Listen postgresql notifies initialization...")

        crs = connection.cursor()
        pg_con = connection.connection
        pg_con.set_isolation_level(psycopg2.extensions.ISOLATION_LEVEL_AUTOCOMMIT)
        crs.execute("LISTEN entity_event;")

        return pg_con

    def _init_celery_app(self):
        self.logger.info("Celery initialization...")

        app = celery.Celery("project")
        app.config_from_object("django.conf:settings", namespace="CELERY")
        app.autodiscover_tasks()

        return app

    def get_chat_phones(self, chat):
        chat_phones = chat.chatphone_set.filter(is_using=True, phone__takeout=False)

        if chat_phones.count() >= self.settings.CHAT_PHONE_LINKS:
            return []

        phones = list(
            self.models.Phone.objects.filter(
                status=self.models.Phone.STATUS_READY,
                takeout=False
            ).exclude(id__in=[cp.phone.id for cp in chat_phones])
        )

        return phones[:self.settings.CHAT_PHONE_LINKS - chat_phones.count()]

    def _handle(self):
        self.connection.poll()

        while self.connection.notifies:
            notify = self.connection.notifies.pop(0)

            payload = PGNotify.Payload(**json.loads(notify.payload))

            self.logger.info(f"Handle new pg notification {notify.payload}")

            if payload.table == Table.links:
                if payload.action == Action.insert:
                    try:
                        link = self.models.Link.objects.get(id=payload.record)
                    except self.models.Link.DoesNotExist:
                        self.logger.warning(f"Link ({payload.record}) is not found...")

                        continue

                    self.logger.debug("Sending LinkResolveTask...")

                    self.app.send_task(
                        "LinkResolveTask",
                        (link.id,),
                        time_limit=60,
                        task_id=payload.record,
                        queue="high_prio"
                    )

                elif payload.action == Action.delete:
                    self.app.control.revoke(payload.record, terminate=True)

            elif payload.table == Table.phones:
                if payload.action == Action.insert:
                    try:
                        phone = self.models.Phone.objects.get(id=payload.record)
                    except self.models.Phone.DoesNotExist:
                        self.logger.warning(f"Phone ({payload.record}) is not found...")

                        continue

                    self.logger.debug("Sending PhoneAuthorizationTask...")

                    self.app.send_task(
                        "PhoneAuthorizationTask",
                        (phone.id,),
                        time_limit=600,
                        task_id=payload.record,
                        queue="high_prio"
                    )

                elif payload.action == Action.delete:
                    self.app.control.revoke(payload.record, terminate=True)

            elif payload.table == Table.chats_tasks:
                if payload.action == Action.insert:
                    try:
                        chat_task = self.models.ChatTask.objects.get(id=payload.record)
                    except self.models.ChatTask.DoesNotExist:
                        self.logger.warning(f"ChatTask ({payload.record}) is not found...")

                        continue

                    chat = chat_task.chat

                    phones = self.get_chat_phones(chat)

                    self.logger.debug(f"Phones len {len(phones)}")

                    if phones:
                        chat_links = list(chat.chatlink_set.order_by("-created_at"))

                        self.logger.debug(f"Chat links len {len(chat_links)}")

                        self.logger.debug("Sending celery group")

                        result = celery.group([
                            celery.signature(
                                "JoinChatTask",
                                args=(link.id, phone.id),
                                queue="high_prio",
                                immutable=True,
                                time_limit=60
                            ) for phone in phones for link in chat_links
                        ])()

                        result.join()

                        to_start = len(phones)

                        # for v in result.collect():
                        #     if not isinstance(v, (ResultBase, tuple)):
                        #         to_start -= 1

                        #     if not to_start:
                        #         result.revoke(terminate=True, signal="SIGKILL")

                    if ChatTaskType(chat_task.type) == ChatTaskType.task_member:
                        self.logger.debug("Sending ParseMembersTask...")

                        self.app.send_task(
                            "ParseMembersTask",
                            (chat.id,),
                            queue="low_prio",
                            task_id=payload.record,
                            immutable=True
                        )

                    elif ChatTaskType(chat_task.type) == ChatTaskType.task_message:
                        self.logger.debug("Sending ParseMessagesTask...")

                        self.app.send_task(
                            "ParseMessagesTask",
                            (chat.id,),
                            queue="low_prio",
                            task_id=payload.record,
                            immutable=True
                        )

                    elif ChatTaskType(chat_task.type) == ChatTaskType.task_monitoring:
                        self.logger.debug("Sending MonitoringChatTask...")

                        self.app.send_task(
                            "MonitoringChatTask",
                            (chat.id,),
                            queue="high_prio",
                            task_id=payload.record,
                            immutable=True
                        )

                    elif ChatTaskType(chat_task.type) == ChatTaskType.task_chat_media:
                        self.logger.debug("Sending ChatMediaTask...")

                        self.app.send_task(
                            "ChatMediaTask",
                            (chat.id,),
                            queue="high_prio",
                            task_id=payload.record,
                            immutable=True
                        )

                elif payload.action == Action.update:
                    try:
                        chat_task = self.models.ChatTask.objects.get(id=payload.record)
                    except self.models.ChatTask.DoesNotExist:
                        self.logger.warning(f"ChatTask ({payload.record}) is not found...")

                        continue

                    if TaskStatus(chat_task.status) == TaskStatus.revoked:
                        self.app.control.revoke(payload.record, terminate=True)

                elif payload.action == Action.delete:
                    self.app.control.revoke(payload.record, terminate=True)

            elif payload.table == Table.phones_tasks:
                if payload.action == Action.insert:
                    try:
                        phone_task = self.models.PhoneTask.objects.get(id=payload.record)
                    except self.models.PhoneTask.DoesNotExist:
                        self.logger.warning(f"PhoneTask ({payload.record}) is not found...")

                        continue

                    if PhoneTaskType(phone_task.type) == PhoneTaskType.task_auth:
                        self.logger.debug("Sending PhoneAuthorizationTask...")

                        self.app.send_task(
                            "PhoneAuthorizationTask",
                            (phone_task.phone.id,),
                            time_limit=60,
                            task_id=payload.record,
                            queue="high_prio"
                        )

                elif payload.action == Action.update:
                    try:
                        phone_task = self.models.PhoneTask.objects.get(id=payload.record)
                    except self.models.PhoneTask.DoesNotExist:
                        self.logger.warning(f"PhoneTask ({payload.record}) is not found...")

                        continue

                    if TaskStatus(phone_task.status) == TaskStatus.revoked:
                        self.app.control.revoke(payload.record, terminate=True)

                elif payload.action == Action.delete:
                    self.app.control.revoke(payload.record, terminate=True)

    def _ex_handler(self, loop, context):
        loop.default_exception_handler(context)

        self.stop(1)

    def start(self):
        self.logger.info("Starting PGNotify service")

        self.loop.add_reader(self.connection, self._handle)

        self.loop.run_forever()

    def stop(self, code: "int" = 0):
        self.logger.info(f"Stopping PGNotify service with code {code}.")
        self.logger.info("Cleaning up...")

        self.loop.stop()

        sys.exit(code)


if __name__ == "__main__":
    service = PGNotify()
    service.start()
