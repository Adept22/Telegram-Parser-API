import telethon, telethon.sessions

class TelegramClient(telethon.TelegramClient):
    from base.models import TypePhone

    def __init__(self, phone: 'TypePhone', *args, **kwargs):
        self.phone = phone

        super(TelegramClient, self).__init__(
            *args, 
            **kwargs, 
            connection_retries=-1,
            retry_delay=5, 
            session=telethon.sessions.StringSession(phone.session), 
            api_id=phone.parser.api_id, 
            api_hash=phone.parser.api_hash
        )

    async def start(self):
        from base.exceptions import ClientNotAvailableError

        if not self.is_connected():
            await self.connect()

        if await self.is_user_authorized() and await self.get_me() != None:
            return self
        else:
            raise ClientNotAvailableError(f'Phone {self.phone.id} not authorized')
            
    async def __aenter__(self):
        return await self.start()
    