import os.path
from tempfile import tempdir
from django.utils import timezone
from datetime import timedelta, datetime
from rest_framework.response import Response
from rest_framework.decorators import action
from django_filters import rest_framework as filters
from rest_framework.filters import OrderingFilter
from rest_framework import permissions, viewsets, status
import api.serializers as serializers
from api.paginators import CustomPagination
from api.filters import ChatFilter, PhoneFilter, MessageFilter, MemberFilter, ChatMemberFilter, ChatMemberRoleFilter
import base.models as base_models
import base.tasks as base_tasks
from tg_parser.celeryapp import app as celery_app


class Bots(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.BotListSerializer
    queryset = base_models.Bot.objects.all()
    pagination_class = CustomPagination


class Phones(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.PhoneListSerializer
    queryset = base_models.Phone.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend,)
    filter_class = PhoneFilter

    def get_serializer_class(self):
        if self.action == "update":
            return serializers.PhoneUpdateSerializer
        return self.serializer_class

    def get_queryset(self):
        return self.queryset

    @action(methods=["post"], detail=True)
    def join_chat(self, request, pk=None):
        if "chat" in request.data:
            serializer = serializers.ChatMiniSerializer(data=request.data["chat"])
            if serializer.is_valid():
                task = celery_app.send_task("JoinChatTask", (serializer.validated_data["id"], pk), time_limit=60)
                return Response("{}".format(task), status=status.HTTP_201_CREATED)
            return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)
        return Response("chat field is required", status=status.HTTP_400_BAD_REQUEST)

    @action(methods=["post"], detail=True)
    def ban(self, request, pk=None):
        serializer = serializers.WaitSerializer(data=request.data)
        if serializer.is_valid():
            phone = self.get_object()
            phone.wait = datetime.now() + timedelta(seconds=serializer.validated_data["wait"])
            phone.save()
            base_tasks.unban_phone_task.apply_async((phone.id,), countdown=serializer.validated_data["wait"])
            return Response(status=status.HTTP_201_CREATED)
        return Response("wait field is required", status=status.HTTP_400_BAD_REQUEST)

    @action(methods=["post"], detail=True)
    def bots(self, request, pk=None):
        phone = self.get_object()
        phone.make_telegram_bot
        return Response(status=status.HTTP_201_CREATED)

    @action(methods=["post"], detail=True)
    def authorization(self, request, pk=None):
        task = celery_app.send_task("PhoneAuthorizationTask", (pk,), time_limit=60)
        return Response("{}".format(task), status=status.HTTP_201_CREATED)


class Chats(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.ChatListSerializer
    queryset = base_models.Chat.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend,)
    filter_class = ChatFilter

    def create(self, request, *args, **kwargs):
        serializer = self.get_serializer(data=request.data)
        if serializer.is_valid():
            chat, created = base_models.Chat.objects.update_or_create(
                link=serializer.validated_data["link"],
                defaults=serializer.validated_data,
            )
            if created:
                celery_app.send_task("ChatResolveTask", (chat.id,), time_limit=60)
                return Response(serializer.data, status=status.HTTP_201_CREATED)
            return Response(serializer.data, status=status.HTTP_200_OK)
        return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)

    @action(methods=["post"], detail=True)
    def resolve(self, request, pk=None):
        task = celery_app.send_task("ChatResolveTask", (pk,), time_limit=60)
        return Response("{}".format(task), status=status.HTTP_201_CREATED)

    @action(methods=["post"], detail=True)
    def parse(self, request, pk=None):
        # task = celery_app.send_task("ParseMembersTask", (pk,))
        task = celery_app.send_task("ParseMessagesTask", (pk,))
        return Response("{}".format(task), status=status.HTTP_201_CREATED)

    @action(methods=["post"], detail=False)
    def test(self, request):
        # base_tasks.test_task.apply_async((), countdown=3)
        # [celery_app.send_task("base.tasks.test", ("test param3333",)) for i in range(2)]
        # base_tasks.ChatResolveTask().delay("f652949e-e0cd-11ec-9669-7972643f4571")
        # base_tasks.JoinChatTask().delay("f652949e-e0cd-11ec-9669-7972643f4571", "1d1efa20-ddce-11ec-95c5-cf63300076c1")
        celery_app.send_task("test", ("123",), time_limit=60)
        return Response(status=status.HTTP_201_CREATED)


class ChatPhones(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.ChatPhoneListSerializer
    queryset = base_models.ChatPhone.objects.all()
    pagination_class = CustomPagination

    def create(self, request, *args, **kwargs):
        serializer = self.get_serializer(data=request.data)
        if serializer.is_valid():
            obj, created = base_models.ChatPhone.objects.update_or_create(
                chat=serializer.validated_data["chat"],
                phone=serializer.validated_data["phone"],
                defaults=serializer.validated_data,
            )
            serializer = self.get_serializer(obj)
            if created:
                return Response(serializer.data, status=status.HTTP_201_CREATED)
            return Response(serializer.data, status=status.HTTP_200_OK)
        return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)


