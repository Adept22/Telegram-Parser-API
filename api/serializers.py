from rest_framework import serializers
import base.models as base_models


class WaitSerializer(serializers.Serializer):
    wait = serializers.IntegerField()


class BotListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.Bot
        fields = ('id', 'created', 'name', 'session')
        read_only_fields = ('id', 'created', 'session')


class PhoneListSerializer(serializers.ModelSerializer):
    internalId = serializers.IntegerField(source='internal_id')
    isBanned = serializers.BooleanField(source='is_banned')
    isVerified = serializers.BooleanField(source='is_verified')
    id = serializers.CharField(read_only=True)

    class Meta:
        model = base_models.Phone
        fields = ('id', 'number', 'internalId', 'session', 'first_name', 'isVerified', 'isBanned', 'code', 'created')
        read_only_fields = ('id', 'created')


class PhoneUpdateSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.Phone
        fields = ('id', 'internal_id', 'session', 'first_name', 'is_verified', 'is_banned', 'code', 'wait', 'created')
        read_only_fields = ('id', 'created')


class PhoneViewSerializer(serializers.ModelSerializer):
    isVerified = serializers.BooleanField(source='is_verified')
    createdAt = serializers.DateTimeField(source='created')
    isBanned = serializers.BooleanField(source='is_banned')
    id = serializers.CharField(read_only=True)

    class Meta:
        model = base_models.Phone
        fields = ('id', 'number', 'internal_id', 'session', 'first_name', 'isVerified', 'isBanned', 'createdAt', 'code')
        read_only_fields = ('id',)


class ChatListSerializer(serializers.ModelSerializer):
    id = serializers.UUIDField(read_only=True)
    createdAt = serializers.DateTimeField(source='created', read_only=True)
    internalId = serializers.IntegerField(source='internal_id', required=False)
    isAvailable = serializers.BooleanField(source='is_available', required=False)

    class Meta:
        model = base_models.Chat
        fields = ('id', 'createdAt', 'link', 'internalId', 'title', 'isAvailable')
        read_only_fields = ('id',)


class ChatMiniSerializer(serializers.ModelSerializer):
    id = serializers.ModelField(model_field=base_models.Chat()._meta.get_field('id'))

    class Meta:
        model = base_models.Chat
        fields = ('id',)
        read_only_fields = ('id',)


class ChatViewSerializer(serializers.ModelSerializer):
    isAvailable = serializers.BooleanField(source='is_available')
    createdAt = serializers.DateTimeField(source='created')
    internalId = serializers.CharField(source='internal_id')
    id = serializers.CharField(read_only=True)

    class Meta:
        model = base_models.Chat
        fields = ('id', 'link', 'internalId', 'isAvailable', 'title', 'createdAt', 'date', 'description')
        read_only_fields = ('id',)


class ChatPhoneListSerializer(serializers.ModelSerializer):
    isUsing = serializers.BooleanField(source='is_using')
    createdAt = serializers.DateTimeField(source='created', read_only=True)
    chat = ChatViewSerializer(read_only=True)
    phone = PhoneViewSerializer(read_only=True)
    id = serializers.CharField(read_only=True)

    class Meta:
        model = base_models.ChatPhone
        fields = ('id', 'chat', 'phone', 'isUsing', 'createdAt')
        read_only_fields = ('id',)


class HostViewSerializer(serializers.ModelSerializer):
    createdAt = serializers.DateTimeField(source='created')
    localIp = serializers.DateTimeField(source='local_ip')

    class Meta:
        model = base_models.Host
        fields = ('id', 'createdAt', 'localIp', 'name')
        read_only_fields = ('id',)


class ParserListSerializer(serializers.ModelSerializer):
    createdAt = serializers.DateTimeField(source='created', read_only=True)
    host = HostViewSerializer(read_only=True)
    chatsCount = serializers.SerializerMethodField(read_only=True)
    phonesCount = serializers.SerializerMethodField(read_only=True)

    def get_chatsCount(self, obj):
        return 10835

    def get_phonesCount(self, obj):
        return 1

    class Meta:
        model = base_models.Parser
        fields = ('id', 'createdAt', 'chatsCount', 'phonesCount', 'status', 'api_id', 'api_hash', 'host')
        read_only_fields = ('id',)


class MemberViewSerializer(serializers.ModelSerializer):
    id = serializers.ModelField(model_field=base_models.Member()._meta.get_field('id'))
    internalId = serializers.IntegerField(source='internal_id', read_only=True)
    firstName = serializers.CharField(source='first_name', required=False)
    lastName = serializers.CharField(source='last_name', required=False)

    class Meta:
        model = base_models.Member
        fields = ('id', 'internalId', 'username', 'firstName', 'lastName', 'phone', 'about')
        read_only_fields = ('id',)


class MemberListSerializer(serializers.ModelSerializer):
    internalId = serializers.IntegerField(source='internal_id', read_only=True)
    firstName = serializers.CharField(source='first_name', required=False)
    lastName = serializers.CharField(source='last_name', required=False)

    class Meta:
        model = base_models.Member
        fields = ('id', 'internalId', 'username', 'firstName', 'lastName', 'phone', 'about')
        read_only_fields = ('id',)


class ChatMemberListSerializer(serializers.ModelSerializer):
    member = MemberViewSerializer()
    chat = ChatMiniSerializer()

    class Meta:
        model = base_models.ChatMember
        fields = ('id', 'chat', 'member', 'is_left', 'date', 'chat_id')
        read_only_fields = ('id',)

    def create(self, validated_data):
        member = validated_data.pop('member')
        chat = validated_data.pop('chat')
        chat_member = base_models.ChatMember.objects.create(member_id=member['id'], chat_id=chat['id'], **validated_data)
        return chat_member


class MemberMediaListSerializer(serializers.ModelSerializer):
    internalId = serializers.IntegerField(source='internal_id')
    member = MemberViewSerializer()

    class Meta:
        model = base_models.MemberMedia
        fields = ('id', 'member', 'internalId', 'path', 'date')
        read_only_fields = ('id',)

    def update(self, instance, validated_data):
        member = validated_data.pop('member', None)
        instance.member_id = member['id']
        instance.internal_id = validated_data.get('internal_id', instance.internal_id)
        instance.member = validated_data.get('member', instance.member)
        instance.date = validated_data.get('date', instance.date)
        instance.save()
        return instance


class ChatMediaListSerializer(serializers.ModelSerializer):
    internalId = serializers.IntegerField(source='internal_id')

    class Meta:
        model = base_models.ChatMedia
        fields = ('id', 'chat', 'internalId', 'path', 'date', 'file')
        read_only_fields = ('id',)


class HostListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.Host
        fields = ('id', 'created', 'public_ip', 'local_ip', 'name')
        read_only_fields = ('id', 'created')


class MessageListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.Message
        fields = ('id', 'member', 'reply_to_id', 'internal_id', 'text', 'is_pinned', 'forwarded_from_id',
                  'forwarded_from_name', 'created', 'chat_id', 'grouped_id', 'date')
        read_only_fields = ('id', 'created')


class MessageMediaListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.MessageMedia
        fields = ('id', 'message', 'path', 'created', 'internal_id', 'date')
        read_only_fields = ('id', 'created')

