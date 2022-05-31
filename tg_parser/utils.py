import telethon, telethon.sessions, re

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

LINK_RE = re.compile(
    r'(?:@|(?:https?:\/\/)?(?:www\.)?(?:telegram\.(?:me|dog)|t\.me)\/(?:@|joinchat\/|\+)?|'
    r'tg:\/\/(?:join|resolve)\?(?:invite=|domain=))'
    r'(?:[a-zA-Z0-9_.-](?:(?!__)\w){3,30}[a-zA-Z0-9_.-]|'
    r'gif|vid|pic|bing|wiki|imdb|bold|vote|like|coub)',
    re.IGNORECASE
)
HTTP_RE = re.compile(r'^(?:@|(?:https?://)?(?:www\.)?(?:telegram\.(?:me|dog)|t\.me))/(\+|joinchat/)?')
TG_RE = re.compile(r'^tg://(?:(join)|resolve)\?(?:invite|domain)=')

def parse_username(link: 'str') -> 'tuple[str | None, str | None]':
    link = link.strip()

    m = re.match(HTTP_RE, link) or re.match(TG_RE, link)

    if m:
        link = link[m.end():]
        is_invite = bool(m.group(1))

        if is_invite:
            return link, True
        else:
            link = link.rstrip('/')

    if telethon.utils.VALID_USERNAME_RE.match(link):
        return link.lower(), False
    else:
        return None, False
