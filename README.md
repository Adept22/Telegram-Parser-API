# API мониторинга чатов

* [Системные требования](#системные-требования)
* [Запуск](#запуск)
* [Использование API](#использование-api)
* [Доступные методы](#доступные-методы)
	* [Получение чата по UUID](#получение-чата-по-UUID)
	* [Получение списка чатов телеграмма](#получение-списка-чатов-телеграмма)
	* [Создание чата телеграмма](#создание-чата-телеграмма)
	* [Добавление чата телеграмма для мониторинга](#добавление-чата-телеграмма-для-мониторинга)
	* [Изменение чата телеграмма](#изменение-чата-телеграмма)
	* [Удаление чата телеграмма](#удаление-чата-телеграмма)

Представленный сервис является REST API и реализован на основе фреймворка Symfony версии 3.4 с использованием языка программирования PHP версии 7.0. Он предназначен для распределенной работы с разными типами файловых хранилищ, например, с такими как ПК СИУ и NextCloud.

## Системные требования
Docker, Docker Compose

## Запуск

Собираем контейнер:
```
docker-compose build --no-cache --pull
```

Запускаем контейнер:
```
docker-compose -f docker-compose.yml -f docker-compose.override.yml up -d
```

## Использование API

Все запросы к API следует осуществлять с использованием REST методов (GET, POST, PUT, DELETE). Важно передавать тип чата с которым предполагается работа. Данный параметр помечен как `CHAT_TYPE` в примерах запросов. Особенности работы с каждым типом чатов описаны каждым методом. Формат общения с сервером - `JSON`.

Параметры запроса:
```
{METHOD} https://localhost:7449/api/v1/{CHAT_TYPE}/{ACTION}
```
, где:
- `METHOD` - Метод запроса;
- `CHAT_TYPE` - Тип чата (`telegram`);
- `ACTION` - Метод API;

## Доступные методы

### Получение чата по UUID

Получает чат телеграмма по UUID.

`GET /telegram/chat/{UUID}`

Пример запроса:
```
curl -i \
	-X GET \
	-H 'Accept: application/json' \
	https://localhost:7449/api/v1/telegram/chat/cc976c61-adc6-4fd0-ac0d-832e7221709e
```

Успешный ответ:
```
HTTP/1.1 200 OK
Date: Mon, 12 Oct 2020 22:35:15 GMT
Status: 200 OK
Connection: close
Content-Type: application/json
```
```json
{
	id: "cc976c61-adc6-4fd0-ac0d-832e7221709e",
	internalId: "ID из Телеграмма",
	title: "Группа 1",
	createdAt: "2021-11-24T15:43:53+00:00",
	media: [...],
	members: [...],
}
```

### Получение списка чатов телеграмма

Получает чаты телеграмма в виде списка.

`POST /telegram/chat/find`

```
curl -i \
	-X POST \
	-H 'Accept: application/json' \
	-d '{ "members": { "id": "9d6053d0-58f6-11ec-bf63-0242ac130002" }, "_start": "100", "_limit": "10", "_order": "ASC", "_sort": "createdAt" }'
	https://localhost:7449/api/v1/telegram/chat/find
```

Успешный ответ:
```
HTTP/1.1 200 OK
Date: Mon, 12 Oct 2020 22:35:15 GMT
Status: 200 OK
Connection: close
Content-Type: application/json
```
```json
[
    ...
	{
		"id": "cc976c61-adc6-4fd0-ac0d-832e7221709e",
		"internalId": "ID из Телеграмма",
		"title": "Группа 1",
		"createdAt": "2021-11-24T15:43:53+00:00",
		"media": [...],
		"members": [...],
	},
	{
		"id": "b93709fa-58f6-11ec-bf63-0242ac130002",
		"internalId": "ID из Телеграмма 2",
		"title": "Группа 2",
		"createdAt": "2021-12-02T11:22:03+00:00",
		"media": [...],
		"members": [...],
	}
    ...
]
```

### Создание чата телеграмма

Создает сущность чата телеграма.

> Метод доступен только для пользователей с ролью `ROLE_PARSER`

`POST /telegram/chat`

```
curl -i \
	-X POST \
	-H 'Accept: application/json' \
	-d '{ "internalId": "ID из Телеграмма", "title": "Группа 3" }'
	https://localhost:7449/api/v1/telegram/chat
```

Успешный ответ:
```
HTTP/1.1 201 Created
Date: Mon, 12 Oct 2020 22:35:15 GMT
Status: 201 Created
Connection: close
```
```json
{
	"id": "b49e4236-58f7-11ec-bf63-0242ac130002",
	"internalId": "ID из Телеграмма",
	"title": "Группа 3",
	"createdAt": "2021-11-24T15:43:53+00:00",
	"media": [],
	"members": [],
}
```

### Добавление чата телеграмма для мониторинга

Добавляет чат телеграмма на мониторинг.

`POST /telegram/chat/monitoring`

```
curl -i \
	-X POST \
	-H 'Accept: application/json' \
	-d '{ "link": "ссылка на чат" }'
	https://localhost:7449/api/v1/telegram/chat/monitoring
```

Успешный ответ:
```
HTTP/1.1 201 Created
Date: Mon, 12 Oct 2020 22:35:15 GMT
Status: 201 Created
Connection: close
```
```json
{
	"id": "066b412c-58f8-11ec-bf63-0242ac130002",
	"internalId": "ID из Телеграмма",
	"title": "Группа 4",
	"createdAt": "2021-11-24T15:43:53+00:00",
	"media": [],
	"members": [],
}
```

Ошибочные ответы:

- Чат по ссылке не найден или ссылка просрочена:
	```
	HTTP/1.1 404 Not Found
	Date: Mon, 12 Oct 2020 22:35:15 GMT
	Status: 404 Not Found
	Connection: close
	```

### Изменение чата телеграмма

Изменяет сущность чата телеграма.

> Метод доступен только для пользователей с ролью `ROLE_PARSER`

`PUT /telegram/chat/{UUID}`

```
curl -i \
	-X PUT \
	-H 'Accept: application/json' \
	-d '{ "title": "Группа 4 теперь 5" }'
	https://localhost:7449/api/v1/telegram/chat/066b412c-58f8-11ec-bf63-0242ac130002
```

Успешный ответ:
```
HTTP/1.1 200 OK
Date: Mon, 12 Oct 2020 22:35:15 GMT
Status: 200 OK
Connection: close
```
```json
{
	"id": "066b412c-58f8-11ec-bf63-0242ac130002",
	"internalId": "ID из Телеграмма",
	"title": "Группа 4 теперь 5",
	"createdAt": "2021-11-24T15:43:53+00:00",
	"media": [],
	"members": [],
}
```

### Удаление чата телеграмма

Удаляет сущность чата телеграма и снимает с мониторинга.

> Метод доступен только для пользователей с ролью `ROLE_PARSER`

`DELETE /telegram/chat/{UUID}`

```
curl -i \
	-X DELETE \
	https://localhost:7449/api/v1/telegram/chat/066b412c-58f8-11ec-bf63-0242ac130002
```

Успешный ответ:
```
HTTP/1.1 204 No Content
Date: Mon, 12 Oct 2020 22:35:15 GMT
Status: 204 No Content
Connection: close
```