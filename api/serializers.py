from rest_framework import serializers, status
from rest_framework.exceptions import APIException

import base.models as base_models


class WaitSerializer(serializers.Serializer):
    wait = serializers.IntegerField()


class BotListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.Bot
        fields = ('id', 'created_at', 'name', 'session')
        read_only_fields = ('id', 'created_at', 'session')


class HostViewSerializer(serializers.ModelSerializer):

    class Meta:
        model = base_models.Host
        fields = ('id', 'created_at', 'local_ip', 'public_ip', 'name')
        read_only_fields = ('id',)


class ParserListSerializer(serializers.ModelSerializer):
    host = HostViewSerializer(read_only=True)
    chatsCount = serializers.SerializerMethodField(read_only=True)
    phonesCount = serializers.SerializerMethodField(read_only=True)

    def get_chatsCount(self, obj):
        return 10835

    def get_phonesCount(self, obj):
        return 1

    class Meta:
        model = base_models.Parser
        fields = ('id', 'created_at', 'chatsCount', 'phonesCount', 'status', 'api_id', 'api_hash', 'host')
        read_only_fields = ('id',)


class ParserSerializer(serializers.ModelSerializer):
    id = serializers.ModelField(model_field=base_models.Parser()._meta.get_field('id'))
    api_id = serializers.IntegerField(required=False)
    api_hash = serializers.CharField(required=False)

    class Meta:
        model = base_models.Parser
        fields = ('id', 'created_at', 'status', 'api_id', 'api_hash')
        read_only_fields = ('id', 'created_at')


class PhoneListSerializer(serializers.ModelSerializer):
    # id = serializers.CharField(read_only=True)
    parser = ParserSerializer()

    class Meta:
        model = base_models.Phone
        fields = ('id', 'number', 'internal_id', 'session', 'first_name', 'last_name', 'status', 'code', 'created_at',
                  'parser')
        read_only_fields = ('id', 'created_at')

    def create(self, validated_data):
        parser_id = validated_data.get('parser', None)
        if parser_id is not None:
            parser = base_models.Parser.objects.get(id=parser_id.get('id'))
            validated_data['parser'] = parser
        return base_models.Phone.objects.create(**validated_data)


class PhoneUpdateSerializer(serializers.ModelSerializer):
    parser = ParserSerializer()

    class Meta:
        model = base_models.Phone
        fields = (
            'id', 'number', 'internal_id', 'session', 'first_name', 'last_name', 'code', 'wait', 'created_at',
            'status_text', 'status', 'parser'
        )
        read_only_fields = ('id', 'created_at')

    def update(self, instance, validated_data):
        instance.number = validated_data.get('number', instance.number)
        instance.internal_id = validated_data.get('internal_id', instance.internal_id)
        instance.session = validated_data.get('session', instance.session)
        instance.first_name = validated_data.get('first_name', instance.first_name)
        instance.last_name = validated_data.get('last_name', instance.last_name)
        instance.code = validated_data.get('code', instance.code)
        instance.wait = validated_data.get('wait', instance.wait)
        instance.status_text = validated_data.get('status_text', instance.wait)
        instance.status = validated_data.get('status', instance.status)
        parser = validated_data.get('parser')
        if parser is not None:
            instance.parser_id = parser.get('id')
        instance.save()
        return instance


class PhoneViewSerializer(serializers.ModelSerializer):
    id = serializers.CharField(read_only=True)

    class Meta:
        model = base_models.Phone
        fields = ('id', 'number', 'internal_id', 'session', 'first_name', 'status', 'created_at', 'code')
        read_only_fields = ('id',)


class ChatListSerializer(serializers.ModelSerializer):
    id = serializers.UUIDField(read_only=True)
    parser = ParserSerializer(read_only=True)

    class Meta:
        model = base_models.Chat
        fields = ('id', 'created_at', 'link', 'internal_id', 'title', 'status', 'parser')
        read_only_fields = ('id', 'created_at')


class ChatMiniSerializer(serializers.ModelSerializer):
    id = serializers.ModelField(model_field=base_models.Chat()._meta.get_field('id'))

    class Meta:
        model = base_models.Chat
        fields = ('id',)
        read_only_fields = ('id',)


