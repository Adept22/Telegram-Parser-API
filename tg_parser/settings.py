from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent.parent
SECRET_KEY = 'django-insecure-06%+&y8-c!eqoqkjzt9z9rom)-(hw+37z84&#9qaj+c2-*mykj'
DEBUG = True
ALLOWED_HOSTS = ['*']

INSTALLED_APPS = [
    'django.contrib.admin',
    'django.contrib.auth',
    'django.contrib.sites',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.messages',
    'django.contrib.staticfiles',
    # 'django_celery_results',
    # 'django_celery_beat',
    'rest_framework',
    'django_filters',
    # 'post_office',
    'base',
    'api',
]

MIDDLEWARE = [
    'django.middleware.security.SecurityMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.middleware.common.CommonMiddleware',
    'django.middleware.csrf.CsrfViewMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'django.contrib.messages.middleware.MessageMiddleware',
    'django.middleware.clickjacking.XFrameOptionsMiddleware',
]

ROOT_URLCONF = 'tg_parser.urls'

TEMPLATES = [
    {
        'BACKEND': 'django.template.backends.django.DjangoTemplates',
        'DIRS': [BASE_DIR / 'templates'],
        'APP_DIRS': True,
        'OPTIONS': {
            'context_processors': [
                'django.template.context_processors.debug',
                'django.template.context_processors.request',
                'django.contrib.auth.context_processors.auth',
                'django.contrib.messages.context_processors.messages',
            ],
        },
    },
]

WSGI_APPLICATION = 'tg_parser.wsgi.application'

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.postgresql',
        'NAME': 'dev_tg_parser3',
        'USER': 'postgres',
        'PASSWORD': '123',
        'HOST': '127.0.0.1',
        'PORT': '',
    },
}

AUTH_PASSWORD_VALIDATORS = [
    {
        'NAME': 'django.contrib.auth.password_validation.UserAttributeSimilarityValidator',
    },
    {
        'NAME': 'django.contrib.auth.password_validation.MinimumLengthValidator',
    },
    {
        'NAME': 'django.contrib.auth.password_validation.CommonPasswordValidator',
    },
    {
        'NAME': 'django.contrib.auth.password_validation.NumericPasswordValidator',
    },
]

LANGUAGE_CODE = 'ru-RU'
TIME_ZONE = 'Europe/Moscow'
USE_I18N = True
USE_TZ = False
SITE_ID = 1
STATIC_URL = 'static/'
MEDIA_ROOT = './'

DEFAULT_AUTO_FIELD = 'django.db.models.BigAutoField'
MIGRATE = False

# CELERY_BROKER_URL = "redis://81.163.20.222:6379/1"
CELERY_BROKER_URL = "redis://127.0.0.1:6379"
CELERY_RESULT_BACKEND = "redis://127.0.0.1:6379"
# CELERY_RESULT_BACKEND = "django-db" #'db+postgresql+psycopg2://postgres:123@localhost/dev_tg_parser4'
CELERY_SEND_TASK_ERROR_EMAILS = True
CELERY_ACCEPT_CONTENT = ["json"]
CELERY_TASK_SERIALIZER = "json"
# CELERY_RESULT_SERIALIZER = "json"
CELERY_TIMEZONE = "Europe/Moscow"
MIGRATION_MODULES = {
    "admin": None,
    "auth": None,
    "contenttypes": None,
    "sessions": None,
    "sites": None,
}


REST_FRAMEWORK = {
    'DEFAULT_PERMISSION_CLASSES': [],
    'DEFAULT_AUTHENTICATION_CLASSES': [],
    'UNAUTHENTICATED_USER': None,
    'DEFAULT_RENDERER_CLASSES': (
        'rest_framework.renderers.JSONRenderer',
        'rest_framework.renderers.BrowsableAPIRenderer',
    ),
    'COERCE_DECIMAL_TO_STRING': False,
    'DEFAULT_PAGINATION_CLASS': 'rest_framework.pagination.LimitOffsetPagination',
    'PAGE_SIZE': 50,
}

EMAIL_BACKEND = 'post_office.EmailBackend'

DEFAULT_FROM_EMAIL = u"TEST <ypit@mail.ru>"
EMAIL_HOST = "smtp.mail.ru"
EMAIL_HOST_USER = "ypit@mail.ru"
EMAIL_HOST_PASSWORD = "8kvRKE1jsPE6MwLcuYPc"
EMAIL_PORT = 587
EMAIL_USE_TLS = True
SERVER_EMAIL = EMAIL_HOST_USER
EMAIL_TIMEOUT = 600

DATA_UPLOAD_MAX_NUMBER_FIELDS = 2000000
DJANGO_ALLOW_ASYNC_UNSAFE = True

# POST_OFFICE = {
#     # 'CELERY_ENABLED': True,
#     # 'DEFAULT_PRIORITY': 'now',
#     'LOG_LEVEL': 2,
#     'BACKENDS': {
#         'default': 'django.core.mail.backends.smtp.EmailBackend',
#     }
# }

CSRF_COOKIE_SECURE = False

CHAT_PHONE_LINKS = 3

