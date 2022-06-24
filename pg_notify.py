import signal
import logging
import os
import asyncio
import json
import sys
import celery
from enum import Enum, auto
import psycopg2.extensions


class Action(Enum):
    insert = auto()
    update = auto()
    delete = auto()


class Table(Enum):
    chats = auto()
    phones = auto()
    chats_tasks = auto()


class TaskStatus(Enum):
    created = 0
    started = 1
    success = 2
    failure = 3
    revoked = 4


class TaskType(Enum):
    task_member = 0
    task_message = 1
    task_monitoring = 2
    task_chat_media = 3


class PGNotify:
    class Payload:
        def __init__(self, table, action, schema, record, data):
            self.table = Table[table]
            self.action = Action[action]
            self.schema = schema
            self.record = record
            self.data = data

    def __init__(self) -> None:
        signal.signal(signal.SIGTERM, self._handle_sig)
        signal.signal(signal.SIGINT, self._handle_sig)

        self._init_django()

        self.loop = asyncio.new_event_loop()
        self.logger = self._init_logger()
        self.connection = self._init_db_connection()
        self.app = self._init_celery_app()

        self.logger.info('PGNotify instance created')

    def _handle_sig(self, sig, frame):
        self.logger.warning(f'{signal.Signals(sig).name} received...')
        self.stop()

    def _init_django(self):
        import django

        os.environ["DJANGO_SETTINGS_MODULE"] = "project.settings"
        os.environ["DJANGO_ALLOW_ASYNC_UNSAFE"] = "true"

        django.setup()

    def _init_logger(self):
        logger = logging.getLogger(__name__)
        logger.setLevel(logging.DEBUG)
        stdout_handler = logging.StreamHandler()
        stdout_handler.setLevel(logging.INFO)
        stdout_handler.setFormatter(logging.Formatter('%(levelname)8s | %(message)s'))
        logger.addHandler(stdout_handler)

        return logger

    def _init_celery_app(self):
        app = celery.Celery('project')
        app.config_from_object('django.conf:settings', namespace='CELERY')
        app.autodiscover_tasks()

        return app

    def _init_db_connection(self):
        from django.db import connection

        crs = connection.cursor()
        pg_con = connection.connection
        pg_con.set_isolation_level(psycopg2.extensions.ISOLATION_LEVEL_AUTOCOMMIT)
        crs.execute("LISTEN entity_event;")

        return pg_con

    def _get_chat(self, chat_id: int):
        import base.models as base_models

        try:
            return base_models.Chat.objects.get(id=chat_id)
        except base_models.Chat.DoesNotExist:
            self.logger.warning(f'Chat ({chat_id}) is not found...')

        return None

    def _handle(self):
        self.connection.poll()

        self.logger.info(f'Handle new pg notifications {self.connection.notifies}')
        self.logger.info(f"{type(self.connection.notifies[0])}")

        for index, notify in enumerate(self.connection.notifies):
            payload = PGNotify.Payload(**json.loads(notify.payload))

            self.logger.info(f'Notification #{index} {notify.payload}')

            if payload.table == Table.chats:
                if payload.action == Action.insert:
                    self.logger.debug('Sending ChatResolveTask...')

                    self.app.send_task(
                        "ChatResolveTask",
                        (payload.record['id'],),
                        time_limit=60,
                        task_id=payload.record['id'],
                        queue='high_prio'
                    )

                elif payload.action == Action.delete:
                    self.app.control.revoke(payload.record['id'], terminate=True)

            elif payload.table == Table.phones:
                if payload.action == Action.insert:
                    self.logger.debug('Sending PhoneAuthorizationTask...')

                    self.app.send_task(
                        "PhoneAuthorizationTask",
                        (payload.record['id'],),
                        time_limit=600,
                        task_id=payload.record['id'],
                        queue="high_prio"
                    )

                elif payload.action == Action.delete:
                    self.app.control.revoke(payload.record['id'], terminate=True)

            elif payload.table == Table.chats_tasks:
                if payload.action == Action.insert:
                    chat = self._get_chat(payload.record['chat_id'])

                    if chat is None:
                        return

                    s = None

                    if TaskType(payload.record['type']) == TaskType.task_member:
                        self.logger.debug('Creating ParseMembersTask signature...')

                        s = celery.signature(
                            "ParseMembersTask",
                            (chat.id,),
                            queue="low_prio",
                            task_id=payload.record['id'],
                            immutable=True
                        )

                    elif TaskType(payload.record['type']) == TaskType.task_message:
                        self.logger.debug('Creating ParseMessagesTask signature...')

                        s = celery.signature(
                            "ParseMessagesTask",
                            (chat.id,),
                            queue="low_prio",
                            task_id=payload.record['id'],
                            immutable=True
                        )

                    elif TaskType(payload.record['type']) == TaskType.task_monitoring:
                        self.logger.debug('Creating MonitoringChatTask signature...')

                        s = celery.signature(
                            "MonitoringChatTask",
                            (chat.id,),
                            queue="high_prio",
                            task_id=payload.record['id'],
                            immutable=True
                        )

                    elif TaskType(payload.record['type']) == TaskType.task_chat_media:
                        self.logger.debug('Creating ChatMediaTask signature...')

                        s = celery.signature(
                            "ChatMediaTask",
                            (chat.id,),
                            queue="high_prio",
                            task_id=payload.record['id'],
                            immutable=True
                        )

                    phone_ids = chat.get_chat_phones()

                    self.logger.debug(f'Phones len {len(phone_ids)}')

                    if phone_ids:
                        self.logger.debug('Sending celery chord')

                        celery.chord([
                            celery.signature(
                                'JoinChatTask',
                                args=(chat.id, id),
                                queue='high_prio',
                                immutable=True,
                                time_limit=60
                            ) for id in phone_ids
                        ])(s)
                    else:
                        self.logger.debug('Sending celery standalone task')

                        s.delay()

                elif payload.action == Action.update:
                    if TaskStatus(payload.record['status']) == TaskStatus.revoked:
                        self.app.control.revoke(payload.record['id'], terminate=True)

                elif payload.action == Action.delete:
                    self.app.control.revoke(payload.record['id'], terminate=True)

        self.connection.notifies.clear()

    def start(self):
        self.loop.add_reader(self.connection, self._handle)

        self.loop.run_forever()

    def stop(self):
        self.logger.info('Cleaning up...')
        self.loop.stop()
        sys.exit(0)


if __name__ == '__main__':
    service = PGNotify()
    service.start()
