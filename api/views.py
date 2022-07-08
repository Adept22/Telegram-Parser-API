from abc import abstractmethod
import glob
import os.path
import random
import string
import tempfile
from functools import reduce
from django.conf import settings
from django_filters import rest_framework as filters
from rest_framework.response import Response
from rest_framework.decorators import action
from rest_framework.filters import OrderingFilter
from rest_framework import permissions, viewsets, status
import api.serializers as serializers
from api.paginators import CustomPagination
import base.models as base_models
import api.filters as base_filters


class LinkSubClassFieldsMixin(object):
    def get_queryset(self):
        return base_models.Link.objects.select_subclasses()


class BaseModelViewSet(viewsets.ModelViewSet):
    model_class = None

    @abstractmethod
    def get_required(serializer):
        """Возвращает dict обязательных параметров"""

        raise NotImplementedError

    def get_model_class(self):
        """
        Return the class to use for the model.
        Defaults to using `self.model_class`.
        """
        assert self.model_class is not None, (
            "'%s' should either include a `model_class` attribute, "
            "or override the `get_model_class()` method."
            % self.__class__.__name__
        )

        return self.model_class

    def create(self, request, *args, **kwargs):
        serializer = self.get_serializer(data=request.data)
        if serializer.is_valid():
            model = self.get_model_class()
            required = serializer.get_required()
            obj, created = model.objects.update_or_create(
                **required,
                defaults=serializer.validated_data,
            )
            serializer = self.get_serializer(obj)
            if created:
                return Response(serializer.data, status=status.HTTP_201_CREATED)
            return Response(serializer.data, status=status.HTTP_200_OK)
        return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)


class Links(LinkSubClassFieldsMixin, BaseModelViewSet):
    permission_classes = [permissions.AllowAny]
    model_class = base_models.Link
    serializer_class = serializers.LinkListSerializer
    queryset = base_models.Link.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend,)
    filter_class = base_filters.LinkFilter


class Hosts(BaseModelViewSet):
    permission_classes = [permissions.AllowAny]
    model_class = base_models.Host
    serializer_class = serializers.HostListSerializer
    queryset = base_models.Host.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend, OrderingFilter)
    filter_class = base_filters.HostFilter


class Parsers(BaseModelViewSet):
    permission_classes = [permissions.AllowAny]
    model_class = base_models.Parser
    serializer_class = serializers.ParserListSerializer
    queryset = base_models.Parser.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend, OrderingFilter)
    filter_class = base_filters.ParserFilter


class Phones(BaseModelViewSet):
    permission_classes = [permissions.AllowAny]
    model_class = base_models.Phone
    serializer_class = serializers.PhoneListSerializer
    queryset = base_models.Phone.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend,)
    filter_class = base_filters.PhoneFilter


class PhonesTasks(BaseModelViewSet):
    permission_classes = [permissions.AllowAny]
    model_class = base_models.PhoneTask
    serializer_class = serializers.PhoneTaskListSerializer
    queryset = base_models.PhoneTask.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend, OrderingFilter)
    filter_class = base_filters.PhoneTaskFilter


class Chats(BaseModelViewSet):
    permission_classes = [permissions.AllowAny]
    model_class = base_models.Chat
    serializer_class = serializers.ChatListSerializer
    queryset = base_models.Chat.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend,)
    filter_class = base_filters.ChatFilter


class ChatsLinks(BaseModelViewSet):
    permission_classes = [permissions.AllowAny]
    model_class = base_models.ChatLink
    serializer_class = serializers.ChatLinkListSerializer
    queryset = base_models.ChatLink.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend,)
    filter_class = base_filters.ChatLinkFilter


class ChatsTasks(BaseModelViewSet):
    permission_classes = [permissions.AllowAny]
    model_class = base_models.ChatTask
    serializer_class = serializers.ChatTaskListSerializer
    queryset = base_models.ChatTask.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend, OrderingFilter)
    filter_class = base_filters.ChatTaskFilter


