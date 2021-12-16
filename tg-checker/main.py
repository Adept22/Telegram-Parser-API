import time
import re
import hypercorn.asyncio
import telethon
from telethon import functions, errors
from core.ClientManager import ClientManager
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

    print('')
    print('phones ', phones)

    for phone in phones:
        client = ClientManager().new(phone['phone'])

        try:
            await client.connect()

            if not await client.is_user_authorized():
                print('client ', phone['phone'], client, 'false')
                sent = await client.send_code_request(phone=phone['phone'])
                print('sent ', phone['phone'], sent)

                api.set('phone', { 'id': phone['id'], 'isVerified': False, 'phoneCodeHash': sent.phone_code_hash })
            else:
                ClientManager().set(phone['id'], client)
        except errors.RPCError as ex:
            print(ex.message)
    print('clients ', ClientManager().get_clients())


# After we're done serving (near shutdown), clean up the client
@app.after_serving
async def cleanup():
    clients = ClientManager().get_clients()
    
    for client in clients.values():
        await client.disconnect()

@app.route('/phone/verify', methods=['POST'])
async def phone_verify():
    json = await request.json

    if 'phone' in json and not json['phone'] is None:
        phone = json['phone']
    else:
        raise Exception('Недействительный номер телефона')

    if 'code' in json and not json['code'] is None:
        code = json['code']
    else:
        raise Exception('Недействительный код')

    try:
        if not ClientManager().has(phone['id']):
            client = ClientManager().new(phone['phone'])
            
            try:
                await client.connect()

                ClientManager().set(phone['id'], client)
            except OSError as ex:
                return ex.strerror
        else:
            client = ClientManager().get(phone['id'])

        await client.sign_in(phone=phone['phone'], code=code, phone_code_hash=phone['phoneCodeHash'])

        api.set('phone', { 'id': phone['id'], 'isVerified': True, 'phoneCodeHash': None })
    except errors.RPCError as ex:
        # пометить в API что не удалось авторизовать
        return "Except error: " + ex.message

    return "ok"

@app.route('/logout', methods=['GET'])
async def logout():
    clients = ClientManager().get_clients()

    for client in clients.values():
        await client.log_out()
            
    return ""

@app.route('/check', methods=['POST'])
async def check():
    json = await request.json

    if 'link' in json and not json['link'] is None:
        link = json['link']
    else:
        raise Exception('Недействительная ссылка')

    matches = re.match(r'https:\/\/t.me\/joinchat\/(.*)', link)

    link_hash = matches.group(1)

    if link_hash != "":
        clients = ClientManager().get_clients()
        print(clients)
        for client in clients.values():
            try:
                print('client ', client)
                updates = await client(functions.messages.ImportChatInviteRequest(link_hash))
                print('updates ', updates)
                print('updates.to_dict() ', updates.to_dict())
                channel = updates['chats'][0]
                print('channel ', channel)
                print('channel.to_dict() ', channel.to_dict())

                return channel.to_dict()
                # channel = Channel(channel)

                # return channel.serialize()
            # except errors.FloodWaitError as e:
            #     print('Have to sleep', e.seconds, 'seconds')
            #     time.sleep(e.seconds)
            except errors.rpcbaseerrors.RPCError as ex:
                if not type(ex) == errors.rpcbaseerrors.FloodError \
                    or not type(ex) == errors.rpcbaseerrors.TimedOutError:
                    return ex.__dict__

    return ""


async def main():
    await hypercorn.asyncio.serve(app, hypercorn.Config())

if __name__ == '__main__':
    app.run(debug = SERVER['debug'], host = SERVER['host'], port = SERVER['port'])