import os
import hypercorn.asyncio
from telethon.sync import TelegramClient

from config import SERVER
from quart import Quart, request
app = Quart(__name__)

from models.Channel import Channel

client = TelegramClient(
    os.environ['TELEGRAM_USERNAME'], 
    os.environ['TELEGRAM_API_ID'], 
    os.environ['TELEGRAM_API_HASH']
)

# Connect the client before we start serving with Quart
@app.before_serving
async def startup():
    try:
        await client.connect()
        print('connect')
    except:
        await client.start(os.environ['TELEGRAM_PHONE'])
        print(os.environ['TELEGRAM_PHONE'])
        # await client.start(os.environ['TELEGRAM_PHONE'])

# After we're done serving (near shutdown), clean up the client
@app.after_serving
async def cleanup():
    await client.disconnect()

@app.route('/logout', methods=['GET'])
async def logout():
    if client.is_connected():
        await client.log_out()
    return ""

@app.route('/check', methods=['POST'])
async def check():
    json = await request.json

    if 'link' in json:
        link = json['link']

    if link is None:
        raise Exception('Недействительная ссылка')

    
    channel = await client.get_entity(link)
    channel = Channel(channel)

    return channel.serialize()

async def main():
    await hypercorn.asyncio.serve(app, hypercorn.Config())

if __name__ == '__main__':
    app.run(debug = SERVER['debug'], host = SERVER['host'], port = SERVER['port'])