class ChatsPhones(BaseModelViewSet):
    permission_classes = [permissions.AllowAny]
    model_class = base_models.ChatPhone
    serializer_class = serializers.ChatPhoneListSerializer
    queryset = base_models.ChatPhone.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend,)
    filter_class = base_filters.ChatPhoneFilter


class ChatsMedias(BaseModelViewSet):
    permission_classes = [permissions.AllowAny]
    model_class = base_models.ChatMedia
    serializer_class = serializers.ChatMediaListSerializer
    queryset = base_models.ChatMedia.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend, OrderingFilter)
    filter_class = base_filters.ChatMediaFilter

    def make_path(self, current, *, depth: 'int' = 0):
        if depth >= settings.MEDIA_PATH_DEPTH:
            return current

        name = ''.join([random.choice(string.digits + string.ascii_lowercase) for c in range(0, 2)])

        current = os.path.join(current, name)

        if not os.path.exists(current):
            os.mkdir(current, 0o775)

        return self.make_path(current, depth=depth + 1)

    @action(methods=["post", "get"], detail=True)
    def chunk(self, request, pk=None):
        if request.method == "POST":
            request.data['filename'] = request.query_params.get('filename')
            request.data['chunk_number'] = request.query_params.get('chunk_number')
            request.data['total_size'] = request.query_params.get('total_size')
            request.data['total_chunks'] = request.query_params.get('total_chunks')
            request.data['chunk_size'] = request.query_params.get('chunk_size')

            serializer = serializers.ChunkCreateSerializer(data=request.data)

            if serializer.is_valid():
                chunk = serializer.validated_data['chunk']
                filename = serializer.validated_data['filename']
                chunk_number = serializer.validated_data['chunk_number']
                total_size = serializer.validated_data['total_size']
                total_chunks = serializer.validated_data['total_chunks']

                tmp_dir = tempfile.gettempdir()

                tmp_path = os.path.join(tmp_dir, f"{filename}.part{chunk_number}")

                with open(tmp_path, 'wb') as tmpf:
                    tmpf.write(chunk.read())

                if chunk_number >= total_chunks - 1:
                    chunks = glob.glob(os.path.join(tmp_dir, f"{filename}.part[0-9]*"))
                    if chunks:
                        computed = reduce(lambda x, y: x + y, [os.path.getsize(c) for c in chunks])
                        if computed >= total_size:
                            obj = self.get_object()

                            path = self.make_path(settings.MEDIA_ROOT)
                            name, ext = os.path.splitext(filename)
                            media_path = os.path.join(path, f"{obj.id}{ext}")

                            with open(media_path, "wb") as mf:
                                for chunk in sorted(chunks):
                                    with open(chunk, "rb") as cf:
                                        mf.write(cf.read())

                                    os.remove(chunk)

                            obj.path = os.path.join('uploads', os.path.relpath(media_path, settings.MEDIA_ROOT))
                            obj.save()

                return Response(status=status.HTTP_204_NO_CONTENT)
            return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)
        else:
            serializer = serializers.ChunkViewSerializer(data=request.GET)

            if serializer.is_valid():
                req = serializer.validated_data

                tmp_dir = tempfile.gettempdir()

                if os.path.exists(os.path.join(tmp_dir, u"{}.part{}".format(req["filename"], req["chunk_number"]))):
                    return Response(status=status.HTTP_204_NO_CONTENT)

                return Response(serializer.data, status=status.HTTP_404_NOT_FOUND)
            else:
                return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)


class Members(BaseModelViewSet):
    permission_classes = [permissions.AllowAny]
    model_class = base_models.Member
    serializer_class = serializers.MemberListSerializer
    queryset = base_models.Member.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend, OrderingFilter)
    filter_class = base_filters.MemberFilter


class MembersLinks(BaseModelViewSet):
    permission_classes = [permissions.AllowAny]
    model_class = base_models.MemberLink
    serializer_class = serializers.MemberLinkListSerializer
    queryset = base_models.MemberLink.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend,)
    filter_class = base_filters.MemberLinkFilter


