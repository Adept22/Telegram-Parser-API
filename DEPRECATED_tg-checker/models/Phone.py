import os
from telethon import sync

class Phone:
    def __init__(self, __dict__):
        if __dict__ is None:
            raise Exception('Unexpected phone dictionary')
            
        if not 'id' in __dict__ or __dict__['id'] is None:
            raise Exception('Unexpected phone id')

        if not 'phone' in __dict__ or __dict__['phone'] is None:
            raise Exception('Unexpected phone id')

        self.__dict__ = __dict__

        self.id = self.__dict__['id']
        self.number = self.__dict__['phone']
        self.username = self.__dict__['username']
        self.first_name = self.__dict__['firstName']
        self.is_verified = self.__dict__['isVerified']
        self.is_banned = self.__dict__['isBanned']
        self.code_hash = self.__dict__.get('phoneCodeHash')
        self.created_at = self.__dict__['createdAt']
        self.chats_count = self.__dict__['chatsCount']
        self.client = sync.TelegramClient(
            session='sessions/' + str(self.number), 
            api_id=os.environ['TELEGRAM_API_ID'], 
            api_hash=os.environ['TELEGRAM_API_HASH']
        )
