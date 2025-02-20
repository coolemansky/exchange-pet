# API Сервис для обмена валют

## Описание

Данный сервис предоставляет API для получения актуальных курсов валют. Сервис работает с использованием Swagger и имеет один основной эндпоинт.

## Эндпоинт

### Получение последних курсов валют

**URL:** `/api/v1/exchange/get/latest`  
**Метод:** `GET`

### Пример корректного cURL-запроса

```bash
curl -X 'GET' \
  'http://localhost/api/v1/exchange/get/latest' \
  -H 'accept: */*' \
  -H 'Authorization: Bearer user' \
  -H 'Exchange-Auth: <ваш токен, выданный на openexchangerates>'
```

## Пользователи

В системе реализована примитивная ролевая модель с двумя пользователями:

- **user** - имеет доступ к эндпоинту.
- **user1** - не имеет доступа к эндпоинту.
`\App\ProjectModule\Infrastructure\Domain\User\Memory\MemoryUserRepository::USER_LIST`

Для корректной работы эндпоинта необходимо использовать `Authorization: Bearer user` и `Exchange-Auth: <ваш корректный токен>`.

## Swagger

Вы можете ознакомиться с документацией API через Swagger. Для этого перейдите по следующему адресу:

```
http://localhost/api/doc
```
### Установка
выполнить `make init`

Пример сваггера
![Screenshot_1.png](images/Screenshot_1.png)

Пример happy-path запроса
![Screenshot_2.png](images/Screenshot_2.png)