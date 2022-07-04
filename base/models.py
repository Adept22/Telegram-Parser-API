import uuid
from django.db import models


class BaseModel(models.Model):
    """Абстрактная модель для использования UUID в качестве PK."""

    id = models.UUIDField(primary_key=True, default=uuid.uuid1, editable=False)
    created_at = models.DateTimeField(u"дата создания", auto_now_add=True)

    class Meta:
        abstract = True


class Task(BaseModel):
    """Абстрактная модель задания."""

    STATUS_CREATED = 0
    STATUS_STARTED = 1
    STATUS_SUCCESS = 2
    STATUS_FAILURE = 3
    STATUS_REVOKED = 4

    STATUS_CHOICES = (
        (STATUS_CREATED, u"Создан"),
        (STATUS_STARTED, u"В работе"),
        (STATUS_SUCCESS, u"Выполнен"),
        (STATUS_FAILURE, u"Ошибка"),
        (STATUS_REVOKED, u"Отозван"),
    )

    status = models.IntegerField(u"status", default=STATUS_CREATED, choices=STATUS_CHOICES)
    status_text = models.TextField(u"status text", blank=True, null=True)
    started_at = models.DateTimeField(u"дата запуска", blank=True, null=True)
    ended_at = models.DateTimeField(u"дата завершения", blank=True, null=True)

    class Meta:
        abstract = True


class Link(BaseModel):
    STATUS_CREATED = 0
    STATUS_RESOLVED = 1
    STATUS_FAILURE = 2

    STATUS_CHOICES = (
        (STATUS_CREATED, u"Создана"),
        (STATUS_RESOLVED, u"Определена"),
        (STATUS_FAILURE, u"Ошибка")
    )

    link = models.CharField(u"link", max_length=255, blank=False, unique=True)
    status = models.IntegerField(u"status", default=STATUS_CREATED, choices=STATUS_CHOICES)
    status_text = models.TextField(u"status text", blank=True, null=True)

    class Meta:
        verbose_name = u"Link"
        verbose_name_plural = u"Links"
        db_table = 'telegram\".\"links'


class Host(BaseModel):
    public_ip = models.CharField(max_length=15, blank=True, null=True)
    local_ip = models.CharField(max_length=15, unique=True)
    name = models.CharField(max_length=255, blank=True, null=True)

    class Meta:
        verbose_name = u"Host"
        verbose_name_plural = u"Hosts"
        db_table = 'telegram\".\"hosts'

    def __str__(self):
        return u"{}. {}".format(self.id, self.name)


class Parser(BaseModel):
    STATUS_NEW = 0
    STATUS_IN_PROGRESS = 1
    STATUS_FAILED = 2

    STATUS_CHOICES = (
        (STATUS_NEW, u"Создан"),
        (STATUS_IN_PROGRESS, u"В работе"),
        (STATUS_FAILED, u"Ошибка"),
    )

    host = models.ForeignKey(Host, verbose_name=u"host", on_delete=models.DO_NOTHING)
    status = models.IntegerField(u"status", default=STATUS_NEW, choices=STATUS_CHOICES)

    class Meta:
        verbose_name = u"Parser"
        verbose_name_plural = u"Parsers"
        db_table = 'telegram\".\"parsers'

    def __str__(self):
        return u"{}".format(self.id)


class Phone(BaseModel):
    STATUS_CREATED = 0
    STATUS_READY = 1
    STATUS_FLOOD = 2
    STATUS_FULL = 3
    STATUS_BAN = 4

    STATUS_CHOICES = (
        (STATUS_CREATED, u"Создан"),
        (STATUS_READY, u"Готов"),
        (STATUS_FLOOD, u"В ожидании"),
        (STATUS_FULL, u"Полон"),
        (STATUS_BAN, u"Забанен"),
    )

    number = models.CharField(u"номер", max_length=20, unique=True)
    first_name = models.CharField(u"first name", max_length=255, blank=True, null=True)
    last_name = models.CharField(u"last name", max_length=255, blank=True, null=True)
    status = models.IntegerField(u"status", default=STATUS_CREATED, choices=STATUS_CHOICES)
    status_text = models.TextField(u"status text", blank=True, null=True)
    parser = models.ForeignKey(Parser, verbose_name=u"parser", on_delete=models.DO_NOTHING)
    code = models.CharField(u"code", max_length=10, blank=True, null=True)
    session = models.CharField(u"session", max_length=512, null=True, blank=True, unique=True)
    internal_id = models.BigIntegerField(u"internal_id", blank=True, null=True, unique=True)
    api = models.JSONField(blank=True, null=True)
    takeout = models.BooleanField(u"takeout", default=True)

    class Meta:
        verbose_name = u"Phone"
        verbose_name_plural = u"Phones"
        db_table = 'telegram\".\"phones'

    def __str__(self):
        return u"{}. {}".format(self.id, self.number)


