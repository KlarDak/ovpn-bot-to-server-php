# OVPN-Bot-To-Server

Библиотека для коммуникации между серверами на базе протоколов VPN и ботом / панелью управления. 

## Описание

**BTS (Bot To Server)** — PHP-библиотека для управления пользователями, VPN-доступами, подписками и удалёнными серверами через единый программный интерфейс.

Библиотека объединяет работу с базой данных и API удалённых серверов, позволяя создавать и изменять пользователей, управлять их доступами, получать данные о серверах и выполнять операции через серверные контракты.

### Расширяемость

BTS построена на модульной архитектуре и позволяет заменять стандартные компоненты собственными реализациями.

**По умолчанию предусмотрены:**

- *MySQL через PDO* — для работы с базой данных.

- *GuzzleHTTP* — для выполнения HTTP-запросов.

- *Серверные контракты* — для взаимодействия с поддерживаемыми VPN-протоколами и собственными API.

Разработчик может реализовать собственный коннектор базы данных, HTTP-клиент или серверный контракт, используя соответствующие интерфейсы библиотеки.

## Требования

Для использования библиотеки требуется следующее:

- PHP 8.2+
- Composer 2.x
- ext-openssl
- ext-json
- MySQL 8.0+ _(при взаимодействии с базой данных через PDOConnector)_
- ext-pdo _(при взаимодействии с базой данных через PDOConnector)_
- ext-pdo_mysql _(при взаимодействии с базой данных через PDOConnector)_

Дополнительные зависимости библиотека скачивает автоматически при установке.

## Установка

Для установки библиотеки, создайте файл ``composer.json`` и добавьте в него следующее содержимое:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/KlarDak/ovpn-bot-to-server-php"
        }
    ],
    "require": {
        "cns/ovpn-bot-to-server-php": "dev-main"
    }
}
```
После чего установите библиотеку:

```bash
composer update
```

## Подготовка окружения

После установки библиотеки через Composer выполните:

```bash
vendor/bin/publish
```

Команда создаст в корне вашего проекта папку bts/ и скопирует в неё файлы из библиотеки:

```text
bts/
├── schema.sql
├── .temp
└── .local
```

Исходные файлы библиотеки останутся без изменений. Команда только копирует файлы — импортировать schema.sql в базу данных нужно самостоятельно.

Если файлы назначения уже существуют, публикация остановится. Для их перезаписи используйте --force:

```bash
vendor/bin/publish --force
```

Перезапись удалит ваши изменения в этих файлах.

Чтобы выбрать другую папку назначения, передайте ``--target``:

```bash
vendor/bin/publish --target=config/bts
```

Относительный путь считается от корня проекта. Также можно указать абсолютный путь.

Справка по команде:

```bash
vendor/bin/publish --help
```

После выполнения команды, заполните файлы, согласно указанным комментариям, и импортируйте файл ``.sql`` в выбранную вами базу данных.

**Библиотека готова к использованию!**

## Быстрый старт

Для начала работы библиотеки, создайте класс для подключения к базе данных, определитесь с HTTP-клиентом и создайте сам класс ``BotToServer``:

Пример с использованием встроенных коннектора к базе данных ``PDOConnector`` и HTTP-клиента ``GuzzleHTTP``:

```php
<?php

require __DIR__ . "/vendor/autoload.php";

use CNS\BotToServer\Databases\PDOConnector;
use CNS\BotToServer\Http\Drivers\GuzzleHttpClient;
use CNS\BotToServer\BotToServer;
use CNS\BotToServer\Types\Env;
use Dotenv\Dotenv;

Dotenv::createImmutable(__DIR__)->load();

$connector = new PDOConnector(
    hostname: Env::getString("DB_HOSTNAME"),
    port: Env::getInt("DB_PORT"),
    username: Env::getString("DB_USERNAME"),
    password: Env::getString("DB_PASSWORD"),
    dbname: Env::getString("DB_DATABASE")
);

$bts = new BotToServer(
    database: $connector,
    http: GuzzleHttpClient::class
);

...
```

### Изменение вводных констант

При необходимости, вы можете обойтись без ``.env``-файла, и создавать все необходимые константы окружения через встроенный класс ``BTSConfig``.

Пример создания класса библиотеки из образца выше, но без использования заранее созданного файла ``.env``:

```php
<?php

require __DIR__ . "/vendor/autoload.php";

use CNS\BotToServer\Databases\PDOConnector;
use CNS\BotToServer\Http\Drivers\GuzzleHttpClient;
use CNS\BotToServer\BotToServer;
use CNS\BotToServer\Types\Env;
use CNS\BotToServer\Config\BTSConfig;

$btsConfig = new BTSConfig(
    encryptionKey: "EXAMPLE_OF_ENCRYPTION_TOKEN",
    role: "admin",
    audCode: "test_01",
    configsDir: __DIR__ . "/users",
    logsDir: __DIR__ . "/logs",
    contracts: [
        "openvpn" => CNS\BotToServer\Contracts\OpenVPN\OpenVPNContract::class,
        "xray" => CNS\BotToServer\Contracts\Xray\XrayContract::class
    ]
);

