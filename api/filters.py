import django_filters
import base.models as base_models


class ChatFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.Chat
        fields = ('status', 'link')


class PhoneFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.Phone
        fields = ('number', 'status')


class MessageFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.Message
        fields = ('internal_id',)