class PhoneTask(Task):
    TYPE_AUTH = 0

    TYPE_CHOICES = (
        (TYPE_AUTH, u"Авторизация"),
    )

    phone = models.ForeignKey(Phone, verbose_name=u"phone", on_delete=models.DO_NOTHING)
    type = models.IntegerField(u"type", choices=TYPE_CHOICES)

    class Meta:
        verbose_name = u"PhoneTask"
        verbose_name_plural = u"PhoneTasks"
        db_table = 'telegram\".\"phones_tasks'


class Chat(BaseModel):
    STATUS_CREATED = 0
    STATUS_AVAILABLE = 1
    STATUS_FAILED = 2

    STATUS_CHOICES = (
        (STATUS_CREATED, u"Создан"),
        (STATUS_AVAILABLE, u"Доступен"),
        (STATUS_FAILED, u"Ошибка"),
    )

    internal_id = models.BigIntegerField("internal id", null=True, blank=True, unique=True)
    title = models.CharField(u"title", max_length=255, blank=True)
    status = models.IntegerField(u"status", default=STATUS_CREATED, choices=STATUS_CHOICES, blank=True)
    status_text = models.TextField(u"status text", blank=True, null=True)
    description = models.TextField(u"description", blank=True, null=True)
    parser = models.ForeignKey(Parser, verbose_name=u"parser", on_delete=models.DO_NOTHING)
    date = models.DateField(u"date", blank=True, null=True)
    total_messages = models.IntegerField(u"total messages", default=0)
    total_members = models.IntegerField(u"total members", default=0)

    class Meta:
        verbose_name = u"Chat"
        verbose_name_plural = u"Chats"
        db_table = 'telegram\".\"chats'


class ChatLink(Link):
    chat = models.ForeignKey(Chat, verbose_name=u"chat", on_delete=models.DO_NOTHING)

    class Meta:
        verbose_name = u"ChatLink"
        verbose_name_plural = u"ChatLinks"
        db_table = 'telegram\".\"chats_links'


class ChatTask(Task):
    TYPE_MEMBER = 0
    TYPE_MESSAGE = 1
    TYPE_MONITORING = 2

    TYPE_CHOICES = (
        (TYPE_MEMBER, u"Участники"),
        (TYPE_MESSAGE, u"Сообщения"),
        (TYPE_MONITORING, u"Мониторинг"),
    )

    chat = models.ForeignKey(Chat, verbose_name=u"chat", on_delete=models.DO_NOTHING)
    type = models.IntegerField(u"type", choices=TYPE_CHOICES)

    class Meta:
        verbose_name = u"ChatTask"
        verbose_name_plural = u"ChatTasks"
        db_table = 'telegram\".\"chats_tasks'


class ChatPhone(BaseModel):
    chat = models.ForeignKey(Chat, verbose_name=u"chat", on_delete=models.DO_NOTHING)
    phone = models.ForeignKey(Phone, verbose_name=u"phone", on_delete=models.DO_NOTHING)
    is_using = models.BooleanField(u"is using", default=True)

    class Meta:
        verbose_name = u"ChatPhone"
        verbose_name_plural = u"ChatPhones"
        db_table = 'telegram\".\"chats_phones'
        constraints = [
            models.UniqueConstraint(fields=["chat", "phone"], name="chat_phone_unique"),
        ]


class ChatMedia(BaseModel):
    internal_id = models.BigIntegerField(u"internal id", unique=True)
    chat = models.ForeignKey(Chat, verbose_name=u"chat", on_delete=models.DO_NOTHING)
    path = models.CharField(max_length=3000, blank=True, null=True)
    date = models.DateTimeField(u"дата", blank=True, null=True)

    class Meta:
        verbose_name = u"ChatMedia"
        verbose_name_plural = u"ChatMedias"
        db_table = 'telegram\".\"chats_medias'


class Member(BaseModel):
    internal_id = models.BigIntegerField(u"internal_id")
    username = models.CharField(u"username", max_length=255, blank=True, null=True)
    first_name = models.CharField(u"first name", max_length=255, blank=True, null=True)
    last_name = models.CharField(u"last name", max_length=255, blank=True, null=True)
    about = models.TextField(u"about", blank=True, null=True)
    phone = models.CharField(u"phone", max_length=255, blank=True, null=True)

    class Meta:
        verbose_name = u"Member"
        verbose_name_plural = u"Members"
        db_table = 'telegram\".\"members'


