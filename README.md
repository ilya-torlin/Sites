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

 Класс Users имеет следующие поля:
 - property int $id
 - property string $login
 - property string $pass
 - property string $token

 При создании пользователя если не указаны какие-то параметры, возвращается соответствующее сообщение об этом. Если в системе уже существует пользователь с таким login, то система сообщит об этом.

 Если вы захотите посмотреть пользователя с несуществующим id (которого нет в системе), приложение возвратить предупреждение об этом, при просмотре информации о пользователе мы возвращаем только доступную информацию, без конфиденциальной (пароль и токен).

 При удалении пользователя с несуществующим id (которого нет в системе), приложение возвратить предупреждение, что такого пользователя нет в системе.

 При создании файлов проверяется допустимое расширение и размер файлов.

 Существует ограничение на имя файла, если фал с таким именем существует, то его нельзя создать или загрузить.

 Параметры можно изменить в конфигурации приложения.

 ```
 basic/config/params.php
 ```

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

Класс Files содержит поля:
- property int $id
- property string $name
- property string $path
- property string $meta
- property int $size
- property string $created
- property int $creator

 Мета информацию о файле мы можем получить из поля **$file->meta**

 При просмотре списка файлов, вам возвратятся только те файлы, у которых вы являетесь создателем.

 При просмотре списка файлов в директории, если в базе нет данных о файлах, то возвращается сообщение, что файлов в директории нет.

 При просмотре/удалении/обновлении файла с несуществующим id об этом возвратиться предупреждение. Если вы захотите посмотреть/удалить/обновить файл, который вы не создавали, выведется предупреждение.

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

***Создание файла POST (передача целого файла, например изображение)***

Самы простой способ передать файл на сервер, например, через форму (поле input type=file)

request: http://api.testwork.home/files/post

method: POST

params:
- file - сам файл которые передается в массиве $_FILES

return: json c созданным файлом
Exception: ForbiddenHttpException

пример ответа:
```php
[
     {
          "name":"file1",
          "path":"/Users/ilyatorlin/Sites/basic/uploads/file1.txt",
          "size":12,
          "created":"2018-06-23 07:27:46",
          "creator":4,
          "meta":"{\"size\":12,\"modified\":1529738866,\"path\":\"\\/Users\\/ilyatorlin\\/Sites\\/basic\\/uploads\\/file1.txt\",\"is_dir\":false,\"mime_type\":\"text\\/plain\"}",
          "id":17
     }
]
```


***Создание/Закачивание файла PUT (передача целого файла, например изображение)***

передача файла в приложение, в котором мы последовательно считываем данные блоками по 1024б и записываем в файл с именем filename на сервере

request: http://api.testwork.home/files/put?filename=file1.jpg

method: PUT

params:
- filename - имя файла, под которым мы его сохраним на сервере

return: json c созданным файлом
Exception: ForbiddenHttpException

пример ответа:
```php
     {
          "name":"file1.jpg",
          "path":"/Users/ilyatorlin/Sites/basic/uploads/file1.jpg",
          "size":12,
          "created":"2018-06-23 07:27:46",
          "creator":4,
          "meta":"{\"size\":12,\"modified\":1529738866,\"path\":\"\\/Users\\/ilyatorlin\\/Sites\\/basic\\/uploads\\/file1.jpg\",\"is_dir\":false,\"mime_type\":\"image\\/jpg\"}",
          "id":17
     }
```


***Создание файла POST (создание файла из параметров в запросе)***

создание файла (например, текстового) из параметров в запросе и сохранение его на сервере

request: http://api.testwork.home/files/createpost

method: POST

params:
- filename - имя файла, под которым мы его сохраним на сервере
- content - содержимое файла (строка)

return: json c созданным файлом
Exception: ForbiddenHttpException

пример ответа:
```php
{
    "name": "3.txt",
    "path": "/Users/ilyatorlin/Sites/basic/uploads/3.txt",
    "size": 11,
    "created": "2018-06-22 21:35:32",
    "creator": 4,
    "meta": "{\"size\":11,\"modified\":1529703332,\"path\":\"\\/Users\\/ilyatorlin\\/Sites\\/basic\\/uploads\\/3.txt\",\"is_dir\":false,\"mime_type\":\"text\\/plain\"}",
    "id": 16
}
```
