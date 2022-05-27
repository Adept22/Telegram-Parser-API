import datetime
import telethon
import asyncio
from tg_parser.celeryapp import app
from django.conf import settings


@app.task
def test_task():
    print("RUN task at {}".format(datetime.datetime.now()))
    return True


@app.task
def unban_phone_task(phone_id: int):
    from base.models import Phone
    print("RUN task at {}".format(datetime.datetime.now()))
    try:
        phone = Phone.objects.get(id=phone_id)
    except Phone.DoesNotExist:
        return False                                        # Добавить логирование!!!
    # phone.wait = timezone.now()
    # phone.save()
    return True


@app.task
def resolve_chat(data):
    from base.models import Chat, ChatLog
    try:
        chat = Chat.objects.get(id=data.get('chat_id'))
    except Exception as ex:
        return False

    with telethon.TelegramClient(
        api_id=settings.API_ID,
        api_hash=settings.API_HASH,
        session=telethon.sessions.StringSession(data.get('session'))
    ) as client:
        try:
            tg_chat = client.get_entity(chat.link)
        except Exception as ex:
            ChatLog.objects.create(chat=chat, body=ex)
            return False
        else:
            internal_id = telethon.utils.get_peer_id(tg_chat)
            chat.title = tg_chat.title
            chat.internal_id = internal_id
            chat.save()
            return True


@app.task
def make_telegram_bot(phone_id: int):
    import re
    import random
    from django.conf import settings
    from telethon import TelegramClient, events
    from base.models import Phone, Bot

    try:
        phone = Phone.objects.get(id=phone_id)
    except Phone.DoesNotExist:
        return False

    client = TelegramClient(
        session=telethon.sessions.StringSession(phone.session),
        api_id=settings.API_ID,
        api_hash=settings.API_HASH,
    )

    BOT_NAME = ''.join(random.choice('ABCDEFGHIJKLMNOPQRSTUVWXYZ') for _ in range(10))
    BOT_USER_NAME = "{}_bot".format(BOT_NAME)

    @client.on(events.NewMessage)
    async def message_handler(event):
        print(event.raw_text)
        if 'Please choose a name for your bot' in event.raw_text:
            await event.reply(BOT_NAME)
        elif 'choose a username for your bot' in event.raw_text:
            await event.reply(BOT_USER_NAME)
        elif 'Done! Congratulations on your new bot' in event.raw_text:
            token = re.search(r'\d{10}:\w{35}', event.raw_text)
            # Bot.objects.create(name=BOT_NAME, phone=phone, token=token)
            asyncio.run(Bot.objects.create(name=BOT_NAME, phone=phone, token=token))
            print("Bot created!")
            await client.disconnect()

    async def main():
        await client.send_message('botfather', '/newbot')

    with client:
        client.loop.run_until_complete(main())
        client.run_until_disconnected()
        return True

