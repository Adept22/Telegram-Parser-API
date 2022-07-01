# Celery Telegram Parser

## Установка

```
sudo su
apt install uwsgi uwsgi-plugin-python3
```
```
cd /var/www
git clone git@gitlab.com:msr-system/chats-monitoring-api.git telegram-parser-api
cd telegram-parser-api
```
```
python3 -m venv /var/www/telegram-parser-api/venv
source ./venv/bin/activate
pip install -r requirements.txt
python3 -m manage collectstatic
```
```
cp /opt/celery/telegram-parser/conf.d/telegram-parser-api /var/www/telegram-parser-api/conf.d/telegram-parser-api.local
mkdir -p /etc/conf.d && ln -s /var/www/telegram-parser-api/conf.d/telegram-parser-api.local /etc/conf.d/telegram-parser-api
```
```
ln -s /var/www/telegram-parser-api/uwsgi/telegram-parser-api.ini /etc/uwsgi/apps-available/
ln -s /etc/uwsgi/apps-available/telegram-parser-api.ini /etc/uwsgi/apps-enabled/
```
```
ln -s /var/www/telegram-parser-api/nginx/telegram-parser-api.conf.local /etc/nginx/sites-available/telegram-parser-api.conf
ln -s /etc/nginx/sites-available/telegram-parser-api.conf /etc/nginx/sites-enabled/
```
```
mkdir -p /var/log/pg_notify
chown -R www-data:www-data /var/log/pg_notify
chmod -R 660 /var/log/pg_notify
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