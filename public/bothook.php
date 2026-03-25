<?php
    require __DIR__ . '/../vendor/autoload.php';
    require 'inline_buttons/__main_buttons.php';

    use CNS\OvpnBotToServer\BotToServer;
    use CNS\OvpnBotToServer\Databases\PDOConnector;
    use CNS\OvpnBotToServer\Langs\Langs;
    use CNS\OvpnBotToServer\Types\Env;
    use Dotenv\Dotenv;

    use SergiX44\Nutgram\Nutgram;
    use SergiX44\Nutgram\RunningMode\Webhook;
    use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
    use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

    Dotenv::createImmutable(__DIR__ . '/../')->load();

    $bot = new Nutgram(Env::getToken());

    $connector = new PDOConnector(Env::getDatabaseKeys()->hostname, Env::getDatabaseKeys()->port, Env::getDatabaseKeys()->username, Env::getDatabaseKeys()->password, Env::getDatabaseKeys()->dbname);
    $ovpnConnector = new BotToServer($connector);

    $bot->setRunningMode(WebHook::class);

    $bot->run();

    