$connector = new PDOConnector(
    hostname: "localhost",
    port: 3306,
    username: "root",
    password: "root",
    dbname: "unlimitdb"
);

$bts = new BotToServer(
    database: $connector,
    http: GuzzleHttpClient::class,
    btsConfig: $btsConfig
);

...
```

> Более подробное описание ``BTSConfig`` описано в файле документации "[Создание конфигурации библиотеки](docs/btsconfig.md)".

## Использование библиотеки

Экземпляр ``BotToServer`` предоставляет единый интерфейс для работы с пользователями, доступами, серверами, подписками и транзакциями.

```php
use CNS\BotToServer\Types\UserType;

$user = $bts->user(770, UserType::TELEGRAM)->get();

// Вернёт свойство созданного класса User со значением ID.
echo $user->id;
```

```php
$access = $bts->access("0bc4734c-ca6c-11f1-a43a-38f3ab6d0b2a")->get();

// Вернёт свойство созданного класса Access со значением UUID.
echo $access->uuid;
```

```php
$server = $bts->server("test_02")->info();

// Вернёт дамп созданного класса Server.
var_dump($server);
```

```php
$transaction = $bts->transaction()->get(112);

// Вернёт дамп созданного класса Transaction.
var_dump($transaction);
```

```php
$newServer = $bts->server()->create(
    code: "test_03",
    name: "test",
    host: "192.168.1.11",
    port: 3000,
    api_endpoint: "/api",
    encrypt_secret_code: "ENCRYPT_CODE",
    type: "openvpn"
);

// Вернёт ID новой записи в базе данных 
echo $newServer;
```

```php
$access = $bts->server("test_03")->contract();

// Вернёт результат создания конфигурации на сервере ответом Boolean
echo $access->access()->create(uuid: "0bc4734c-ca6c-11f1-a43a-38f3ab6d0b2a", type: "user", ttl: 3600);
```

Подробнее про методы Resources указано в файле "[Список доступных методов библиотеки](docs/availableMethods.md)".

## Архитектура

BTS построена на модульной архитектуре с разделением ответственности между компонентами. Центральной точкой взаимодействия является класс `BotToServer`, предоставляющий единый интерфейс для работы с библиотекой.

Основные компоненты:

- **BotToServer** — основной класс библиотеки, предоставляющий доступ к ресурсам и серверным контрактам.
- **Resources** — классы для управления пользователями, VPN-доступами, серверами, подписками и транзакциями.
- **Contracts** — адаптеры для взаимодействия с API удалённых серверов. Позволяют реализовать поддержку собственных протоколов и серверных решений.
- **Database Connectors** — компоненты взаимодействия с базой данных через интерфейс `IDBConnector`. По умолчанию предусмотрен `PDOConnector` для MySQL.
- **HTTP Clients** — компоненты для отправки HTTP-запросов через интерфейс `HttpClientInterface`. По умолчанию используется GuzzleHTTP.
- **BTSConfig** — объект конфигурации библиотеки, содержащий настройки и соответствия серверных контрактов.

Ресурсы выполняют операции с данными, а серверные контракты обеспечивают взаимодействие с удалёнными API. Благодаря разделению этих компонентов библиотеку можно расширять без изменения её основного кода.

Подробнее об устройстве и расширении BTS см. [Архитектура библиотеки](docs/architecture.md).

## Тестирование

Библиотека полностью покрыта тестами. Чтобы их запустить, введите в консоли команду:

```bash
composer test:unit
```

### Интеграционные тесты

Для тестирования библиотеки с работой на тестовой базе данных, необходимо подготовить окружение заранее, так как для этого также используется отдельный конфигурационный файл.

Создайте файл ``.env.integration.local`` и заполните его следующим содержимым:

```text
# Говорит системе провести интеграционное тестирование. Замените на 0, чтобы отключить режим тестирования
BTS_RUN_DB_TESTS=1
# Хост базы данных
BTS_TEST_DB_HOST=
# Порт базы данных
BTS_TEST_DB_PORT=
# Имя базы данных
BTS_TEST_DB_NAME=unlimitdb_test
# Имя пользователя
BTS_TEST_DB_USER=
# Пароль
BTS_TEST_DB_PASSWORD=
```

> **Важно:** проводите тестирование *исключительно* на базе тестовой базы данных. Не используйте для этих целей основную базу данных для предотвращения возможного повреждения информации в таблицах.

После создания файла и тестовой базы данных, запустите интеграционные тесты:

```bash
composer test:integration
```

### Запуск одновременного тестирования

Для проведения основных тестов методов библиотеки и интеграционного тестирования одновременно, запустите общее тестирование:

```bash
composer test:all
```

