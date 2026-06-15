<?php

declare(strict_types=1);

function app_config(): array
{
    static $config = null;

    if ($config === null) {
        $path = APP_ROOT . '/config/config.php';
        if (!is_file($path)) {
            throw new RuntimeException('Missing app/config/config.php — copy from config.example.php');
        }
        $config = require $path;
    }

    return $config;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function asset_url(string $path): string
{
    $path = ltrim($path, '/');
    $base = rtrim((string) (app_config()['base_url'] ?? ''), '/');

    if ($base === '') {
        return '/' . $path;
    }

    return $base . '/' . $path;
}

function site_domain(): string
{
    return (string) app_config()['site_domain'];
}

function site_name_display(): string
{
    return (string) app_config()['site_name_display'];
}

function page_title(string $title): string
{
    return $title . ' | ' . site_domain();
}

function admin_url(string $path): string
{
    $path = ltrim($path, '/');
    if (str_starts_with($path, 'admin/')) {
        $path = substr($path, 6);
    }

    return asset_url('admin/' . $path);
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}
