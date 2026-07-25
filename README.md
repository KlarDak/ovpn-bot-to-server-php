# OVPN-bot-to-server-PHP v2.1.9

PHP-библиотека для связи между Telegram-ботом и OpenVPN Controller API `v2.1.9`.

![PHP Version](https://img.shields.io/badge/PHP-8.1+-blue)
![License](https://img.shields.io/badge/License-MIT-green)
![Status](https://img.shields.io/badge/Status-Beta-yellow)

## ❗️ Данный код НЕ является библиотекой общего использования
Если вы случайно наткнулись на этот код, пропустите данный репозиторий - он не предназначен для общего использования, является исключительно презентационным проектом.

## ❗️ This code is NOT a general-purpose library.
If you have come across this code by accident, please skip this repository — it is not intended for public use and exists solely as a presentation project.

## Требования

Для работы библиотеки, должно быть установлено следующее:

- PHP 8.1+
- MySQL 8.0+
- Redis
- cURL
- ``composer`` или ``git``

## Установка

Библиотека предназначена для работы с PHP через менеджер **composer** (без обработчика для бота) или в варианте **клонирования репозитория** (рекомендуется для работы со встроенным файлом-обработчиком для бота)

### Установка через composer

Для установки библиотеки, создайте файл ``composer.json`` и добавьте следующее:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/KlarDak/ovpn-bot-to-server-php"
        }
    ],
    "require": {
        "cns/ovpn-bot-to-server-php": "dev-dev"
    }
}
```

После чего введите команду:

```bash
composer update
```

> На момент версии **dev-2.1** библиотека доступна только в версии **dev**.

### Установка через git

Для установки через ``git``, используйте команду:

```bash
git clone -b main https://github.com/KlarDak/ovpn-bot-to-server-php.git
```

## Настройка работы библиотеки

До начала работы, убедитесь в установленных на сервере реляционной базы данных и сервера Redis.

> На момент версии ``dev-2.1`` доступен адаптер для работы только с MySQL через драйвер PDO.

Убедитесь, что вы настроили файл-окружения ``.env``. Его шаблон находится в файле ``.env.temp``.

### Важное про настройку окружения для связи с серверами

У каждого сервера имеются свои **секретные ключи** для JWT-токенов, а также свои **индекс** и, естественно, **адрес**. Перед запуском, убедитесь, что вы создали связки значений для серверов.

**Пример связок:**

```text
# Адрес дочернего сервера
RU_SERVER_ADDRESS=localhost:3000

# Индекс дочернего сервера
RU_SERVER_INDEX=RU_SRV_1

# Секретный ключ дочернего сервера
RU_SECRET_KEY=secret_do_not_push_this_on_the_github
```

Где ``RU`` - буквенное обозначение сервера для работы со связкой в библиотеке. 

**Важно:** у каждого сервера, вне зависимости от их локации, должно быть *уникальное буквенное обозначение*.

### Настройка пользователя базы данных

Изначально функциональный набор библиотеки предполагает схему ``CREATE-READ-UPDATE``. Опция ``DELETE`` **не используется** для удаления строк - вместо этого используется отдельное поле ``is_dropped``, скрывающая строки из общей выборки.

### Настройка баз данных

После подготовки файла ``.env``, нужно произвести подготовку базы данных для работы.

Для этого перейдите в директорию ``helpers`` и запустите файл ``tablesInstaller.php``. Настройка будет произведена автоматически.

> На момент версии ``dev-2.1``, файл работает **только** с адаптером PDO.

```bash
cd helpers/
php tablesInstaller.php
```

**Важно:** так как в этом случае выполняется создание таблиц, необходим пользователь в базе данных с привилегией ``CREATE``.

## Работа с библиотекой

Библиотека создана для работы с базой данных пользователей и их конфиг-файлов, а также для связи по API между сервером бота и серверами OpenVPN.

### Подготовка к работе

Для работы классов библиотеки, создайте класс с функциями для работы с базой данных. На данный момент в библиотеке реализован класс, совместимый с драйвером ``PDO`` - ``PDOConnector``.

```php
<?php

