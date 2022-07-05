import django_filters
import base.models as base_models


class LinkFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.Link
        fields = "__all__"


class HostFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.Host
        fields = "__all__"


class ParserFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.Parser
        fields = "__all__"


class PhoneFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.Phone
        fields = "__all__"
        exclude = ["api"]


class PhoneTaskFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.PhoneTask
        fields = "__all__"


class ChatFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.Chat
        fields = "__all__"


class ChatLinkFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.ChatLink
        fields = "__all__"


class ChatTaskFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.ChatTask
        fields = "__all__"


class ChatPhoneFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.ChatPhone
        fields = "__all__"


class ChatMediaFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.ChatMedia
        fields = "__all__"


class MemberFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.Member
        fields = "__all__"


class MemberLinkFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.MemberLink
        fields = "__all__"


class MemberMediaFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.MemberMedia
        fields = "__all__"


class ChatMemberFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.ChatMember
        fields = "__all__"


class ChatMemberRoleFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.ChatMemberRole
        fields = "__all__"


class MessageFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.Message
        fields = "__all__"


class MessageLinkFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.MessageLink
        fields = "__all__"


class MessageMediaFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.MessageMedia
        fields = "__all__"
