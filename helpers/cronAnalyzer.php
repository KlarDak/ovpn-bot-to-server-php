<?php
    require __DIR__ . "/../vendor/autoload.php";
    
    use CNS\OvpnBotToServer\BotToServer;
    use CNS\OvpnBotToServer\Databases\PDOConnector;
    use CNS\OvpnBotToServer\Types\Env;
    use CNS\OvpnBotToServer\Utils\Utils;
    use SergiX44\Nutgram\Nutgram;
    use CNS\OvpnBotToServer\Services\ApiCronClient;
    use Dotenv\Dotenv;

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

    // $bot = new Nutgram(Env::getToken());


    $expiredUsers = $botServer->usersClient()->getExpiredUsers(Utils::timeGenerator($timeNow));

    echo Utils::timeGenerator($timeNow);

    if (count($expiredUsers) > 0){
        foreach ($expiredUsers as $user) {
            $botServer->users($user->user_id)->updateActiveStatus(0, $timeNow);
            $botServer->configs($user->user_id)->blockConfigsByUserID();
            $servers = Env::getRequiredServers();

            $configs = $botServer->configs($user->user_id)->getConfigsByUserID();

            foreach ($configs as $config) {
                $botServer->apiCronClient($config->location)->banUser($config->uuid);
            }
        }
    }