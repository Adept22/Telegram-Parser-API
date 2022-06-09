import django_filters
import base.models as base_models


class ChatFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.Chat
        fields = "__all__"


class PhoneFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.Phone
        fields = "__all__"
        exclude = ["api"]


class MessageFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.Message
        fields = "__all__"


class MemberFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.Member
        fields = "__all__"


class ChatMemberFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.ChatMember
        fields = "__all__"


class ChatMemberRoleFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.ChatMemberRole
        fields = "__all__"

