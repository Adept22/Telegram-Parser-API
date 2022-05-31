from django.utils import timezone
from datetime import timedelta, datetime
from rest_framework.response import Response
from rest_framework.decorators import action
from django_filters import rest_framework as filters
from rest_framework import permissions, viewsets, status
import api.serializers as serializers
from api.paginators import MyPagination
from api.filters import ChatFilter, PhoneFilter
import base.models as base_models
from base.tasks import resolve_chat, test_task, unban_phone_task
import base.tasks as base_tasks
# from celery import Celery
from tg_parser.celeryapp import app as celery_app


class Bots(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.BotListSerializer
    queryset = base_models.Bot.objects.all()
    pagination_class = MyPagination


class Phones(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.PhoneListSerializer
    queryset = base_models.Phone.objects.all()
    pagination_class = MyPagination
    filter_backends = (filters.DjangoFilterBackend,)
    filter_class = PhoneFilter

    def get_queryset(self):
        return self.queryset

    @action(methods=['post'], detail=False)
    def find(self, request):
        serializer = self.serializer_class(self.queryset.filter(is_banned=False), many=True)
        return Response(serializer.data, status=status.HTTP_201_CREATED)

    @action(methods=['post'], detail=True)
    def ban(self, request, pk=None):
        serializer = serializers.WaitSerializer(data=request.data)
        if serializer.is_valid():
            phone = self.get_object()
            phone.wait = datetime.now() + timedelta(seconds=serializer.validated_data['wait'])
            phone.save()
            unban_phone_task.apply_async((phone.id,), countdown=serializer.validated_data['wait'])
            return Response(status=status.HTTP_201_CREATED)
        return Response("wait field is required", status=status.HTTP_400_BAD_REQUEST)

    @action(methods=['post'], detail=True)
    def bots(self, request, pk=None):
        phone = self.get_object()
        phone.make_telegram_bot
        return Response(status=status.HTTP_201_CREATED)

    @action(methods=['post'], detail=True)
    def authorization(self, request, pk=None):
        phone = self.get_object()
        base_tasks.PhoneAuthorizationTask().delay(phone.id)
        return Response(status=status.HTTP_201_CREATED)


class Chats(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.ChatListSerializer
    queryset = base_models.Chat.objects.all()
    pagination_class = MyPagination
    filter_backends = (filters.DjangoFilterBackend,)
    filter_class = ChatFilter

    def perform_create(self, serializer):
        chat = serializer.save()
        try:
            bot = base_models.Bot.objects.all().first()
        except Exception as ex:
            print("Exception: {}".format(ex))
            base_models.ChatLog.objects.create(chat=chat, body=ex)
        else:
            session = bot.get_session
            resolve_chat.delay({"chat_id": chat.id, "session": session})

    @action(methods=['post'], detail=False)
    def find(self, request):
        serializer = self.serializer_class(self.queryset.filter(id__in=[37622]), many=True)
        return Response(serializer.data, status=status.HTTP_201_CREATED)

    @action(methods=['post'], detail=False)
    def test(self, request):
        # test_task.apply_async((), countdown=3)
        # [celery_app.send_task('base.tasks.test', ('test param3333',)) for i in range(100)]
        # base_tasks.ChatResolveTask().delay('f652949e-e0cd-11ec-9669-7972643f4571')
        # base_tasks.JoinChatTask().delay('f652949e-e0cd-11ec-9669-7972643f4571', '1d1efa20-ddce-11ec-95c5-cf63300076c1')
        return Response(status=status.HTTP_201_CREATED)


class ChatPhones(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.ChatPhoneListSerializer
    queryset = base_models.ChatPhone.objects.all()
    pagination_class = MyPagination

    def get_queryset(self):
        print(self.request.data)
        return self.queryset

    @action(methods=['post'], detail=False)
    def find(self, request):
        serializer = self.serializer_class(self.queryset.all(), many=True)
        return Response(serializer.data, status=status.HTTP_201_CREATED)


class Parsers(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.ParserListSerializer
    queryset = base_models.Parser.objects.all()
    pagination_class = MyPagination


class Messages(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.MessageListSerializer
    queryset = base_models.Message.objects.all()
    pagination_class = MyPagination


class MessageMedias(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.MessageMediaListSerializer
    queryset = base_models.MessageMedia.objects.all()
    pagination_class = MyPagination


class Members(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.MemberListSerializer
    queryset = base_models.Member.objects.all()
    pagination_class = MyPagination


class ChatMembers(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.ChatMemberListSerializer
    queryset = base_models.ChatMember.objects.all()
    pagination_class = MyPagination


class MemberMedias(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.MemberMediaListSerializer
    queryset = base_models.MemberMedia.objects.all()
    pagination_class = MyPagination

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
    pagination_class = MyPagination

    @action(methods=['post'], detail=True)
    def upload(self, request, pk=None):
        print("REQUEST: {}".format(request.data))
        print("PK: {}".format(pk))
        # print("MM: {}".format(ChatMedia.objects.get(id=pk)))
        # chat_media = ChatMedia.objects.create(id=pk)
        # chat_media = self.get_object()
        # chat_media.file = request.FILES['file']
        # chat_media.save()
        # serializer = self.serializer_class(request.data)
        # serializer = self.get_serializer(instance=chat_media)
        # serializer.is_valid(raise_exception=True)
        # self.perform_update(serializer)
        return Response({}, status=status.HTTP_201_CREATED)


class Hosts(viewsets.ModelViewSet):
    permission_classes = [permissions.AllowAny]
    serializer_class = serializers.HostListSerializer
    queryset = base_models.Host.objects.all()
    pagination_class = MyPagination
