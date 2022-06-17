from django.contrib import admin
from django.contrib.admin import display
import base.models as base_models


class VersionBotInline(admin.TabularInline):
    readonly_fields = ("name", "wait", "token")
    template = "admin/view_inline/tabular.html"
    model = base_models.Bot
    extra = 0
    can_delete = False

    def has_add_permission(self, request, obj=None):
        return False


class ChatsInline(admin.TabularInline):
    readonly_fields = ("is_using", "chat", "created_at")
    template = "admin/view_inline/chat_phone.html"
    model = base_models.ChatPhone
    extra = 0
    can_delete = False

    def has_add_permission(self, request, obj=None):
        return False


class PhoneAdmin(admin.ModelAdmin):
    list_display = ("id", "number", "first_name", "status", "status_text", "created_at", "chats", "api")
    date_hierarchy = "created_at"
    search_fields = ["number"]
    readonly_fields = ("internal_id", "created_at", "wait") #  "token_verify",
    inlines = (VersionBotInline, ChatsInline)
    ordering = ["-created_at"]
    list_filter = ("status",)

    def status(self, instance):
        return instance.get_status_text
    status.short_description = u"статус"

    # def token_verify(self, instance):
    #     return instance.token_is_valid
    # token_verify.short_description = u"Верификая сессии"

    def chats(self, instance):
        return instance.chatphone_set.count()
    status.short_description = u"чаты"


class VersionChatInline(admin.TabularInline):
    readonly_fields = ("id", "created_at", "body")
    model = base_models.ChatLog
    extra = 0
    can_delete = False

    def has_add_permission(self, request, obj=None):
        return False


class ChatAdmin(admin.ModelAdmin):
    list_display = [
        "link", "get_type", "title", "internal_id", "status", "status_text", "description", "get_phones", "get_members",
        "get_messages", "total_members", "total_messages", "id"
    ]
    search_fields = ["id", "link", "title", "description"]
    readonly_fields = ["id", "internal_id"]
    list_filter = (
        "status",
        ("internal_id", admin.EmptyFieldListFilter),
    )
    inlines = (VersionChatInline,)

    def get_phones(self, instance):
        return instance.chatphone_set.count()
    get_phones.short_description = u"phones"

    def get_members(self, instance):
        return instance.chatmember_set.count()
    get_members.short_description = u"members"

    def get_messages(self, instance):
        return instance.message_set.count()
    get_messages.short_description = u"messages"

    def get_type(self, instance):
        return instance.get_type
    get_type.short_description = u"type"


class ChatPhoneAdmin(admin.ModelAdmin):
    list_display = ["id", "get_chat", "get_phone", "is_using", "created_at"]
    search_fields = ["id", "chat__title", "phone__number"]
    readonly_fields = ["chat"]

    @display(ordering='phone', description='Phone')
    def get_phone(self, obj):
        return obj.phone.number

    @display(ordering='chat', description='Chat')
    def get_chat(self, obj):
        return obj.chat.title


class ChatMemberRoleAdmin(admin.ModelAdmin):
    list_display = ["id", "get_member", "get_chat", "title", "created_at"]
    search_fields = ["title"]
    readonly_fields = ["member"]

    @display(description='Member')
    def get_member(self, obj):
        return obj.member.member.username

    @display(description='Chat')
    def get_chat(self, obj):
        return obj.member.chat.title


class ChatLogAdmin(admin.ModelAdmin):
    list_display = ("id", "chat", "body", "created_at")


class MemberAdmin(admin.ModelAdmin):
    list_display = ["internal_id", "username", "first_name", "last_name", "phone", "about", "created_at"]
    search_fields = ["username", "phone", "internal_id", "first_name", "last_name"]
    list_filter = ["created_at"]


class MemberMediaAdmin(admin.ModelAdmin):
    list_display = ("id", "member", "internal_id", "date")
    search_fields = ["internal_id"]


class HostAdmin(admin.ModelAdmin):
    list_display = ("id", "name", "local_ip", "created_at")
    search_fields = ["name"]


class ChatMemberAdmin(admin.ModelAdmin):
    list_display = ["id", "get_chat", "get_member", "date"]
    search_fields = ["id", "chat__id", "member__id", "chat__link", "chat__title", "member__username"]
    readonly_fields = ["member", "chat"]

    @display(ordering='chat__title', description='Chat')
    def get_chat(self, obj):
        return obj.chat.title

    @display(description='Member')
    def get_member(self, obj):
        return obj.member.username


class ParserAdmin(admin.ModelAdmin):
    list_display = ("id", "created_at", "status", "api_id", "host", "phones")
    search_fields = ["api_id"]

    def phones(self, instance):
        return instance.phone_set.count()
    phones.short_description = u"телефоны"


class MessageAdmin(admin.ModelAdmin):
    list_display = ["id", "member_id", "get_member", "text", "created_at"]
    readonly_fields = ["member", "reply_to", "chat", "date", "internal_id"]
    search_fields = ["id"]

    @display(description='Member username')
    def get_member(self, obj):
        if obj.member:
            return obj.member.member.username
        return None


class ChatMediaAdmin(admin.ModelAdmin):
    list_display = ["id", "chat", "date"]
    readonly_fields = ["chat"]


class BotAdmin(admin.ModelAdmin):
    list_display = ("id", "phone", "name", "created_at", "get_token_status", "status")

    def status(self, instance):
        return instance.get_status_text
    status.short_description = u"статус"

    def get_token_status(self, instance):
        return "Токен валиден" if instance.token_is_valid else "Ошибка валидации токена"
    get_token_status.short_description = u"проверка токена"


class SubscriptionAdmin(admin.ModelAdmin):
    list_display = ("id", "title")


class TaskAdmin(admin.ModelAdmin):
    list_display = ("id", "status", "type", "created_at")
    readonly_fields = ["id", "status", "type", "created_at", "chat", "status_text"]


admin.site.register(base_models.Phone, PhoneAdmin)
admin.site.register(base_models.Chat, ChatAdmin)
admin.site.register(base_models.ChatLog, ChatLogAdmin)
admin.site.register(base_models.ChatPhone, ChatPhoneAdmin)
admin.site.register(base_models.Member, MemberAdmin)
admin.site.register(base_models.Parser, ParserAdmin)
admin.site.register(base_models.Host, HostAdmin)
admin.site.register(base_models.MemberMedia, MemberMediaAdmin)
admin.site.register(base_models.ChatMember, ChatMemberAdmin)
admin.site.register(base_models.ChatMemberRole, ChatMemberRoleAdmin)
admin.site.register(base_models.ChatMedia, ChatMediaAdmin)
admin.site.register(base_models.Bot, BotAdmin)
admin.site.register(base_models.Subscription, SubscriptionAdmin)
admin.site.register(base_models.Message, MessageAdmin)
admin.site.register(base_models.Task, TaskAdmin)

