import time
import re
import hypercorn.asyncio
import random

from telethon.tl.functions import channels
from models.Phone import Phone
from telethon import functions, errors, types
from core.PhonesManager import PhonesManager
from processors.ApiProcessor import ApiProcessor

from config import SERVER
from quart import Quart, request
app = Quart(__name__)

from models.Channel import Channel

api = ApiProcessor()

# Connect the client before we start serving with Quart
@app.before_serving
async def startup():
    phones = api.get('phone')

    for phone in phones:
        print('')

        try:
            phone = Phone(phone)

            await phone.client.connect()

            if not await phone.client.is_user_authorized():
                phone.code_hash = None

                try:
                    sent = await phone.client.send_code_request(phone=phone.number)
                    print('sent', phone.number, sent)

                    phone.code_hash = sent.phone_code_hash
                    
                    time.sleep(1)
                except errors.RPCError as ex:
                    print('Exception', phone.number, ex.message)

                api.set('phone', { 'id': phone.id, 'isVerified': False, 'phoneCodeHash': phone.code_hash })
            else:
                PhonesManager().add(phone)

                print('using', phone.number)
        except errors.RPCError as ex:
            print('Exception', phone.number, ex.message)


# After we're done serving (near shutdown), clean up the client
@app.after_serving
async def cleanup():
    for phone in PhonesManager().list():
        await phone.client.disconnect()

@app.route('/phone/resend', methods=['POST'])
async def phone_resend():
    json = await request.json

    phone = json.get('phone')

    if PhonesManager().get(phone['id']) is None:
        try:
            phone = Phone(phone)

            await phone.client.connect()

            try:
                sent = await phone.client.send_code_request(phone=phone.number)
                print('sent', phone.number, sent)

                phone.code_hash = sent.phone_code_hash

                api.set('phone', { 'id': phone.id, 'isVerified': False, 'phoneCodeHash': phone.code_hash })
            except errors.RPCError as ex:
                print('Exception', phone.number, ex.message)

                return ex.message
        except OSError as ex:
            return ex.strerror
    else:
        return "PHONE_IS_AUTHORIZED"


@app.route('/phone/verify', methods=['POST'])
async def phone_verify():
    json = await request.json

    _phone = json.get('phone')

    code = json.get('code')

    if code is None:
        raise Exception('Недействительный код')

    phone = PhonesManager().get(_phone['id'])

    if phone is None:
        try:
            phone = Phone(_phone)

            await phone.client.connect()

            PhonesManager().add(phone)
        except OSError as ex:
            return ex.strerror

    await phone.client.sign_in(phone=phone.number, code=code, phone_code_hash=phone.code_hash)

    api.set('phone', { 'id': phone.id, 'isVerified': True, 'phoneCodeHash': None })

    return "ok"

@app.route('/logout', methods=['GET'])
async def logout():
    for phone in PhonesManager().list():
        await phone.client.log_out()
            
    return ""


def get_hash(link):
    if link is None:
        raise Exception('Недействительная ссылка')

    link = re.sub(r'https?:\/\/t\.me\/', '', link)

    matches = re.match(r'(?:joinchat\/|\+)(\w{16})', link)

    hash = matches.group(1) if not matches is None else None

    link = link if hash is None else None

    return link, hash

@app.route('/add', methods=['POST'])
async def add():
    json = await request.json

    link, hash = get_hash(json.get('link'))

    chat = None

    phones = sorted(PhonesManager().list(), key=lambda phone: phone.chats_count)

    joined_phones = []

    for phone in phones:
        try:
            chat = await join(phone, channel=link, hash=hash)

            joined_phones.append(phone)

            break
        except errors.rpcerrorlist.InviteHashExpiredError:
            return "INVALID_LINK"
    else:
        return "INVALID_LINK"
    
    joined_phone = joined_phones[0]

    phones_to_join = [phone for phone in phones if phone.id != joined_phone.id]

    print(phones_to_join)

    for phone in phones_to_join:
        try:
            await invite(phone=joined_phone, next_phone=phone, chat=chat)
            
            joined_phones.append(phone)
        except:
            pass

        if len(joined_phones) >= 3:
            break

    api.set('chat', { 'internalId': chat.id, 'title': chat.title, 'phones': [{ 'id': phone.id } for phone in joined_phones] })
    
    return chat.to_dict()

@app.route('/check', methods=['POST'])
async def check():
    pass

async def main():
    await hypercorn.asyncio.serve(app, hypercorn.Config())

if __name__ == '__main__':
    app.run(debug = SERVER['debug'], host = SERVER['host'], port = SERVER['port'])