from rest_framework import serializers
import base.models as base_models


class BaseModelSerializer(serializers.ModelSerializer):
    def get_required(self):
        """Возвращает dict обязательных параметров"""

        return {}


class LinkListSerializer(BaseModelSerializer):
    class Meta:
        model = base_models.Link
        fields = "__all__"
        read_only_fields = ("id", "created_at")
        extra_kwargs = {
            "link": {"validators": [], "required": True}
        }

    def get_required(self):
        return {
            "link": self.validated_data["link"]
        }


class HostListSerializer(BaseModelSerializer):
    class Meta:
        model = base_models.Host
        fields = "__all__"
        read_only_fields = ("id", "created_at")
        extra_kwargs = {"local_ip": {"validators": [], "required": True}}

    def get_required(self):
        return {
            "local_ip": self.validated_data["local_ip"]
        }


class ParserListSerializer(BaseModelSerializer):
    class Meta:
        model = base_models.Parser
        fields = "__all__"
        read_only_fields = ("id", "created_at")


class PhoneListSerializer(BaseModelSerializer):
    class Meta:
        model = base_models.Phone
        fields = "__all__"
        read_only_fields = ("id", "created_at")
        extra_kwargs = {
            "number": {"validators": [], "required": True},
            "session": {"validators": [], "required": False},
            "internal_id": {"validators": [], "required": False},
        }

    def get_required(self):
        return {
            "number": self.validated_data["number"],
            "session": self.validated_data["session"],
            "internal_id": self.validated_data["internal_id"],
        }


class PhoneTaskListSerializer(BaseModelSerializer):
    class Meta:
        model = base_models.PhoneTask
        fields = "__all__"
        read_only_fields = ("id", "created_at")


class ChatListSerializer(BaseModelSerializer):
    class Meta:
        model = base_models.Chat
        fields = "__all__"
        read_only_fields = ("id", "created_at")
        extra_kwargs = {"internal_id": {"validators": [], "required": False}}

    def get_required(self):
        return {
            "internal_id": self.validated_data["internal_id"]
        }


class ChatLinkListSerializer(BaseModelSerializer):
    class Meta:
        model = base_models.ChatLink
        fields = "__all__"
        read_only_fields = ("id", "created_at")
        extra_kwargs = {
            "link": {"validators": [], "required": True}
        }

    def get_required(self):
        return {
            "link": self.validated_data["link"]
        }


class ChatTaskListSerializer(BaseModelSerializer):
    class Meta:
        model = base_models.ChatTask
        fields = "__all__"
        read_only_fields = ("id", "created_at")


class ChatPhoneListSerializer(BaseModelSerializer):
    class Meta:
        model = base_models.ChatPhone
        fields = "__all__"
        read_only_fields = ("id", "created_at")
        extra_kwargs = {
            "chat": {"validators": [], "required": True},
            "phone": {"validators": [], "required": True}
        }

    def get_required(self):
        return {
            "chat": self.validated_data["chat"],
            "phone": self.validated_data["phone"],
        }


class ChatMediaListSerializer(BaseModelSerializer):
    class Meta:
        model = base_models.ChatMedia
        fields = "__all__"
        read_only_fields = ("id", "path", "created_at")
        extra_kwargs = {"internal_id": {"validators": [], "required": True}}

    def get_required(self):
        return {
            "internal_id": self.validated_data["internal_id"],
        }


class MemberListSerializer(BaseModelSerializer):
    class Meta:
        model = base_models.Member
        fields = "__all__"
        read_only_fields = ("id", "created_at")
        extra_kwargs = {"internal_id": {"validators": [], "required": True}}

    def get_required(self):
        return {
            "internal_id": self.validated_data["internal_id"],
        }


class MemberLinkListSerializer(BaseModelSerializer):
    class Meta:
        model = base_models.MemberLink
        fields = "__all__"
        read_only_fields = ("id", "created_at")

    def get_required(self):
        return {
            "link": self.validated_data["link"]
        }


class MemberMediaListSerializer(BaseModelSerializer):
    class Meta:
        model = base_models.MemberMedia
        fields = "__all__"
        read_only_fields = ("id", "path", "created_at")
        extra_kwargs = {"internal_id": {"validators": [], "required": True}}

    def get_required(self):
        return {
            "internal_id": self.validated_data["internal_id"],
        }


class ChatMemberListSerializer(BaseModelSerializer):
    class Meta:
        model = base_models.ChatMember
        fields = "__all__"
        read_only_fields = ("id", "created_at")
        extra_kwargs = {
            "chat": {"validators": [], "required": True},
            "member": {"validators": [], "required": True}
        }

    def get_required(self):
        return {
            "chat": self.validated_data["chat"],
            "member": self.validated_data["member"],
        }


class ChatMemberRoleListSerializer(BaseModelSerializer):
    class Meta:
        model = base_models.ChatMemberRole
        fields = "__all__"
        read_only_fields = ("id", "created_at")
        extra_kwargs = {
            "member": {"validators": [], "required": True},
            "title": {"validators": [], "required": True},
            "code": {"validators": [], "required": True}
        }

    def get_required(self):
        return {
            "member": self.validated_data["member"],
            "title": self.validated_data["title"],
            "code": self.validated_data["code"],
        }


class MessageListSerializer(BaseModelSerializer):
    class Meta:
        model = base_models.Message
        fields = "__all__"
        read_only_fields = ("id", "created_at")
        extra_kwargs = {
            "internal_id": {"validators": [], "required": True},
            "chat": {"validators": [], "required": True}
        }

    def get_required(self):
        return {
            "internal_id": self.validated_data["internal_id"],
            "chat": self.validated_data["chat"],
        }


class MessageLinkListSerializer(BaseModelSerializer):
    class Meta:
        model = base_models.MessageLink
        fields = "__all__"
        read_only_fields = ("id", "created_at")
        extra_kwargs = {
            "link": {"validators": [], "required": True}
        }

    def get_required(self):
        return {
            "link": self.validated_data["link"]
        }


class MessageMediaListSerializer(BaseModelSerializer):
    class Meta:
        model = base_models.MessageMedia
        fields = "__all__"
        read_only_fields = ("id", "path", "created_at")
        extra_kwargs = {
            "internal_id": {"validators": [], "required": True},
            "message": {"validators": [], "required": True}
        }

    def get_required(self):
        return {
            "internal_id": self.validated_data["internal_id"],
            "message": self.validated_data["message"],
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
