<?php

declare(strict_types=1);

/**
 * Copy to config.php and adjust for your environment.
 * config.php must not be committed if it contains secrets.
 */
return [
    'site_domain' => 'brunchindetroit.com',
    'site_name_display' => 'brunch in detroit',
    'environment' => 'local',
    'debug' => true,
    'base_url' => '',
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'brunchindetroit',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'name' => 'brunch_admin_session',
    ],
    'uploads' => [
        'max_bytes' => 5 * 1024 * 1024,
        'allowed_mime' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
    ],
];