class MembersMedias(BaseModelViewSet):
    permission_classes = [permissions.AllowAny]
    model_class = base_models.MemberMedia
    serializer_class = serializers.MemberMediaListSerializer
    queryset = base_models.MemberMedia.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend, OrderingFilter)
    filter_class = base_filters.MemberMediaFilter

    def make_path(self, current, *, depth: 'int' = 0):
        if depth >= settings.MEDIA_PATH_DEPTH:
            return current

        name = ''.join([random.choice(string.digits + string.ascii_lowercase) for c in range(0, 2)])

        current = os.path.join(current, name)

        if not os.path.exists(current):
            os.mkdir(current, 0o775)

        return self.make_path(current, depth=depth + 1)

    @action(methods=["post", "get"], detail=True)
    def chunk(self, request, pk=None):
        if request.method == "POST":
            request.data['filename'] = request.query_params.get('filename')
            request.data['chunk_number'] = request.query_params.get('chunk_number')
            request.data['total_size'] = request.query_params.get('total_size')
            request.data['total_chunks'] = request.query_params.get('total_chunks')
            request.data['chunk_size'] = request.query_params.get('chunk_size')

            serializer = serializers.ChunkCreateSerializer(data=request.data)

            if serializer.is_valid():
                chunk = serializer.validated_data['chunk']
                filename = serializer.validated_data['filename']
                chunk_number = serializer.validated_data['chunk_number']
                total_size = serializer.validated_data['total_size']
                total_chunks = serializer.validated_data['total_chunks']

                tmp_dir = tempfile.gettempdir()

                tmp_path = os.path.join(tmp_dir, f"{filename}.part{chunk_number}")

                with open(tmp_path, 'wb') as tmpf:
                    tmpf.write(chunk.read())

                if chunk_number >= total_chunks - 1:
                    chunks = glob.glob(os.path.join(tmp_dir, f"{filename}.part[0-9]*"))
                    if chunks:
                        computed = reduce(lambda x, y: x + y, [os.path.getsize(c) for c in chunks])
                        if computed >= total_size:
                            obj = self.get_object()

                            path = self.make_path(settings.MEDIA_ROOT)
                            name, ext = os.path.splitext(filename)
                            media_path = os.path.join(path, f"{obj.id}{ext}")

                            with open(media_path, "wb") as mf:
                                for chunk in sorted(chunks):
                                    with open(chunk, "rb") as cf:
                                        mf.write(cf.read())

                                    os.remove(chunk)

                            obj.path = os.path.join('uploads', os.path.relpath(media_path, settings.MEDIA_ROOT))
                            obj.save()

                return Response(status=status.HTTP_204_NO_CONTENT)
            return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)
        else:
            serializer = serializers.ChunkViewSerializer(data=request.GET)

            if serializer.is_valid():
                req = serializer.validated_data

                tmp_dir = tempfile.gettempdir()

                if os.path.exists(os.path.join(tmp_dir, u"{}.part{}".format(req["filename"], req["chunk_number"]))):
                    return Response(status=status.HTTP_204_NO_CONTENT)

                return Response(serializer.data, status=status.HTTP_404_NOT_FOUND)
            else:
                return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)


class ChatsMembers(BaseModelViewSet):
    permission_classes = [permissions.AllowAny]
    model_class = base_models.ChatMember
    serializer_class = serializers.ChatMemberListSerializer
    queryset = base_models.ChatMember.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend, OrderingFilter)
    filter_class = base_filters.ChatMemberFilter


class ChatsMembersRoles(BaseModelViewSet):
    permission_classes = [permissions.AllowAny]
    model_class = base_models.ChatMemberRole
    serializer_class = serializers.ChatMemberRoleListSerializer
    queryset = base_models.ChatMemberRole.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend, OrderingFilter)
    filter_class = base_filters.ChatMemberRoleFilter


