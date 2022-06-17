from rest_framework import serializers
from rest_framework.exceptions import APIException, ValidationError
import base.models as base_models


class WaitSerializer(serializers.Serializer):
    wait = serializers.IntegerField()


class BotListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.Bot
        fields = ("id", "created_at", "name", "session")
        read_only_fields = ("id", "created_at", "session")


class HostViewSerializer(serializers.ModelSerializer):

    class Meta:
        model = base_models.Host
        fields = ("id", "created_at", "local_ip", "public_ip", "name")
        read_only_fields = ("id",)


class ParserListSerializer(serializers.ModelSerializer):
    # host = HostViewSerializer(read_only=True)

    class Meta:
        model = base_models.Parser
        fields = ("id", "created_at", "status", "api_id", "api_hash", "host")
        read_only_fields = ("id",)


class ParserSerializer(serializers.ModelSerializer):
    id = serializers.ModelField(model_field=base_models.Parser()._meta.get_field("id"))
    api_id = serializers.IntegerField(required=False)
    api_hash = serializers.CharField(required=False)

    class Meta:
        model = base_models.Parser
        fields = ("id", "created_at", "status", "api_id", "api_hash")
        read_only_fields = ("id", "created_at")


class PhoneListSerializer(serializers.ModelSerializer):
    # parser = ParserSerializer()

    class Meta:
        model = base_models.Phone
        fields = ("id", "number", "internal_id", "session", "first_name", "last_name", "status", "code", "created_at",
                  "parser", "api")
        read_only_fields = ("id", "created_at")

    def create(self, validated_data):
        parser_id = validated_data.get("parser", None)
        if parser_id is not None:
            parser = base_models.Parser.objects.get(id=parser_id.get("id"))
            validated_data["parser"] = parser
        return base_models.Phone.objects.create(**validated_data)


class PhoneUpdateSerializer(serializers.ModelSerializer):
    # parser = ParserSerializer()

    class Meta:
        model = base_models.Phone
        fields = (
            "id", "number", "internal_id", "session", "first_name", "last_name", "code", "wait", "created_at",
            "status_text", "status", "parser", "api"
        )
        read_only_fields = ("id", "created_at")

    # def update(self, instance, validated_data):
    #     instance.number = validated_data.get("number", instance.number)
    #     instance.internal_id = validated_data.get("internal_id", instance.internal_id)
    #     instance.session = validated_data.get("session", instance.session)
    #     instance.first_name = validated_data.get("first_name", instance.first_name)
    #     instance.last_name = validated_data.get("last_name", instance.last_name)
    #     instance.code = validated_data.get("code", instance.code)
    #     instance.wait = validated_data.get("wait", instance.wait)
    #     instance.status_text = validated_data.get("status_text", instance.wait)
    #     instance.status = validated_data.get("status", instance.status)
    #     instance.api = validated_data.get("api", instance.api)
    #     parser = validated_data.get("parser")
    #     if parser is not None:
    #         instance.parser_id = parser.get("id")
    #     instance.save()
    #     return instance


class PhoneViewSerializer(serializers.ModelSerializer):
    id = serializers.CharField(read_only=True)

    class Meta:
        model = base_models.Phone
        fields = ("id", "number", "internal_id", "session", "first_name", "status", "created_at", "code")
        read_only_fields = ("id",)


class ChatListSerializer(serializers.ModelSerializer):
    id = serializers.UUIDField(read_only=True)
    parser = ParserSerializer(read_only=True)

    class Meta:
        model = base_models.Chat
        fields = (
            "id", "created_at", "link", "internal_id", "title", "status", "parser", "total_members", "total_messages"
        )
        read_only_fields = ("id", "created_at")
        extra_kwargs = {"link": {"validators": [], "required": True}}


class ChatMiniSerializer(serializers.ModelSerializer):
    id = serializers.ModelField(model_field=base_models.Chat()._meta.get_field("id"))

    class Meta:
        model = base_models.Chat
        fields = ("id",)
        read_only_fields = ("id",)


class ChatViewSerializer(serializers.ModelSerializer):
    id = serializers.CharField(read_only=True)

    class Meta:
        model = base_models.Chat
        fields = ("id", "link", "internal_id", "status", "title", "created_at", "date", "description")
        read_only_fields = ("id",)


class PhoneMiniSerializer(serializers.ModelSerializer):
    id = serializers.ModelField(model_field=base_models.Phone()._meta.get_field("id"))

    class Meta:
        model = base_models.Phone
        fields = ("id",)
        read_only_fields = ("id",)


class ChatPhoneListSerializer(serializers.ModelSerializer):
    # chat = ChatMiniSerializer()
    # phone = PhoneMiniSerializer()
    # id = serializers.CharField(read_only=True)

    class Meta:
        model = base_models.ChatPhone
        fields = ("id", "chat", "phone", "is_using", "created_at")
        read_only_fields = ("id",)

    # def validate_chat(self, chat: dict):
    #     print("{}".format(chat))
    #     try:
    #         data = base_models.Chat.objects.get(id=chat)
    #     except Exception as ex:
    #         raise ValidationError(ex)
    #     return data
    #
    # def validate_phone(self, phone: dict):
    #     try:
    #         data = base_models.Phone.objects.get(id=phone)
    #     except Exception as ex:
    #         raise ValidationError(ex)
    #     return data


