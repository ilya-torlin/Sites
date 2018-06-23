HTTP API сервис для работы с файловой системой
----------------------------------------------

Сервис позволяет работать с файловой системой на сервере в рамках одной директории без поддержки подкаталогов, которая указывается в настройках системы. Вы можете посылать GET,POST,PUT,UPDATE,DELETE запросы для создания, просмотра, обновления, удаления файлов и пользователей, которые могут иметь доступ к файлам.

Каждый пользователь может работать только с теми файлами создателем которых он является, при попытке получить доступ к файлам другого пользователя, выдается предупреждение.

Доступ к приложения имеют только авторизированные пользователи, по средствам токена доступа.

Доступ ко все информации происходит через БД, в который хранятся пользователи системы, информация о добавленных файлах и связь файлов и  пользователей.

**Структура проекта**

- api.testwork.home/       содержит точку входа в приложение index.php
- basic/                   содержит логику работы (Yii2 basic app)
- testwork.home/           содержит страницу для теста index.php

**Технологии**

Проект написан на PHP 7 с использование баз данных MySQL, установка всех зависимостей и необходимых компонентов для создания и работы с приложение производились с помощью Composer.

Для написания сервиса был выбран фреймворк Yii2 (basic).

Для доступа к сервису использавалась аутентификация по токену (HTTP Bearer token).

Директория для записи файлов задается в конфигурации сервиса.

Лимит размера файлов задается в конфигурации сервиса.

Реализована привязка файлов к создателю, для ограницения доступа.

ФУНКЦИОНАЛ СЕРВИСА
------------------

(на примере url: http://api.testwork.home/)

### Работа с пользователями

***Добавление пользователя***

request: http://api.testwork.home/users

method: POST

params:
- login
- pass

return: new User (данные логина и токена доступа, пароль записывется в базу как hash, токен записываются как рандомная строка)  
Exception: ForbiddenHttpException

пример ответа:
```php
{
    "login": "user1",
    "token": "FCGlqZtqTkaEhIJX"
}
```

***Удаление пользователя***

Удаляем пользователя по <id>

request: http://api.testwork.home/users/<id>

method: DELETE

params:
- id

return: json строка с сообщением, что пользователь удален
Exception: ForbiddenHttpException

пример ответа:
```php
{
    "message": "Пользователь удален"
}
```

***Список пользователей***

Получаем список всех пользователей в приложении

request: http://api.testwork.home/users/

method:  GET

params:  нет

return: json строка со списком пользователей
Exception: ForbiddenHttpException

пример ответа:
```php
[
    {
        "id": 4,
        "login": "user"
    },
    {
        "id": 7,
        "login": "user1"
    }
]
```

### Работа с файлами

***Список файлов***

request: http://api.testwork.home/files

method: GET

params: нет

return: json строка со списком файлов, либо сообщение, что в директории нет файлов
Exception: ForbiddenHttpException

пример ответа:
```php
[
    {
        "id": 15,
        "name": "file123",
        "path": "/Users/ilyatorlin/Sites/basic/uploads/file123.txt",
        "meta": "{\"size\":12,\"modified\":1529682839,\"path\":\"\\/Users\\/ilyatorlin\\/Sites\\/basic\\/uploads\\/file123.txt\",\"is_dir\":false,\"mime_type\":\"text\\/plain\"}",
        "size": 12,
        "created": "2018-06-22 15:53:59",
        "creator": 4
    },
    {
        "id": 16,
        "name": "3.txt",
        "path": "/Users/ilyatorlin/Sites/basic/uploads/3.txt",
        "meta": "{\"size\":11,\"modified\":1529703332,\"path\":\"\\/Users\\/ilyatorlin\\/Sites\\/basic\\/uploads\\/3.txt\",\"is_dir\":false,\"mime_type\":\"text\\/plain\"}",
        "size": 11,
        "created": "2018-06-22 21:35:32",
        "creator": 4
    }
]
```

***Просмотр информации файла***

request: http://api.testwork.home/files/<id>

method: GET

params:
- id - идентификатор файла в системе

return: json строка с объектом файла, в поле content - находится содержимое файла
Exception: ForbiddenHttpException

пример ответа:
```php
{
    "file": {
        "id": 16,
        "name": "3.txt",
        "path": "/Users/ilyatorlin/Sites/basic/uploads/3.txt",
        "meta": "{\"size\":11,\"modified\":1529703332,\"path\":\"\\/Users\\/ilyatorlin\\/Sites\\/basic\\/uploads\\/3.txt\",\"is_dir\":false,\"mime_type\":\"text\\/plain\"}",
        "size": 11,
        "created": "2018-06-22 21:35:32",
        "creator": 4
    },
    "content": "yjdsq yjdsq"
}
```

***Удаление файла***

request: http://api.testwork.home/files/<id>

method: DELETE

params:
- id - идентификатор файла в системе

return: json строка с сообщением, что файл удален файла
Exception: ForbiddenHttpException

пример ответа:
```php
{
     "message": "Файл успешно удален"
}
```

***Обновление содержимого файла***

request: http://api.testwork.home/files/update

method: POST

params:
- id - идентификатор файла в системе
- content - обновленное содержимое файла

return: json строка с сообщением, что файл удален файла
Exception: ForbiddenHttpException

пример ответа:
```php
{
     "message": "Файл успешно обновлен"
}
```
