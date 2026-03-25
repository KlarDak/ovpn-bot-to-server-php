<?php

require __DIR__ . "/../vendor/autoload.php";

use CNS\OvpnBotToServer\Types\Env;
use CNS\OvpnBotToServer\Databases\PDOConnector;
use Dotenv\Dotenv;

Dotenv::createImmutable(__DIR__ . '/../')->load();

$connector = new PDOConnector(Env::getDatabaseKeys()->hostname, Env::getDatabaseKeys()->port, Env::getDatabaseKeys()->username, Env::getDatabaseKeys()->password, Env::getDatabaseKeys()->dbname);

echo "Creating 'users' table...";

$connector->execute(
    "CREATE TABLE IF NOT EXISTS `users` (
    `id` int NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
    `user_id` VARCHAR(64) NOT NULL COMMENT 'Telegram User ID',
    `username` text NOT NULL COMMENT 'Telegram Username',
    `language` text NOT NULL COMMENT 'Language of user',
    `configs_count` int DEFAULT '0' COMMENT 'Number of Configurations',
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation Timestamp',
    `expired_at` datetime DEFAULT NULL COMMENT 'Expiration Timestamp',
    `last_payment_at` datetime DEFAULT NULL COMMENT 'Last Payment Timestamp',
    `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Active Status',
    `disabled_at` datetime DEFAULT NULL COMMENT 'Disabled Timestamp',
    `is_dropped` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
    ", []
);

echo "Creating 'configs' table...";

$connector->execute(
    "CREATE TABLE IF NOT EXISTS `configs` (
    `id` int NOT NULL AUTO_INCREMENT,
    `uuid` varchar(255) NOT NULL,
    `user_id` varchar(255) NOT NULL,
    `config_name` text,
    `type` text NOT NULL,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `status` tinyint(1) NOT NULL DEFAULT '1',
    `blocked_at` datetime DEFAULT NULL,
    `is_blocked` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
", []);

echo "Tables was created";