class ChatViewSerializer(serializers.ModelSerializer):
    id = serializers.CharField(read_only=True)

    class Meta:
        model = base_models.Chat
        fields = ('id', 'link', 'internal_id', 'status', 'title', 'created_at', 'date', 'description')
        read_only_fields = ('id',)


class PhoneMiniSerializer(serializers.ModelSerializer):
    id = serializers.ModelField(model_field=base_models.Phone()._meta.get_field('id'))

    class Meta:
        model = base_models.Phone
        fields = ('id',)
        read_only_fields = ('id',)


class ChatPhoneListSerializer(serializers.ModelSerializer):
    chat = ChatMiniSerializer()
    phone = PhoneMiniSerializer()
    id = serializers.CharField(read_only=True)

    class Meta:
        model = base_models.ChatPhone
        fields = ('id', 'chat', 'phone', 'is_using', 'created_at')
        read_only_fields = ('id',)

    def create(self, validated_data):
        chat = validated_data.pop('chat')
        phone = validated_data.pop('phone')
        print("chat: {} phone: {}".format(chat['id'], phone['id']))
        chat_phone = self.__class__.Meta.model.objects.create(chat_id=chat['id'], phone_id=phone['id'], **validated_data)
        return chat_phone


class MemberViewSerializer(serializers.ModelSerializer):
    id = serializers.ModelField(model_field=base_models.Member()._meta.get_field('id'))

    class Meta:
        model = base_models.Member
        fields = ('id', 'internal_id', 'username', 'first_name', 'last_name', 'phone', 'about')
        read_only_fields = ('id',)


class MemberListSerializer(serializers.ModelSerializer):

    class Meta:
        model = base_models.Member
        fields = ('id', 'internal_id', 'username', 'first_name', 'last_name', 'phone', 'about')
        read_only_fields = ('id',)


class Custom409(APIException):
    status_code = status.HTTP_409_CONFLICT


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
        try:
            chat_member = base_models.ChatMember.objects.create(
                member_id=member['id'], chat_id=chat['id'], **validated_data
            )
            return chat_member
        except Exception as exception:
            raise Custom409(exception)


class ChatMemberSerializer(serializers.ModelSerializer):
    id = serializers.ModelField(model_field=base_models.ChatMember()._meta.get_field('id'))

    class Meta:
        model = base_models.ChatMember
        fields = ('id',)
        read_only_fields = ('id',)


class ChatMemberRoleListSerializer(serializers.ModelSerializer):
    member = ChatMemberSerializer()

    class Meta:
        model = base_models.ChatMemberRole
        fields = ('id', 'member', 'title', 'code')
        read_only_fields = ('id',)

    def create(self, validated_data):
        member = validated_data.pop('member')
        chat_member_role = self.__class__.Meta.model.objects.create(member_id=member['id'], **validated_data)
        return chat_member_role


class MemberMediaListSerializer(serializers.ModelSerializer):
    member = MemberViewSerializer()

    class Meta:
        model = base_models.MemberMedia
        fields = ('id', 'member', 'internal_id', 'path', 'date')
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

    class Meta:
        model = base_models.ChatMedia
        fields = ('id', 'chat', 'internal_id', 'path', 'date', 'file')
        read_only_fields = ('id',)


class HostListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.Host
        fields = ('id', 'created_at', 'public_ip', 'local_ip', 'name')
        read_only_fields = ('id', 'created_at')


class MessageListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.Message
        fields = ('id', 'member', 'reply_to_id', 'internal_id', 'text', 'is_pinned', 'forwarded_from_id',
                  'forwarded_from_name', 'created_at', 'chat_id', 'grouped_id', 'date')
        read_only_fields = ('id', 'created_at')


class MessageMediaListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.MessageMedia
        fields = ('id', 'message', 'path', 'created_at', 'internal_id', 'date')
        read_only_fields = ('id', 'created_at')


class ChunkViewSerializer(serializers.Serializer):
    chunk_number = serializers.IntegerField(required=True)
    chunk_size = serializers.IntegerField(required=True)
    filename = serializers.CharField(required=True)
    total_size = serializers.IntegerField(required=False)
    chunk = serializers.FileField(required=False)

