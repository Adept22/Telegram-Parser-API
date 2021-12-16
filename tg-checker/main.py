import time
import re
import hypercorn.asyncio
import telethon
from telethon import functions, errors, types
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

    for phone in phones:
        print('')

        client = ClientManager().new(phone['phone'])

        try:
            await client.connect()

            if not await client.is_user_authorized():
                phone_code_hash = None

                try:
                    sent = await client.send_code_request(phone=phone['phone'])
                    print('sent', phone['phone'], sent)

                    phone_code_hash = sent.phone_code_hash
                except errors.RPCError as ex:
                    print('Exception', phone['phone'], ex.message)

                api.set('phone', { 'id': phone['id'], 'isVerified': False, 'phoneCodeHash': phone_code_hash })
            else:
                ClientManager().set(phone['id'], client)
                print('using', phone['phone'])
        except errors.RPCError as ex:
            print('Exception', phone['phone'], ex.message)

        time.sleep(1)


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

    link = re.sub(r'https?:\/\/t\.me\/', '', link)

    matches = re.match(r'(?:joinchat\/|\+)(\w{16})', link)

    hash = matches.group(1) if not matches is None else None

    clients = ClientManager().get_clients()

    for client in clients.values():
        try:
            if hash is None:
                # TODO: какого-то хрена не вступает
                reguest = functions.channels.JoinChannelRequest(channel=link)
            else:
                reguest = functions.messages.ImportChatInviteRequest(hash=hash)

            updates = await client(reguest)
                
            channel = updates.chats[0]

            if not channel is None:
                try:
                    users = await client.get_participants(channel)
                    print('users', users)
                except ex:
                    print(ex)
                    continue
            
            return { 'id': channel.id, 'title': channel.title }
        except errors.rpcbaseerrors.FloodError as ex:
            print(ex.message)
            time.sleep("FLOOD", ex.seconds)
        except:
            continue

    return "ok"


async def main():
    await hypercorn.asyncio.serve(app, hypercorn.Config())

if __name__ == '__main__':
    app.run(debug = SERVER['debug'], host = SERVER['host'], port = SERVER['port'])