class Parsers(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.ParserListSerializer
    queryset = base_models.Parser.objects.all()
    pagination_class = CustomPagination


class Messages(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.MessageListSerializer
    queryset = base_models.Message.objects.all()
    filter_backends = (filters.DjangoFilterBackend, OrderingFilter)
    pagination_class = CustomPagination
    ordering_fields = ["internal_id", "created_at"]
    filter_class = MessageFilter

    def create(self, request, *args, **kwargs):
        serializer = self.get_serializer(data=request.data)
        if serializer.is_valid():
            obj, created = base_models.Message.objects.update_or_create(
                chat=serializer.validated_data["chat"],
                internal_id=serializer.validated_data["internal_id"],
                defaults=serializer.validated_data,
            )
            serializer = self.get_serializer(obj)
            if created:
                return Response(serializer.data, status=status.HTTP_201_CREATED)
            return Response(serializer.data, status=status.HTTP_200_OK)
        return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)


class MessageMedias(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.MessageMediaListSerializer
    queryset = base_models.MessageMedia.objects.all()
    pagination_class = CustomPagination


class Members(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.MemberListSerializer
    queryset = base_models.Member.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend, OrderingFilter)
    filter_class = MemberFilter

    def create(self, request, *args, **kwargs):
        serializer = self.get_serializer(data=request.data)
        if serializer.is_valid():
            obj, created = base_models.Member.objects.update_or_create(
                internal_id=serializer.validated_data["internal_id"],
                defaults=serializer.validated_data,
            )
            serializer = self.get_serializer(obj)
            if created:
                return Response(serializer.data, status=status.HTTP_201_CREATED)
            return Response(serializer.data, status=status.HTTP_200_OK)
        return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)


class ChatMembers(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.ChatMemberListSerializer
    queryset = base_models.ChatMember.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend, OrderingFilter)
    filter_class = ChatMemberFilter

    def create(self, request, *args, **kwargs):
        serializer = self.get_serializer(data=request.data)
        if serializer.is_valid():
            obj, created = base_models.ChatMember.objects.update_or_create(
                chat=serializer.validated_data["chat"],
                member=serializer.validated_data["member"],
                defaults=serializer.validated_data,
            )
            serializer = self.get_serializer(obj)
            if created:
                return Response(serializer.data, status=status.HTTP_201_CREATED)
            return Response(serializer.data, status=status.HTTP_200_OK)
        return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)


class ChatMemberRoles(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.ChatMemberRoleListSerializer
    queryset = base_models.ChatMemberRole.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend, OrderingFilter)
    filter_class = ChatMemberRoleFilter

    def create(self, request, *args, **kwargs):
        serializer = self.get_serializer(data=request.data)
        if serializer.is_valid():
            obj, created = base_models.ChatMemberRole.objects.update_or_create(
                member=serializer.validated_data["member"],
                defaults=serializer.validated_data,
            )
            serializer = self.get_serializer(obj)
            if created:
                return Response(serializer.data, status=status.HTTP_201_CREATED)
            return Response(serializer.data, status=status.HTTP_200_OK)
        return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)


class MemberMedias(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.MemberMediaListSerializer
    queryset = base_models.MemberMedia.objects.all()
    pagination_class = CustomPagination

    def update(self, request, *args, **kwargs):
        try:
            instance = self.get_object()
        except Exception as ex:
            instance = base_models.MemberMedia.objects.create()
        serializer = self.get_serializer(instance, data=request.data)
        serializer.is_valid(raise_exception=True)
        self.perform_update(serializer)
        return Response(serializer.data)


class ChatMedias(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.ChatMediaListSerializer
    queryset = base_models.ChatMedia.objects.all()
    pagination_class = CustomPagination

    @action(methods=["post", "get"], detail=True)
    def chunk(self, request, pk=None):
        if request.method == "POST":
            chat_media = self.get_object()
            serializer = serializers.ChunkViewSerializer(data=request.data)
            if serializer.is_valid():
                # serializer.save()
                # with open(chat_media.file.path, "ab") as file1:
                #     file1.write(file.read())
                return Response(status=status.HTTP_201_CREATED)
            return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)
        else:
            serializer = serializers.ChunkViewSerializer(data=request.GET)
            if serializer.is_valid():
                req = serializer.validated_data
                if os.path.exists(os.path.join(tempdir, u"{}.part{}".format(req["filename"], req["chunk_number"]))):
                    return Response(serializer.data, status=status.HTTP_200_OK)
                return Response(serializer.data, status=status.HTTP_404_NOT_FOUND)
            else:
                return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)


class Hosts(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.HostListSerializer
    queryset = base_models.Host.objects.all()
    pagination_class = CustomPagination

