# Celery Telegram Parser

## Установка

```
sudo su
apt install uwsgi uwsgi-plugin-python3
```
```
cd /opt
git clone git@gitlab.com:msr-system/chats-monitoring-api.git telegram-parser-api
cd telegram-parser-api
```
```
python3 -m venv /opt/telegram-parser-api/venv
source ./venv/bin/activate
pip install -r requirements.txt
python3 -m manage collectstatic
```
```
mkdir -p /etc/conf.d
ln -s conf.d/prod/telegram-parser-api /etc/conf.d/
```
```
ln -s uwsgi/telegram-parser-api.ini /etc/uwsgi/apps-available/
ln -s /etc/uwsgi/apps-available/telegram-parser-api.ini /etc/uwsgi/apps-enabled/
```
```
ln -s nginx/telegram-parser-api.conf /etc/nginx/sites-available/
ln -s /etc/nginx/sites-available/telegram-parser-api.conf /etc/nginx/sites-enabled/
```
```
ln -s systemd/pg_notify.service /etc/systemd/system/
systemctl daemon-reload
systemctl enable pg_notify.service
```

## Запуск

```
systemctl start uwsgi
systemctl start nginx
systemctl start pg_notify
```