class MemberViewSerializer(serializers.ModelSerializer):
    id = serializers.ModelField(model_field=base_models.Member()._meta.get_field("id"))

    class Meta:
        model = base_models.Member
        fields = ("id", "internal_id", "username", "first_name", "last_name", "phone", "about")
        read_only_fields = ("id",)


class MemberListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.Member
        fields = ("id", "internal_id", "username", "first_name", "last_name", "phone", "about")
        read_only_fields = ("id",)
        extra_kwargs = {"internal_id": {"validators": [], "required": True}}


class ChatMemberListSerializer(serializers.ModelSerializer):
    # member = MemberViewSerializer()
    # chat = ChatMiniSerializer()

    class Meta:
        model = base_models.ChatMember
        fields = ("id", "chat", "member", "is_left", "date", "chat_id")
        read_only_fields = ("id",)

    # def validate_chat(self, chat: dict):
    #     try:
    #         data = base_models.Chat.objects.get(id=chat)
    #     except Exception as ex:
    #         raise ValidationError(ex)
    #     return data
    #
    # def validate_member(self, member: dict):
    #     try:
    #         data = base_models.Member.objects.get(id=member)
    #     except Exception as ex:
    #         raise ValidationError(ex)
    #     return data

    # def update(self, instance, validated_data):
    #     member = validated_data.pop("member", None)
    #     chat = validated_data.pop("chat", None)
    #     instance.member_id = member["id"]
    #     instance.chat_id = chat["id"]
    #     instance.is_left = validated_data.get("is_left", instance.is_left)
    #     instance.date = validated_data.get("date", instance.date)
    #     instance.save()
    #     return instance


class ChatMemberSerializer(serializers.ModelSerializer):
    id = serializers.ModelField(model_field=base_models.ChatMember()._meta.get_field("id"))

    class Meta:
        model = base_models.ChatMember
        fields = ("id",)
        read_only_fields = ("id",)


class ChatMemberRoleListSerializer(serializers.ModelSerializer):
    # member = ChatMemberSerializer()

    class Meta:
        model = base_models.ChatMemberRole
        fields = ("id", "member", "title", "code")
        read_only_fields = ("id",)

    # def validate_member(self, member: dict):
    #     try:
    #         data = base_models.ChatMember.objects.get(id=member["id"])
    #     except Exception as ex:
    #         raise ValidationError(ex)
    #     return data

    # def create(self, validated_data):
    #     member = validated_data.pop("member")
    #     try:
    #         chat_member_role = self.__class__.Meta.model.objects.create(member_id=member["id"], **validated_data)
    #     except Exception as exception:
    #         raise Custom409(exception)
    #     return chat_member_role

    def update(self, instance, validated_data):
        member = validated_data.pop("member", None)
        instance.member_id = member["id"]
        instance.title = validated_data.get("title", instance.title)
        instance.code = validated_data.get("code", instance.code)
        instance.save()
        return instance


class MemberMediaListSerializer(serializers.ModelSerializer):

    class Meta:
        model = base_models.MemberMedia
        fields = ("id", "member", "internal_id", "path", "date")
        read_only_fields = ("id",)
        extra_kwargs = {"internal_id": {"validators": [], "required": True}}


class ChatMediaListSerializer(serializers.ModelSerializer):

    class Meta:
        model = base_models.ChatMedia
        fields = ("id", "chat", "internal_id", "path", "date", "file")
        read_only_fields = ("id",)


class HostListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.Host
        fields = ("id", "created_at", "public_ip", "local_ip", "name")
        read_only_fields = ("id", "created_at")


class MessageListSerializer(serializers.ModelSerializer):
    # member = ChatMemberSerializer(required=False)
    # chat = ChatMiniSerializer()

    class Meta:
        model = base_models.Message
        fields = ("id", "member", "reply_to", "internal_id", "text", "is_pinned", "forwarded_from_id",
                  "forwarded_from_name", "created_at", "chat", "grouped_id", "date")
        read_only_fields = ("id", "created_at")
        write_only_fields = ("internal_id",)

    # def create(self, validated_data):
    #     member = validated_data.get("member", None)
    #     if member is not None:
    #         validated_data["member"] = base_models.ChatMember.objects.get(id=member.get("id"))
    #
    #     chat = validated_data.get("chat", None)
    #     if chat is not None:
    #         validated_data["chat"] = base_models.Chat.objects.get(id=chat.get("id"))
    #     return base_models.Message.objects.create(**validated_data)


class MessageMediaListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.MessageMedia
        fields = ("id", "message", "path", "created_at", "internal_id", "date")
        read_only_fields = ("id", "created_at")


class ChunkCreateSerializer(serializers.Serializer):
    chunk_number = serializers.IntegerField(required=True)
    chunk_size = serializers.IntegerField(required=True)
    filename = serializers.CharField(required=True)
    total_size = serializers.IntegerField(required=True)
    total_chunks = serializers.IntegerField(required=True)
    chunk = serializers.FileField(required=False)


class ChunkViewSerializer(serializers.Serializer):
    chunk_number = serializers.IntegerField(required=True)
    chunk_size = serializers.IntegerField(required=False)
    filename = serializers.CharField(required=True)
    total_size = serializers.IntegerField(required=False)
    total_chunks = serializers.IntegerField(required=False)
    chunk = serializers.FileField(required=False)


