#!/usr/bin/env python
import os
import json
import asyncio
import psycopg2.extensions
from enum import Enum, auto
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
    base_chat = auto()
    base_phone = auto()


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


def get_phone(phone_id: int):
    try:
        phone = base_models.Phone.objects.get(id=phone_id)
    except base_models.Phone.DoesNotExist:
        phone = None
    return phone


def handle_notify():
    pg_con.poll()
    for notify in pg_con.notifies:
        notice = Notify(**json.loads(notify.payload))
        if notice.table == Table.base_chat:
            if notice.action == Action.insert:
                chat = get_chat(notice.record['id'])
                if chat is not None:
                    chat.make_chat_phones

            elif notice.action == Action.update:
                chat = get_chat(notice.record['id'])
                if chat is not None:
                    if chat.status != notice.data['status'] and chat.status is base_models.Chat.MONITORING:
                        celery_app.send_task("ParseMembersTask", (chat.id,), queue="high_prio")

            elif notice.action == Action.delete:
                pass

        elif notice.table == Table.base_phone:
            if notice.action == Action.insert:
                phone = get_phone(notice.record['id'])
                if phone is not None:
                    celery_app.send_task("PhoneAuthorizationTask", (phone.id,), time_limit=180, queue="high_prio")

            elif notice.action == Action.update:
                pass

            elif notice.action == Action.delete:
                pass
    pg_con.notifies.clear()


loop = asyncio.new_event_loop()
loop.add_reader(pg_con, handle_notify)
loop.run_forever()