use CNS\OvpnBotToServer\Databases\PDOConnector;

$db = new PDOConnector($hostname, $port, $username, $password, $dbname);

```

**Важно:** допустимо создание адаптеров и для других драйверов или баз данных. Для этого создайте класс, совместимый с функциями интерфейса ``IDBConnector``.

### BotToServer

Основной класс для работы с библиотекой. Используется как для связи с базой данных, так и для запросов по API.

Аргументом передаётся подключение к базе данных, созданное шагом ранее.

```php
<?php

use CNS\OvpnBotToServer\BotToServer;
use CNS\OvpnBotToServer\Databases\PDOConnector;

$db = new PDOConnector($hostname, $port, $username, $password, $dbname);

$botToServer = new BotToServer($db);
```

### BTSStatic

> Доступен для работы с версии **dev-2.3**

Статический варианта класса **BotToServer**. Обладает тем же набором методов, кроме отсутствия ``__construct`` и наличия метода ``setDBConnection($databaseConnector)`` - метода для установки объекта класса подключения к базе данных.

```php
<?php

use CNS\OvpnBotToServer\BotToServer;
use CNS\OvpnBotToServer\Databases\PDOConnector;

$db = new PDOConnector($hostname, $port, $username, $password, $dbname);

BTSStatic::setDBConnection($db);
```

### Примеры использования

Для получения данных пользователя, используйте метод ``user()`` с ``$user_id`` - User ID пользователя:

```php
use CNS\OvpnBotToServer\BotToServer;

$user = $botToServer->user(1);
// или
$user = BTSStatic::user(1);

// Получить username
print($user->getUserByID()->username);

// Обновить username
print($user->updateUsername("new_username"));
```

Для получения данных конфиг-файла, используйте метод ``config()`` с аргументом ``$uuid`` - уникальным идентификатором конфиг-файла:

```php
use CNS\OvpnBotToServer\BotToServer;

$config = $botToServer->config("uuid");
// или
$config = BTSStatic::config("uuid");

// Получить имя конфиг-файла
print($config->getConfig()->config_name);

// Обновить username
print($config->updateConfigName("new name"));
```

Для отправления запросов по API, используйте метод ``apiClient()`` с аргументом ``$server_id`` - идентификатором сервера, указанным в ``.env``-файле.

```php
use CNS\OvpnBotToServer\BotToServer;

// Обращаемся к RU-серверу, указанному в .env-файле
$apiServer = $botToServer->apiClient("RU");
// или
$apiServer = BTSStatic::apiClient("RU");

// Получить данные конфиг-файла с сервера
var_dump($apiServer->getConfig("uuid"));

// Создадим новый конфиг-файл с типом "user" и временем работы 100 секунд
var_dump($apiServer->postConfig("uuid", 100, "user"));
```

Для блокировки или разблокировки всех конфиг-файлов пользователя разом используйте метод ``configs()`` с аргументом ``$user_id`` - User ID пользователя.

```php
use CNS\OvpnBotToServer\BotToServer;

// Обращаемся к RU-серверу, указанному в .env-файле
$configs = $botToServer->configs(1);
// или
$configs = BTSStatic::configs(1);

// Обновит данные о блокировке всех конфиг-файлов пользователя
print($configs->blockConfigsByUserID());

// Обновит данные о разблокировке всех конфиг-файлов пользователя
print($configs->pardonConfigsByUserID());
```

### Документация

Более подробная документация ко всем методам, свойствам и ошибкам находится в директории ``docs/``.

**Подробный список:**

- [Доступные адаптеры](docs/Adapters.md)
- [Доступные сервисы для связи с API](docs/Services.md)
- [Доступные типы данных](docs/Types.md)
- [Доступные ошибки](docs/Exceptions.md)
- [Утилиты и для чего они нужны](docs/Utils.md)
- [Скрипты для настройки баз данных и cron-работы](docs/Helpers.md)
- [Языковые пакеты для бота](docs/Langs.md)
