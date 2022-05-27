from django.contrib import admin
import base.models as base_models


class VersionBotInline(admin.TabularInline):
    readonly_fields = ('name', 'wait', 'token')
    template = 'admin/view_inline/tabular.html'
    model = base_models.Bot
    extra = 0

    def has_delete_permission(self, request, obj=None):
        return False

    def has_add_permission(self, request, obj=None):
        return False


class PhoneAdmin(admin.ModelAdmin):
    list_display = ('id', 'number', 'first_name', 'is_verified', 'is_banned', 'created', 'status')
    date_hierarchy = 'created'
    search_fields = ['number']
    readonly_fields = ("internal_id", "created", "token_verify", "wait")
    inlines = (VersionBotInline,)

    def status(self, instance):
        return instance.get_status_text
    status.short_description = u"статус"

    def token_verify(self, instance):
        return instance.token_is_valid
    token_verify.short_description = u"Верификая сессии"


class VersionChatInline(admin.TabularInline):
    readonly_fields = ('id', 'created', 'body')
    model = base_models.ChatLog
    extra = 0

    def has_delete_permission(self, request, obj=None):
        return False

    def has_add_permission(self, request, obj=None):
        return False


class ChatAdmin(admin.ModelAdmin):
    list_display = ('id', 'link', 'title', 'internal_id', 'is_available', 'description')
    search_fields = ['link', 'title', 'description']
    readonly_fields = ("id",)
    list_filter = (
        ("internal_id", admin.EmptyFieldListFilter),
        ("title", admin.EmptyFieldListFilter),
        'is_available',
    )
    inlines = (VersionChatInline,)


class ChatPhoneAdmin(admin.ModelAdmin):
    list_display = ('id', 'chat', 'phone', 'is_using')
    search_fields = ['chat', 'phone']


class RoleAdmin(admin.ModelAdmin):
    list_display = ('id', 'title', 'created')


class ChatLogAdmin(admin.ModelAdmin):
    list_display = ('id', 'chat', 'body', 'created')


class MemberAdmin(admin.ModelAdmin):
    list_display = ('id', 'internal_id', 'username', 'first_name', 'last_name', 'phone', 'about')
    search_fields = ['username', 'phone']


class MemberMediaAdmin(admin.ModelAdmin):
    list_display = ('id', 'member', 'internal_id', 'date')
    search_fields = ['internal_id']


class HostAdmin(admin.ModelAdmin):
    list_display = ('id', 'name', 'local_ip', 'created')
    search_fields = ['name']


class ChatMemberAdmin(admin.ModelAdmin):
    list_display = ('id', 'chat', 'member', 'date')


class ParserAdmin(admin.ModelAdmin):
    list_display = ('id', 'created', 'status', 'api_id', 'host')
    search_fields = ['api_id']


class ChatMediaAdmin(admin.ModelAdmin):
    list_display = ('id', 'chat', 'date')


class BotAdmin(admin.ModelAdmin):
    list_display = ('id', 'phone', 'name', 'created', 'get_token_status', 'status')

    def status(self, instance):
        return instance.get_status_text
    status.short_description = u"статус"

    def get_token_status(self, instance):
        return "Токен валиден" if instance.token_is_valid else "Ошибка валидации токена"
    get_token_status.short_description = u"проверка токена"


admin.site.register(base_models.Phone, PhoneAdmin)
admin.site.register(base_models.Chat, ChatAdmin)
admin.site.register(base_models.ChatLog, ChatLogAdmin)
admin.site.register(base_models.ChatPhone, ChatPhoneAdmin)
admin.site.register(base_models.Member, MemberAdmin)
admin.site.register(base_models.Parser, ParserAdmin)
admin.site.register(base_models.Host, HostAdmin)
admin.site.register(base_models.MemberMedia, MemberMediaAdmin)
admin.site.register(base_models.ChatMember, ChatMemberAdmin)
admin.site.register(base_models.ChatMedia, ChatMediaAdmin)
admin.site.register(base_models.Bot, BotAdmin)
admin.site.register(base_models.Role, RoleAdmin)