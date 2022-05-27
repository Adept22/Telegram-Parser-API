import datetime, telethon, asyncio

import celery
from asgiref.sync import sync_to_async

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


class PhoneAuthorizationTask(celery.Task):
    name = "PhoneAuthorizationTask"

    async def _run(self, phone, parser):
        import random, names, telethon, telethon.sessions

        client = telethon.TelegramClient(
            connection_retries=-1,
            retry_delay=5, 
            session=telethon.sessions.StringSession(phone.session), 
            # api_id=phone.parser.api_id,
            api_id=parser.api_id,
            api_hash=parser.api_hash,
            # api_hash=phone.parser.api_hash
        )

        if not client.is_connected():
            try:
                await client.connect()
            except OSError as ex:
                # print(f"Unable to connect client. Exception: {ex}")

                return

        code_hash = None

        while True:
            if not await client.is_user_authorized():
                try:
                    if phone.code is not None and code_hash is not None:
                        try:
                            await client.sign_in(phone.number, phone.code, phone_code_hash=code_hash)
                        except telethon.errors.PhoneNumberUnoccupiedError:
                            await asyncio.sleep(random.randint(2, 5))

                            if phone.first_name is None:
                                phone.first_name = names.get_first_name()

                            if phone.last_name is None:
                                phone.last_name = names.get_last_name()

                            await client.sign_up(phone.code, phone.first_name, phone.last_name, phone_code_hash=code_hash)
                        except (
                            telethon.errors.PhoneCodeEmptyError, 
                            telethon.errors.PhoneCodeExpiredError, 
                            telethon.errors.PhoneCodeHashEmptyError, 
                            telethon.errors.PhoneCodeInvalidError
                        ) as ex:
                            print(f"Code invalid. Exception {ex}")

                            phone.code = None

                            # phone.save()
                            await sync_to_async(phone.save)()

                            continue

                        phone.session = client.session.save()
                        phone.is_verified = True
                        phone.code = None
                        
                        internal_id = getattr(await client.get_me(), "id")
                        
                        if internal_id is not None and phone.internal_id != internal_id:
                            phone.internal_id = internal_id

                        # phone.save()
                        await sync_to_async(phone.save)()

                        break
                    elif code_hash is None:
                        try:
                            sent = await client.send_code_request(phone=phone.number, force_sms=True)
                            
                            code_hash = sent.phone_code_hash

                            print(f"Code sended.")
                        except telethon.errors.rpcerrorlist.FloodWaitError as ex:
                            print(f"Flood exception. Sleep {ex.seconds}.")
                            
                            await asyncio.sleep(ex.seconds)

                            continue
                    else:
                        await asyncio.sleep(10)

                        # phone.refresh_from_db()
                        await sync_to_async(phone.refresh_from_db)()

                except telethon.errors.RPCError as ex:
                    print(f"Cannot authentificate. Exception: {ex}")
                    
                    phone.session = None
                    phone.is_banned = True
                    phone.is_verified = False
                    phone.code = None
                    await sync_to_async(phone.save)()

                    return
            else:
                break

        print(f"Authorized.")

    def run(self, phone_id):
        from base.models import Phone

        try:
            phone = Phone.objects.get(id=phone_id)
        except Phone.DoesNotExist:
            return False
        return asyncio.run(self._run(phone, phone.parser))


app.register_task(PhoneAuthorizationTask())


class ChatResolveTask(celery.Task):
    name = "ChatResolveTask"

    async def _run(self, chat):
        from base.models import ChatPhone
        from tg_parser.utils import TelegramClient

        chat_phones = ChatPhone.objects.filter(chat_id=chat.id)
        
        for chat_phone in chat_phones:

            async with TelegramClient(chat_phone.phone) as client:
                try:
                    tg_chat = await client.get_entity(chat.link)
                except telethon.errors.ChannelPrivateError as ex:
                    print(f"Chat is private. Exception: {ex}.")

                    continue
                except telethon.errors.FloodWaitError as ex:
                    print(f"Chat resolve must wait {ex.seconds}.")

                    continue
                except (TypeError, KeyError, ValueError, telethon.errors.RPCError):
                    chat.is_available = False
                    chat.save()
                        
                    return
                else:
                    internal_id = telethon.utils.get_peer_id(tg_chat)

                    if chat.internal_id != internal_id:
                        chat.internal_id = internal_id

                    if chat.title != tg_chat.title:
                        chat.title = tg_chat.title
                        
                    chat.save()

                    break

    def run(self, chat_id):
        from base.models import Chat

        try:
            chat = Chat.objects.get(id=chat_id)
        except Chat.DoesNotExist:
            return False

        return asyncio.run(self._run(chat))