class MemberLink(Link):
    member = models.ForeignKey(Member, verbose_name=u"member", on_delete=models.DO_NOTHING)

    class Meta:
        verbose_name = u"MemberLink"
        verbose_name_plural = u"MemberLinks"
        db_table = 'telegram\".\"members_links'


class MemberMedia(BaseModel):
    member = models.ForeignKey(Member, verbose_name=u"member media", on_delete=models.DO_NOTHING)
    internal_id = models.BigIntegerField(u"internal id", unique=True)
    path = models.CharField(u"path", max_length=255, blank=True, null=True)
    date = models.DateTimeField(u"date", blank=True, null=True)

    class Meta:
        verbose_name = u"MemberMedia"
        verbose_name_plural = u"MemberMedias"
        db_table = 'telegram\".\"members_medias'


class ChatMember(BaseModel):
    chat = models.ForeignKey(Chat, verbose_name=u"chat", on_delete=models.DO_NOTHING)
    member = models.ForeignKey(Member, verbose_name=u"member", on_delete=models.DO_NOTHING)
    is_left = models.BooleanField(u"is left", default=False)
    date = models.DateTimeField(u"дата", blank=True, null=True)

    class Meta:
        verbose_name = u"ChatMember"
        verbose_name_plural = u"ChatMembers"
        db_table = 'telegram\".\"chats_members'
        constraints = [
            models.UniqueConstraint(fields=["chat", "member"], name="chat_member_unique"),
        ]


class ChatMemberRole(BaseModel):
    member = models.ForeignKey(ChatMember, verbose_name=u"member", on_delete=models.DO_NOTHING)
    title = models.CharField(u"title", max_length=100)
    code = models.CharField(u"code", max_length=10)

    class Meta:
        verbose_name = u"ChatMemberRole"
        verbose_name_plural = u"ChatMemberRoles"
        db_table = 'telegram\".\"chats_members_roles'
        constraints = [
            models.UniqueConstraint(fields=["member", "title", "code"], name="chat_member_role_unique"),
        ]


class Message(BaseModel):
    member = models.ForeignKey(ChatMember, verbose_name=u"member", on_delete=models.DO_NOTHING)
    reply_to = models.ForeignKey("self", verbose_name=u"reply_to", on_delete=models.DO_NOTHING, blank=True, null=True)
    internal_id = models.BigIntegerField(u"internal id")
    text = models.TextField(u"text", blank=True, null=True)
    is_pinned = models.BooleanField(u"is pinned", default=False)
    forwarded_from_id = models.BigIntegerField(u"forwarded from id", blank=True, null=True)
    forwarded_from_name = models.CharField(max_length=255, blank=True, null=True)
    chat = models.ForeignKey(Chat, verbose_name=u"chat", on_delete=models.DO_NOTHING)
    grouped_id = models.BigIntegerField(u"grouped id", blank=True, null=True)
    date = models.DateTimeField(u"date", blank=True, null=True)

    class Meta:
        verbose_name = u"Message"
        verbose_name_plural = u"Messages"
        db_table = 'telegram\".\"messages'
        constraints = [
            models.UniqueConstraint(fields=["internal_id", "chat"], name="message_unique"),
        ]


class MessageLink(Link):
    message = models.ForeignKey(Message, verbose_name=u"message", on_delete=models.DO_NOTHING)

    class Meta:
        verbose_name = u"MessageLink"
        verbose_name_plural = u"MessageLinks"
        db_table = 'telegram\".\"messages_links'


class MessageMedia(BaseModel):
    message = models.ForeignKey(Message, verbose_name=u"message media", on_delete=models.DO_NOTHING)
    internal_id = models.BigIntegerField(u"internal id")
    path = models.CharField(u"path", max_length=255, blank=True, null=True)
    date = models.DateTimeField(u"date", blank=True, null=True)

    class Meta:
        verbose_name = u"MessageMedia"
        verbose_name_plural = u"MessageMedias"
        db_table = 'telegram\".\"messages_medias'
        constraints = [
            models.UniqueConstraint(fields=["internal_id", "message"], name="message_media_unique"),
        ]


TypeTask = Task
TypeLink = Link
TypeHost = Host
TypeParser = Parser
TypePhone = Phone
TypePhoneTask = PhoneTask
TypeChat = Chat
TypeChatLink = ChatLink
TypeChatTask = ChatTask
TypeChatPhone = ChatPhone
TypeChatMedia = ChatMedia
TypeMember = Member
TypeMemberLink = MemberLink
TypeMemberMedia = MemberMedia
TypeChatMember = ChatMember
TypeChatMemberRole = ChatMemberRole
TypeMessage = Message
TypeMessageLink = MessageLink
TypeMessageMedia = MessageMedia
