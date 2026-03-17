<?php
    require __DIR__ . "/../vendor/autoload.php";
    
    use CNS\OvpnBotToServer\BotToServer;
    use CNS\OvpnBotToServer\Databases\PDOConnector;
    use CNS\OvpnBotToServer\Types\Env;
    use CNS\OvpnBotToServer\Utils\Utils;
    use SergiX44\Nutgram\Nutgram;
    use CNS\OvpnBotToServer\Services\ApiCronClient;
    use Dotenv\Dotenv;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

    Dotenv::createImmutable(dirname(__DIR__))->load();

    $connector = new PDOConnector(
        Env::getDatabaseKeys()->hostname,
        Env::getDatabaseKeys()->port,
        Env::getDatabaseKeys()->username,
        Env::getDatabaseKeys()->password,
        Env::getDatabaseKeys()->dbname
    );

    $botServer = new BotToServer($connector);
    $timeNow = time();

    $bot = new Nutgram(Env::getToken());

    $expiredUsers = $botServer->usersClient()->getExpiredUsers(Utils::timeGenerator($timeNow));

    echo Utils::timeGenerator($timeNow);

    if (count($expiredUsers) > 0){
        foreach ($expiredUsers as $user) {
            $botServer->user($user->user_id)->updateActiveStatus(0, $timeNow);
            $botServer->configs($user->user_id)->blockConfigsByUserID();
            $servers = Env::getRequiredServers();

            $configs = $botServer->configs($user->user_id)->getConfigsByUserID();

            foreach ($configs as $config) {
                $botServer->apiCronClient($config->location)->banUser($config->uuid);
            }

            $username = $botServer->user($user->user_id)->getUserByID()->username;

            $bot->sendMessage(
                text: "Привет, $username!\n\nК сожалению, твои конфиг-файлы отключены *за неуплату*. Для возобновления доступа, пожалуйста, произведите оплату, нажав кнопку ниже.",
                parse_mode: "markdown",
                reply_markup: InlineKeyboardMarkup::make()
                    ->addRow(
                        InlineKeyboardButton::make("💳 Оплатить конфиг-файлы", callback_data: "pay_$user->user_id", style: "success")
                    )
            );
        }
    }