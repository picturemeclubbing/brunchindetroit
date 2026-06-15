<?php

declare(strict_types=1);

define('APP_ROOT', __DIR__);
define('PUBLIC_ROOT', dirname(__DIR__) . '/public');

require APP_ROOT . '/helpers/functions.php';
require APP_ROOT . '/helpers/auth.php';
require APP_ROOT . '/config/database.php';

$config = app_config();

if (!empty($config['debug']) && ($config['environment'] ?? '') === 'local') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

date_default_timezone_set('America/Detroit');