app.register_task(ChatResolveTask())


class JoinChatTask(celery.Task):
    name = "JoinChatTask"

    async def _run(self, chat):
        from base.models import ChatPhone
        from base.exceptions import ChatNotAvailableError
        from tg_parser.utils import TelegramClient

        chat_phones = ChatPhone.objects.filter(chat_id=chat.id)
        
        for chat_phone in chat_phones:

            async with TelegramClient(chat_phone.phone) as client:
                try:
                    dialogs = await client.get_dialogs(limit=0)

                    if dialogs.total >= 500:
                        return False

                    if chat.hash is None:
                        await client(telethon.functions.channels.JoinChannelRequest(chat.username))
                    else:
                        try:
                            await client(telethon.functions.messages.ImportChatInviteRequest(chat.hash))
                        except telethon.errors.UserAlreadyParticipantError as ex:
                            await client(telethon.functions.messages.CheckChatInviteRequest(chat.hash))
                except telethon.errors.FloodWaitError as ex:
                    print(f"Chat wiring for phone {chat_phone.phone.id} must wait {ex.seconds}.")

                    await asyncio.sleep(ex.seconds)

                    continue
                except(
                    telethon.errors.ChannelsTooMuchError, 
                    telethon.errors.SessionPasswordNeededError,
                    telethon.errors.UserDeactivatedBanError
                ) as ex:
                    print(f"Chat not available for phone {chat_phone.phone.id}. Exception {ex}")

                    return False
                except (TypeError, KeyError, ValueError, telethon.errors.RPCError) as ex:
                    print(f"Chat not available. Exception {ex}.")
                    
                    raise ChatNotAvailableError(str(ex))
                else:
                    return True

    def run(self, chat_id):
        from base.models import Chat

        try:
            chat = Chat.objects.get(id=chat_id)
        except Chat.DoesNotExist:
            return False

        return asyncio.run(self._run(chat))


app.register_task(JoinChatTask())


