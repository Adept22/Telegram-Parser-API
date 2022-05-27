import os
import uuid
import asyncio
import requests
from datetime import datetime
from django.db import models
from django.conf import settings
from django.utils import timezone
from telethon.sessions import StringSession
from django.db.models.signals import post_save, pre_save
from django.dispatch import receiver
from telethon import TelegramClient, sessions, sync
from base.tasks import make_telegram_bot


class AbstractUUID(models.Model):
    """ Абстрактная модель для использования UUID в качестве PK."""
    # Параметр blank=True позволяет работать с формами, он никогда не
    # будет пустым, см. метод save()

    id = models.UUIDField(primary_key=True, default=uuid.uuid4, editable=False)

    class Meta:
        abstract = True


class Phone(AbstractUUID):
    number = models.CharField(u'номер', max_length=20, blank=False)
    first_name = models.CharField(u'first name', max_length=255, blank=True)
    is_verified = models.BooleanField(u'is verified', default=True)
    is_banned = models.BooleanField(u'is banned', default=False)
    created = models.DateTimeField(u'дата создания', auto_now_add=True)
    code = models.CharField(u'code', max_length=10, blank=True)
    session = models.CharField(u'session', max_length=512, blank=True)
    internal_id = models.BigIntegerField(blank=True, null=True)
    last_name = models.CharField(u'last name', max_length=255, blank=True)
    wait = models.DateTimeField(blank=True, null=True)

    class Meta:
        verbose_name = u'Phone'
        verbose_name_plural = u'Phones'

    def __str__(self):
        return u'{}. {}'.format(self.id, self.number)

    def _get_status_text(self):
        if self.wait and self.wait > timezone.now():
            return 'Пауза {} сек.'.format((self.wait-timezone.now()).seconds)
        return 'Готов'
    get_status_text = property(_get_status_text)

    def _make_telegram_bot(self):
        make_telegram_bot.apply_async((self.id,))
        return
    make_telegram_bot = property(_make_telegram_bot)

    def _token_is_valid(self):
        if self.session:
            loop = asyncio.new_event_loop()
            asyncio.set_event_loop(loop)
            client = TelegramClient(
                session=sessions.StringSession("{}".format(self.session)),
                api_id=settings.API_ID,
                api_hash=settings.API_HASH,
                loop=loop
            )
            client.connect()
            return client.is_user_authorized()
        return False
    token_is_valid = property(_token_is_valid)


class Member(AbstractUUID):
    internal_id = models.IntegerField(blank=True, null=True)
    username = models.CharField(u'username', max_length=255, blank=True)
    first_name = models.CharField(u'first name', max_length=255, blank=True, null=True)
    last_name = models.CharField(u'last name', max_length=255, blank=True, null=True)
    about = models.TextField(u'about', blank=True, null=True)
    phone = models.CharField(u'phone', max_length=255, blank=True)
    created = models.DateTimeField(u'дата создания', auto_now_add=True)

    class Meta:
        verbose_name = u'Member'
        verbose_name_plural = u'Members'

    def __str__(self):
        return u'{}. {}'.format(self.id, self.username)


def attachment_path(instance, filename):
    os.umask(0)
    att_path = os.path.join(settings.MEDIA_ROOT, "attachments")
    if settings.DEFAULT_FILE_STORAGE == "django.core.files.storage.FileSystemStorage":
        if not os.path.exists(att_path):
            os.makedirs(att_path, 755)
    return os.path.join("attachments", filename)


class MemberMedia(AbstractUUID):
    member = models.ForeignKey(Member, verbose_name=u'member media', blank=True, null=True, on_delete=models.CASCADE)
    path = models.CharField(u'path', max_length=255, blank=True, null=True)
    created = models.DateTimeField(u'дата создания', auto_now_add=True)
    internal_id = models.BigIntegerField(u'internal id', blank=True, null=True)
    date = models.DateTimeField(u'date', blank=True, null=True)
    file = models.FileField(u'файл', upload_to=attachment_path, max_length=1000, blank=True, null=True)

    # def delete(self, *args, **kwargs):
    #     file_path = os.path.join(settings.MEDIA_ROOT, str(self.file))
    #     if os.path.exists(file_path):
    #         os.remove(file_path)
    #     super(MemberMedia, self).delete()

    class Meta:
        verbose_name = u'MemberMedia'
        verbose_name_plural = u'MemberMedias'

    def __str__(self):
        return u'{}. {}'.format(self.id, self.member)


class Message(AbstractUUID):
    member = models.ForeignKey(Member, verbose_name=u'member', blank=True, null=True, on_delete=models.CASCADE)
    reply_to_id = models.UUIDField(default=uuid.uuid4)
    internal_id = models.BigIntegerField(u'internal id', blank=True, null=True)
    text = models.TextField()
    is_pinned = models.BooleanField(u'is pinned', default=False)
    forwarded_from_id = models.BigIntegerField(u'forwarded from id', blank=True, null=True)
    forwarded_from_name = models.CharField(max_length=255, blank=True, null=True)
    created = models.DateTimeField(u'дата создания', auto_now_add=True)
    chat_id = models.UUIDField(default=uuid.uuid4)
    grouped_id = models.BigIntegerField(u'grouped id', blank=True, null=True)
    date = models.DateTimeField(u'date', blank=True, null=True)

    class Meta:
        verbose_name = u'Message'
        verbose_name_plural = u'Messages'

    def __str__(self):
        return u'{}. {}'.format(self.id, self.text)


