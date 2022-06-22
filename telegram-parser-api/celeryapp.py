import os
from celery import Celery

os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'telegram-parser-api.settings')

app = Celery('telegram-parser-api')
app.config_from_object('django.conf:settings', namespace='CELERY')
app.autodiscover_tasks()
