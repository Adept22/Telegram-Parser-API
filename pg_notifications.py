#!/usr/bin/env python
import os
import json
import asyncio

import celery
import psycopg2.extensions
from enum import Enum, auto

from celery.canvas import Signature
from django.db import connection
import django
os.environ["DJANGO_SETTINGS_MODULE"] = "tg_parser.settings"
os.environ["DJANGO_ALLOW_ASYNC_UNSAFE"] = "true"
django.setup()
from tg_parser.celeryapp import app as celery_app
import base.models as base_models


crs = connection.cursor()
pg_con = connection.connection
pg_con.set_isolation_level(psycopg2.extensions.ISOLATION_LEVEL_AUTOCOMMIT)
crs.execute("LISTEN entity_event;")


class Action(Enum):
    insert = auto()
    update = auto()
    delete = auto()


class Table(Enum):
    chats = auto()
    phones = auto()
    tasks = auto()


class TaskType(Enum):
    task_member = 0
    task_message = 1
    task_monitoring = 2
    task_chat_media = 3


class Notify:
    def __init__(self, table, action, schema, record, data):
        self.table = Table[table]
        self.action = Action[action]
        self.schema = schema
        self.record = record
        self.data = data


def get_chat(chat_id: int):
    try:
        chat = base_models.Chat.objects.get(id=chat_id)
    except base_models.Chat.DoesNotExist:
        chat = None
    return chat


def handle_notify():
    pg_con.poll()
    for notify in pg_con.notifies:
        notice = Notify(**json.loads(notify.payload))
        if notice.table == Table.chats:
            match notice.action:
                case Action.insert:
                    celery_app.send_task("ChatResolveTask", (notice.record['id'],), time_limit=60, queue='high_prio')

                case Action.update:
                    chat = get_chat(notice.record['id'])
                    if chat is not None:
                        if chat.status != notice.data['status'] and chat.status is base_models.Chat.MONITORING:
                            celery_app.send_task("ParseMembersTask", (chat.id,), queue="high_prio")

        elif notice.table == Table.phones:
            match notice.action:
                case Action.insert:
                    celery_app.send_task(
                        "PhoneAuthorizationTask",
                        (notice.record['id'],),
                        time_limit=600,
                        queue="high_prio"
                    )

        elif notice.table == Table.tasks:
            if notice.action == Action.insert:
                chat = get_chat(notice.record['chat_id'])
                signatures = chat.make_chat_phones()
                s = None

                match TaskType(notice.record['type']):
                    case TaskType.task_member:
                        s = Signature(
                            "ParseMembersTask",
                            (notice.record['chat_id'],),
                            queue="low_prio",
                            task_id=notice.record['id']
                        )

                    case TaskType.task_message:
                        s = Signature(
                            "ParseMessagesTask",
                            (notice.record['chat_id'],),
                            queue="low_prio",
                            task_id=notice.record['id'],
                        )

                    case TaskType.task_monitoring:
                        s = Signature(
                            "MonitoringChatTask",
                            (notice.record['chat_id'],),
                            queue="high_prio",
                            task_id=notice.record['id'],
                        )

                    case TaskType.task_chat_media:
                        s = Signature(
                            "ChatMediaTask",
                            (notice.record['chat_id'],),
                            queue="high_prio",
                            task_id=notice.record['id'],
                        )

                if signatures:
                    celery.chord(signatures)(s)
                else:
                    s.delay()

    pg_con.notifies.clear()


loop = asyncio.new_event_loop()
loop.add_reader(pg_con, handle_notify)
loop.run_forever()