class MessageMedia(AbstractUUID):
    message = models.ForeignKey(Message, verbose_name=u'message media', on_delete=models.CASCADE)
    path = models.CharField(u'path', max_length=255, blank=True, null=True)
    created = models.DateTimeField(u'дата создания', auto_now_add=True)
    internal_id = models.BigIntegerField(u'internal id', blank=True, null=True)
    date = models.DateTimeField(u'date', blank=True, null=True)
    # file = models.FileField(u'файл', upload_to=attachment_path, max_length=1000, blank=True, null=True)

    # def delete(self, *args, **kwargs):
    #     file_path = os.path.join(settings.MEDIA_ROOT, str(self.file))
    #     if os.path.exists(file_path):
    #         os.remove(file_path)
    #     super(MemberMedia, self).delete()

    class Meta:
        verbose_name = u'MessageMedia'
        verbose_name_plural = u'MessageMedias'

    def __str__(self):
        return u'{}. {}'.format(self.id, self.message)


class Chat(AbstractUUID):
    NEW_STATUS = 1
    IN_PROGRESS_STATUS = 2
    DONE_STATUS = 3
    FAILED_STATUS = 4

    STATUS_CHOICES = (
        (NEW_STATUS, u'Новый'),
        (IN_PROGRESS_STATUS, u'В работе'),
        (DONE_STATUS, u'Готово'),
        (FAILED_STATUS, u'Ошибка'),
    )

    internal_id = models.BigIntegerField('internal id', blank=True, null=True)
    title = models.CharField(u'title', max_length=255, blank=True)
    created = models.DateTimeField(u'дата создания', auto_now_add=True)
    link = models.CharField(u'link', max_length=255, blank=False, unique=True)
    is_available = models.BooleanField(u'is available', default=False)
    description = models.TextField(u'description', blank=True, null=True)
    system_title = models.CharField(u'system title', max_length=255, blank=False, null=True)
    system_description = models.TextField(u'system description', blank=True, null=True)
    lat = models.DecimalField(max_digits=22, decimal_places=16, blank=True, null=True)
    lon = models.DecimalField(max_digits=22, decimal_places=16, blank=True, null=True)
    parser = models.ForeignKey('Parser', verbose_name=u'parser', on_delete=models.CASCADE, null=True, blank=True)
    date = models.DateField(u'date', blank=True, null=True)

    class Meta:
        verbose_name = u'Chat'
        verbose_name_plural = u'Chats'

    def __str__(self):
        return u'{}. {}'.format(self.id, self.link)


class Host(AbstractUUID):
    created = models.DateTimeField(u'дата создания', auto_now_add=True)
    public_ip = models.CharField(max_length=15, blank=True, null=True)
    local_ip = models.CharField(max_length=15, blank=True, null=True)
    name = models.CharField(max_length=255, blank=True, null=True)

    class Meta:
        verbose_name = u'Host'
        verbose_name_plural = u'Hosts'

    def __str__(self):
        return u'{}. {}'.format(self.id, self.name)


class Parser(models.Model):
    id = models.UUIDField(default=uuid.uuid4, editable=False, unique=True, primary_key=True)
    host = models.ForeignKey(Host, verbose_name=u'host', blank=True, null=True, on_delete=models.CASCADE)
    created = models.DateTimeField(u'дата создания', auto_now_add=True)
    status = models.CharField(max_length=20, blank=True, null=True)
    api_id = models.IntegerField(u'api id', blank=True)
    api_hash = models.CharField(max_length=255, blank=True, null=True)

    class Meta:
        verbose_name = u'Parser'
        verbose_name_plural = u'Parsers'

    def __str__(self):
        return u'{}. {}'.format(self.id, self.api_id)


# @receiver(pre_save, sender=Chat)
# def create_chat(sender, instance, *args, **kwargs):
#     # get_chat_info(chat_id=instance.id)
#     try:
#         phone = Phone.objects.first() #filter(is_banned=False, is_verified=True)
#     except Phone.DoesNotExist:
#         return False
#     loop = asyncio.new_event_loop()
#     asyncio.set_event_loop(loop)
#     client = TelegramClient(
#         connection_retries=-1,
#         retry_delay=5,
#         session=sessions.StringSession(phone.session),
#         api_id=settings.API_ID,
#         api_hash=settings.API_HASH
#     )
#     client.connect()
#     print(client.get_me())