class ParseChatTask(celery.Task):
    name = "ParseChatTask"

    async def _set_member(self, client, user: 'telethon.types.TypeUser'):
        from base.models import Member

        new_member = {
            "internal_id": user.id, 
            "username": user.username, 
            "first_name": user.first_name, 
            "last_name": user.last_name, 
            "phone": user.phone
        }

        try:
            full_user: 'telethon.types.UserFull' = await client(telethon.functions.users.GetFullUserRequest(user.id))
        except Exception:
            pass
        else:
            new_member["username"] = full_user.user.username
            new_member["first_name"] = full_user.user.first_name
            new_member["last_name"] = full_user.user.last_name
            new_member["phone"] = full_user.user.phone
            new_member["about"] = full_user.about

        return Member.objects.update_or_create(**new_member)
        
    async def _set_chat_member(self, chat, member, participant = None):
        from base.models import ChatMember

        new_chat_member = { "chat": chat, "member": member }

        if isinstance(participant, (telethon.types.ChannelParticipant, telethon.types.ChatParticipant)):
            new_chat_member["date"] = participant.date.isoformat()
        else:
            new_chat_member["isLeft"] = True

        return ChatMember.objects.update_or_create(**new_chat_member)
    
    async def _set_chat_member_role(self, chat_member, participant = None):
        from base.models import ChatMemberRole

        new_chat_member_role = { "member": chat_member }

        if isinstance(participant, (telethon.types.ChannelParticipantAdmin, telethon.types.ChatParticipantAdmin)):
            new_chat_member_role["title"] = (participant.rank if participant.rank is not None else "Администратор")
            new_chat_member_role["code"] = "admin"
        elif isinstance(participant, (telethon.types.ChannelParticipantCreator, telethon.types.ChatParticipantCreator)):
            new_chat_member_role["title"] = (participant.rank if participant.rank is not None else "Создатель")
            new_chat_member_role["code"] = "creator"
        else:
            new_chat_member_role["title"] = "Участник"
            new_chat_member_role["code"] = "member"

        return ChatMemberRole.objects.update_or_create(**new_chat_member_role)
    
    def _get_fwd(self, fwd_from: 'telethon.types.TypeMessageFwdHeader | None'):
        if fwd_from is not None:
            fwd_from_id = None

            if fwd_from.from_id is not None:
                if isinstance(fwd_from.from_id, telethon.types.PeerChannel):
                    fwd_from_id = fwd_from.from_id.channel_id
                elif isinstance(fwd_from.from_id, telethon.types.PeerUser):
                    fwd_from_id = fwd_from.from_id.user_id
            
            return fwd_from_id, fwd_from.from_name if fwd_from.from_name is not None else "Неизвестно"
            
        return None, None
    
    async def _handle_links(self, client, text):
        import re
        from base.models import Chat

        for link in re.finditer(r't\.me\/(?:joinchat\/|\+)?[-_.a-zA-Z0-9]+', text):
            link = f"https://{link.group()}"

            try:
                tg_entity = await client.get_entity(link)
            except:
                pass
            else:
                try:
                    if isinstance(tg_entity, (telethon.types.Channel, telethon.types.Chat)):
                        Chat.objects.create(link=link, internal_id=tg_entity.id, title=tg_entity.title, is_available=False)
                    elif isinstance(tg_entity, telethon.types.User):
                        member = await self._set_member(client, tg_entity)

                        # TODO: Как запускать?
                        # multiprocessing.Process(target=processes.member_media_process, args=(chat_phone, member, tg_entity)).start()
                except Exception as ex:
                    pass
                #     logging.info(f"Can't create new entity from link {link}. Exception {ex}")
                # else:
                #     logging.info(f"New entity from link {link} created.")

    async def _handle_user(self, chat_phone, client, user, participant=None):
        if user.is_self:
            return None, None, None

        member = await self._set_member(client, user)
        # TODO: Как запускать?
        # multiprocessing.Process(target=processes.member_media_process, args=(chat_phone, member, user)).start()

        chat_member = await self._set_chat_member(chat_phone.chat, member, participant)
        chat_member_role = await self._set_chat_member_role(chat_member, participant)

        return member, chat_member, chat_member_role
        
    async def _handle_message(self, chat_phone, client, tg_message: 'telethon.types.TypeMessage'):
        from base.models import Message
        
        fwd_from_id, fwd_from_name = self._get_fwd(tg_message.fwd_from)
        
        chat_member = None

        if isinstance(tg_message.from_id, telethon.types.PeerUser):
            user = await client.get_entity(tg_message.from_id)

            member, chat_member, chat_member_role = await self._handle_user(chat_phone, client, user)

        reply_to = Message.objects.update_or_create(internal_id=tg_message.reply_to.reply_to_msg_id, chat=chat_phone.chat) if tg_message.reply_to is not None else None

        if tg_message.replies is not None:
            try:
                replies: 'telethon.types.messages.Messages' = await client(telethon.tl.functions.messages.GetRepliesRequest(tg_message.peer_id, tg_message.id, 0, None, 0, 0, 0, 0, 0))

                for reply in replies.messages:
                    await self._handle_message(chat_phone, client, reply)
            except Exception as ex:
                pass
                # logging.exception(ex)

        message = Message.objects.update_or_create(
            internal_id=tg_message.id, 
            text=tg_message.message, 
            chat=chat_phone.chat, 
            member=chat_member,
            reply_to=reply_to, 
            is_pinned=tg_message.pinned,     
            forwarded_from_id=fwd_from_id, 
            forwarded_from_name=fwd_from_name, 
            grouped_id=tg_message.grouped_id, 
            date=tg_message.date.isoformat() 
        )
        
        # if tg_message.media != None:
        #     TODO: Как запускать?
        #     multiprocessing.Process(target=processes.message_media_process, args=(chat_phone, message, tg_message)).start()

    async def _members_thread(self, chat, chat_phones):
        from tg_parser.utils import TelegramClient

        for chat_phone in chat_phones:

            try:
                async with TelegramClient(chat_phone) as client:
                    try:
                        async for user in client.iter_participants(entity=chat.internal_id, aggressive=True):
                            user: 'telethon.types.TypeUser'

                            await self._handle_user(chat_phone, client, user, user.participant)
                    except telethon.errors.common.MultiError as ex:
                        await asyncio.sleep(30)

                        continue
                    # except (
                    #     telethon.errors.ChatAdminRequiredError,
                    #     telethon.errors.ChannelPrivateError,
                    # ) as ex:
                    #     logging.critical(f"Can't download participants. Exception: {ex}")
                    except telethon.errors.FloodWaitError as ex:
                        print(f"Members request must wait {ex.seconds} seconds.")

                        continue
                    except (KeyError, ValueError, telethon.errors.RPCError) as ex:
                        print(f"Chat not available. Exception: {ex}")
                        
                        chat.is_available = False
                        chat.save()
                    # else:
                    #     logging.info(f"Participants download success.")
                        
                    return
            # except ClientNotAvailableError as ex:
                print(f"Client not available.")
            except telethon.errors.UserDeactivatedBanError as ex:
                print(f"Phone is banned.")
                
                chat_phone.phone.is_banned = True
                chat_phone.phone.save()
        # else:
        #     logging.error(f"Participants download failed.")

    async def _messages_thread(self, chat, chat_phones):
        from base.models import Message
        from tg_parser.utils import TelegramClient
        
        last_message: 'TypeMessage | None' = Message.objects.filter(chat_id=chat.id).order_by("-internal_id")[0]
        max_id = last_message.internal_id if last_message is not None else 0

        for chat_phone in chat_phones:
            try:
                async with TelegramClient(chat_phone.phone) as client:
                    try:
                        async for tg_message in client.iter_messages(chat.internal_id, 1000, max_id=max_id):
                            tg_message: 'telethon.types.TypeMessage'

                            if not isinstance(tg_message, telethon.types.Message):
                                continue

                            await self._handle_links(client, tg_message.message)

                            await self._handle_message(chat_phone, client, tg_message)
                        # else:
                        #     logging.info(f"Messages download success.")
                    except telethon.errors.common.MultiError as ex:
                        continue
                    except telethon.errors.FloodWaitError as ex:
                        print(f"Messages request must wait {ex.seconds} seconds.")

                        continue
                    except (KeyError, ValueError, telethon.errors.RPCError) as ex:
                        print(f"Chat not available. Exception {ex}")

                        chat.is_available = False
                        chat.save()

                    return
            # except ClientNotAvailableError as ex:
            #     logging.error(f"Client not available.")
            except telethon.errors.UserDeactivatedBanError as ex:
                print(f"Phone is banned. Exception {ex}")
                
                chat_phone.phone.is_banned = True
                chat_phone.phone.save()
        # else:
        #     logging.error(f"Messages download failed.")

    async def _run(self, chat):
        from base.models import ChatPhone

        chat_phones = ChatPhone.objects.filter(chat_id=chat.id)
        
        await self._members_thread(chat, chat_phones)
        await self._messages_thread(chat, chat_phones)

    def run(self, chat_id):
        from base.models import Chat

        try:
            chat = Chat.objects.get(id=chat_id)
        except Chat.DoesNotExist:
            return False

        return asyncio.run(self._run(chat))


