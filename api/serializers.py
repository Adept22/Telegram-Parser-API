from rest_framework import serializers
import base.models as base_models


class LinkListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.Link
        fields = "__all__"
        read_only_fields = ("id", "created_at")


class HostListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.Host
        fields = "__all__"
        read_only_fields = ("id", "created_at")
        extra_kwargs = {"local_ip": {"validators": [], "required": True}}


class ParserListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.Parser
        fields = "__all__"
        read_only_fields = ("id", "created_at")


class PhoneListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.Phone
        fields = "__all__"
        read_only_fields = ("id", "created_at")
        extra_kwargs = {
            "number": {"validators": [], "required": True},
            "session": {"validators": [], "required": False},
            "internal_id": {"validators": [], "required": False},
        }


class PhoneTaskListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.PhoneTask
        fields = "__all__"
        read_only_fields = ("id", "created_at")


class ChatListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.Chat
        fields = "__all__"
        read_only_fields = ("id", "created_at")
        extra_kwargs = {"internal_id": {"validators": [], "required": False}}


class ChatTaskListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.ChatTask
        fields = "__all__"
        read_only_fields = ("id", "created_at")


class ChatPhoneListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.ChatPhone
        fields = "__all__"
        read_only_fields = ("id", "created_at")


class ChatMediaListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.ChatMedia
        fields = "__all__"
        read_only_fields = ("id", "path", "created_at")
        extra_kwargs = {"internal_id": {"validators": [], "required": True}}


class MemberListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.Member
        fields = "__all__"
        read_only_fields = ("id", "created_at")
        extra_kwargs = {"internal_id": {"validators": [], "required": True}}


class MemberMediaListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.MemberMedia
        fields = "__all__"
        read_only_fields = ("id", "path", "created_at")
        extra_kwargs = {"internal_id": {"validators": [], "required": True}}


class ChatMemberListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.ChatMember
        fields = "__all__"
        read_only_fields = ("id", "created_at")


class ChatMemberRoleListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.ChatMemberRole
        fields = "__all__"
        read_only_fields = ("id", "created_at")


class MessageListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.Message
        fields = "__all__"
        read_only_fields = ("id", "created_at")


class MessageMediaListSerializer(serializers.ModelSerializer):
    class Meta:
        model = base_models.MessageMedia
        fields = "__all__"
        read_only_fields = ("id", "path", "created_at")
        extra_kwargs = {
            "internal_id": {"validators": [], "required": True},
            "message": {"validators": [], "required": True}
        }


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
