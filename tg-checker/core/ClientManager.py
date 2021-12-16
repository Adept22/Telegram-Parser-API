import os
from decorators.singleton import singleton
from telethon import sync

@singleton
class ClientManager:
    def __init__(self):
        self.clients = {}

    def get_clients(self):
        return self.clients

    def set_clients(self, clients):
        self.clients = clients

        return self
    
    def new(self, phone):
        return sync.TelegramClient(
            session='sessions/' + str(phone), 
            api_id=os.environ['TELEGRAM_API_ID'], 
            api_hash=os.environ['TELEGRAM_API_HASH']
        )

    def has(self, id):
        return id in self.clients

    def get(self, id):
        return self.clients[id]

    def set(self, id, client):
        self.clients[id] = client

        return self
