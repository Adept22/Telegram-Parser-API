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
cp uwsgi/telegram-parser-api.ini /etc/uwsgi/apps-available/
ln -s /etc/uwsgi/apps-available/telegram-parser-api.ini /etc/uwsgi/apps-enabled/telegram-parser-api.ini
```
```
cp nginx/telegram-parser-api.conf /etc/nginx/sites-available/
ln -s /etc/nginx/sites-available/telegram-parser-api.conf /etc/nginx/sites-enabled/telegram-parser-api.conf
```
```
mkdir -p /etc/conf.d
cp conf.d/pg_notify /etc/conf.d/
cp systemd/pg_notify.service /etc/systemd/system/
```
```
systemctl daemon-reload
systemctl enable pg_notify.service
```

## Запуск

```
systemctl nginx restart
service uwsgi restart
systemctl start pg_notify.service
```