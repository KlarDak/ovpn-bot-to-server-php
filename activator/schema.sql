CREATE DATABASE 'unlimitdb';

CREATE TABLE `accesses` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Internal access ID',
  `user_id` int NOT NULL COMMENT 'ID of the user who owns the access',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Unique access identifier',
  `name` text COMMENT 'User-defined access name',
  `type` varchar(32) NOT NULL COMMENT 'VPN access type',
  `server_id` int NOT NULL COMMENT 'ID of the server hosting the access',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Access creation date and time',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Whether the access is active',
  `disabled_at` datetime DEFAULT NULL COMMENT 'Access deactivation date and time',
  `is_dropped` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether the access is permanently dropped',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Internal user ID',
  `telegram_id` bigint DEFAULT NULL COMMENT 'Telegram user ID',
  `email` text COMMENT 'Email for non-Telegram user',
  `username` text COMMENT 'User''s Telegram username',
  `language` varchar(100) NOT NULL COMMENT 'User''s preferred language',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Account creation date and time',
  `last_activity_at` datetime DEFAULT NULL COMMENT 'Last user activity date and time',
  `expired_at` datetime DEFAULT NULL COMMENT 'Subscription expiration date and time',
  `last_payment_at` datetime DEFAULT NULL COMMENT 'Last successful payment date and time',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Whether the user account is active',
  `disabled_at` datetime DEFAULT NULL COMMENT 'Account deactivation date and time',
  `is_dropped` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether the user account is permanently dropped',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_unique_userid` (`telegram_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Stores user accounts, activity, subscription, and account status';

CREATE TABLE `servers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL,
  `name` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `port` int unsigned NOT NULL,
  `api_endpoint` NOT NULL text,
  `encrypt_secret_code` text NOT NULL,
  `type` varchar(32) NOT NULL,
  `subs_url` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(32) NOT NULL,
  `disabled_at` datetime DEFAULT NULL,
  `is_dropped` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `subs` (
  `id` int unsigned NOT NULL,
  `short_code` varchar(100) NOT NULL,
  `server_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `access_id` int unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_used` tinyint(1) NOT NULL DEFAULT '0',
  `used_at` datetime DEFAULT NULL,
  `is_dropped` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;