class Messages(BaseModelViewSet):
    permission_classes = [permissions.AllowAny]
    model_class = base_models.Message
    serializer_class = serializers.MessageListSerializer
    queryset = base_models.Message.objects.all()
    filter_backends = (filters.DjangoFilterBackend, OrderingFilter)
    pagination_class = CustomPagination
    ordering_fields = ["internal_id", "created_at"]
    filter_class = base_filters.MessageFilter


class MessagesLinks(BaseModelViewSet):
    permission_classes = [permissions.AllowAny]
    model_class = base_models.MessageLink
    serializer_class = serializers.MessageLinkListSerializer
    queryset = base_models.MessageLink.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend,)
    filter_class = base_filters.MessageLinkFilter


class MessagesMedias(BaseModelViewSet):
    permission_classes = [permissions.AllowAny]
    model_class = base_models.MessageMedia
    serializer_class = serializers.MessageMediaListSerializer
    queryset = base_models.MessageMedia.objects.all()
    pagination_class = CustomPagination
    filter_backends = (filters.DjangoFilterBackend,)
    filter_class = base_filters.MessageMediaFilter

    def make_path(self, current, *, depth: 'int' = 0):
        if depth >= settings.MEDIA_PATH_DEPTH:
            return current

        name = ''.join([random.choice(string.digits + string.ascii_lowercase) for c in range(0, 2)])

        current = os.path.join(current, name)

        if not os.path.exists(current):
            os.mkdir(current, 0o775)

        return self.make_path(current, depth=depth + 1)

    @action(methods=["post", "get"], detail=True)
    def chunk(self, request, pk=None):
        if request.method == "POST":
            request.data['filename'] = request.query_params.get('filename')
            request.data['chunk_number'] = request.query_params.get('chunk_number')
            request.data['total_size'] = request.query_params.get('total_size')
            request.data['total_chunks'] = request.query_params.get('total_chunks')
            request.data['chunk_size'] = request.query_params.get('chunk_size')

            serializer = serializers.ChunkCreateSerializer(data=request.data)

            if serializer.is_valid():
                chunk = serializer.validated_data['chunk']
                filename = serializer.validated_data['filename']
                chunk_number = serializer.validated_data['chunk_number']
                total_size = serializer.validated_data['total_size']
                total_chunks = serializer.validated_data['total_chunks']

                tmp_dir = tempfile.gettempdir()

                tmp_path = os.path.join(tmp_dir, f"{filename}.part{chunk_number}")

                with open(tmp_path, 'wb') as tmpf:
                    tmpf.write(chunk.read())

                if chunk_number >= total_chunks - 1:
                    chunks = glob.glob(os.path.join(tmp_dir, f"{filename}.part[0-9]*"))
                    if chunks:
                        computed = reduce(lambda x, y: x + y, [os.path.getsize(c) for c in chunks])
                        if computed >= total_size:
                            obj = self.get_object()

                            path = self.make_path(settings.MEDIA_ROOT)
                            name, ext = os.path.splitext(filename)
                            media_path = os.path.join(path, f"{obj.id}{ext}")

                            with open(media_path, "wb") as mf:
                                for chunk in sorted(chunks):
                                    with open(chunk, "rb") as cf:
                                        mf.write(cf.read())

                                    os.remove(chunk)

                            obj.path = os.path.join('uploads', os.path.relpath(media_path, settings.MEDIA_ROOT))
                            obj.save()

                return Response(status=status.HTTP_204_NO_CONTENT)
            return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)
        else:
            serializer = serializers.ChunkViewSerializer(data=request.GET)

            if serializer.is_valid():
                req = serializer.validated_data

                tmp_dir = tempfile.gettempdir()

                if os.path.exists(os.path.join(tmp_dir, u"{}.part{}".format(req["filename"], req["chunk_number"]))):
                    return Response(status=status.HTTP_204_NO_CONTENT)

                return Response(serializer.data, status=status.HTTP_404_NOT_FOUND)
            else:
                return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)