app.register_task(ParseChatTask())


class MonitoringChatTask(ParseChatTask):
    name = "MonitoringChatTask"

    async def _run(self, chat):
        from tg_parser.utils import TelegramClient
        from base.models import ChatPhone

        chat_phones = ChatPhone.objects.filter(chat_id=chat.id)

        for chat_phone in chat_phones:

            try:
                async with TelegramClient(chat_phone.phone) as client:
                    async def handle_chat_action(event):
                        if event.user_added or event.user_joined or event.user_left or event.user_kicked:
                            async for user in event.get_users():
                                await self._handle_user(chat_phone, client, user, user.participant)

                    async def handle_new_message(event):
                        if not isinstance(event.message, telethon.types.Message):
                            return
                            
                        await self._handle_links(client, event.message.message)
                        
                        await self._handle_message(chat_phone, client, event.message)

                    client.add_event_handler(handle_chat_action, telethon.events.chataction.ChatAction(chats=chat.internal_id))
                    client.add_event_handler(handle_new_message, telethon.events.NewMessage(chats=chat.internal_id, incoming=True))
            # except ClientNotAvailableError as ex:
            #     logging.error(f"Client not available.")
            except telethon.errors.UserDeactivatedBanError as ex:
                print(f"Phone is banned.")
                
                chat_phone.phone.is_banned = True
                chat_phone.phone.save()

    def run(self, chat_id):
        from base.models import Chat

        try:
            chat = Chat.objects.get(id=chat_id)
        except Chat.DoesNotExist:
            return False

        return asyncio.run(self._run(chat))


app.register_task(MonitoringChatTask())

