import django_filters
import base.models as base_models


class ChatFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.Chat
        fields = ('is_available', 'link')


class PhoneFilter(django_filters.FilterSet):
    class Meta:
        model = base_models.Phone
        fields = ('number', 'is_verified', 'is_banned')

