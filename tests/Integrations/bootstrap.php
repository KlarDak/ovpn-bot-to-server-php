<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

// Deliberately never load the application's .env file.
\Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2), '.env.integration.local')->safeLoad();
define('BTS_MYSQL_INTEGRATION_BOOTSTRAP', true);
