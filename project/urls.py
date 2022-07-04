from django.contrib import admin
from django.conf import settings
from django.conf.urls.static import static
from django.urls import include, re_path
from rest_framework.routers import DefaultRouter
from api import views as api_views


router = DefaultRouter()
router.register(r'chats', api_views.Chats)
router.register(r'chats-medias', api_views.ChatsMedias)
router.register(r'chats-members', api_views.ChatsMembers)
router.register(r'chats-members-roles', api_views.ChatsMembersRoles)
router.register(r'chats-phones', api_views.ChatsPhones)
router.register(r'chats-tasks', api_views.ChatsTasks)
router.register(r'hosts', api_views.Hosts)
router.register(r'link', api_views.Links)
router.register(r'members', api_views.Members)
router.register(r'members-medias', api_views.MembersMedias)
router.register(r'messages', api_views.Messages)
router.register(r'messages-medias', api_views.MessagesMedias)
router.register(r'parsers', api_views.Parsers)
router.register(r'phones', api_views.Phones)
router.register(r'phones-tasks', api_views.PhonesTasks)


urlpatterns = [
    re_path(r'^api/v1/', include(router.urls)),
]
urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)

admin.site.site_header = 'Администрирование парсера телеграм'