class ChatPhone(AbstractUUID):
    chat = models.ForeignKey(Chat, verbose_name=u'chat', blank=True, null=True, on_delete=models.CASCADE)
    phone = models.ForeignKey(Phone, verbose_name=u'phone', blank=True, null=True, on_delete=models.CASCADE)
    created = models.DateTimeField(u'дата создания', auto_now_add=True)
    is_using = models.BooleanField(u'is using', default=False)

    class Meta:
        verbose_name = u'ChatPhone'
        verbose_name_plural = u'ChatPhones'

    def __str__(self):
        return u'{}. {} - {}'.format(self.id, self.chat, self.phone)


class ChatMember(AbstractUUID):
    chat = models.ForeignKey(Chat, verbose_name=u'chat', blank=True, null=True, on_delete=models.CASCADE)
    member = models.ForeignKey(Member, verbose_name=u'member', blank=True, null=True, on_delete=models.CASCADE)
    is_left = models.BooleanField(u'is left', default=False)
    created = models.DateTimeField(u'дата создания', auto_now_add=True)
    date = models.DateTimeField(u'дата', blank=True, null=True)

    class Meta:
        verbose_name = u'ChatMember'
        verbose_name_plural = u'ChatMembers'

    def __str__(self):
        return u'{}. {} - {}'.format(self.id, self.chat, self.member)


class ChatMedia(AbstractUUID):
    chat = models.ForeignKey(Chat, verbose_name=u'chat', blank=True, null=True, on_delete=models.CASCADE)
    path = models.CharField(max_length=3000, blank=True, null=True)
    created = models.DateTimeField(u'дата создания', auto_now_add=True)
    internal_id = models.BigIntegerField(u'internal id', blank=True, null=True)
    date = models.DateTimeField(u'дата', blank=True, null=True)
    file = models.FileField(u'файл', upload_to=attachment_path, max_length=1000, blank=True, null=True)

    class Meta:
        verbose_name = u'ChatMedia'
        verbose_name_plural = u'ChatMedias'

    def __str__(self):
        return u'{}. {}'.format(self.id, self.chat)

    # def delete(self, *args, **kwargs):
    #     file_path = os.path.join(settings.MEDIA_ROOT, str(self.file))
    #     if os.path.exists(file_path):
    #         os.remove(file_path)
    #     super(ChatMedia, self).delete()


# class Session(models.Model):
#     phone = models.ForeignKey(Phone, verbose_name=u'phone', on_delete=models.CASCADE)
#     token = models.CharField(u'token', max_length=512, blank=False)
#     created = models.DateTimeField(u'дата создания', auto_now_add=True)
#     wait = models.IntegerField(default=0)
#
#     class Meta:
#         verbose_name = u'Session'
#         verbose_name_plural = u'Sessions'
#
#     def __str__(self):
#         return u'{}. {}'.format(self.id, self.phone)


class Bot(models.Model):
    name = models.CharField(u'название', max_length=255, blank=True)
    token = models.CharField(u'token', max_length=46, blank=True)
    phone = models.ForeignKey(Phone, verbose_name=u'телефон', on_delete=models.CASCADE)
    created = models.DateTimeField(u'дата создания', auto_now_add=True)
    wait = models.PositiveIntegerField('ожидание', default=0)

    class Meta:
        verbose_name = u'Bot'
        verbose_name_plural = u'Bots'

    def __str__(self):
        return u'{}. {}'.format(self.id, self.name)

    def _get_status_text(self):
        if self.wait > 0:
            return 'Пауза {} сек.'.format(self.wait)
        return 'Готов'
    get_status_text = property(_get_status_text)

    def _get_session(self):
        if self.token is None:
            return None
        loop = asyncio.new_event_loop()
        asyncio.set_event_loop(loop)
        bot = TelegramClient(StringSession(), settings.API_ID, settings.API_HASH, loop=loop).start(
            bot_token=u"{}".format(self.token), max_attempts=10
        )
        return bot.session.save()
    get_session = property(_get_session)

    def _token_is_valid(self):
        if len(self.token) == 0:
            return False
        r = requests.post(url="https://api.telegram.org/bot{}/getMe".format(self.token), data={})
        if r.status_code == 200:
            return True
        return False
    token_is_valid = property(_token_is_valid)


class ChatLog(models.Model):
    body = models.TextField(u'ошибка', blank=True)
    chat = models.ForeignKey(Chat, verbose_name=u'chat', on_delete=models.CASCADE)
    created = models.DateTimeField(u'дата создания', auto_now_add=True)

    class Meta:
        verbose_name = u'ChatLog'
        verbose_name_plural = u'ChatLogs'

    def __str__(self):
        return u'{}. {}'.format(self.id, self.chat)


class Role(AbstractUUID):
    member = models.ForeignKey(Member, verbose_name=u'member', blank=True, null=True, on_delete=models.CASCADE)
    title = models.CharField(u'title', max_length=100, blank=True, null=True)
    code = models.CharField(u'code', max_length=10, blank=True)
    created = models.DateTimeField(u'дата создания', auto_now_add=True)

    class Meta:
        verbose_name = u'Role'
        verbose_name_plural = u'Roles'

    def __str__(self):
        return u'{}. {}'.format(self.id, self.title)

