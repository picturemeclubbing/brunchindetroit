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

/**
 * Build an absolute canonical URL for a given site path.
 *
 * Examples:
 *   canonical_url('blog.php')
 *   canonical_url('article.php?slug=my-post')
 *
 * Uses the configured site_domain when available, choosing a sensible scheme
 * (http for localhost / local dev, https otherwise). If site_domain is
 * missing, falls back to the current request host. The path is appended
 * as-is (already-escaped by callers for HTML output via e()).
 */
function canonical_url(string $path = ''): string
{
    $config = app_config();
    $domain = (string) ($config['site_domain'] ?? '');
    $isLocal = ($config['environment'] ?? '') === 'local'
        || $domain === ''
        || str_contains($domain, 'localhost')
        || str_contains($domain, '127.0.0.1');

    // Determine scheme + host.
    if ($domain !== '' && !$isLocal) {
        $scheme = 'https';
        $host   = $domain;
    } else {
        // Fallback to the current request host (works in local dev).
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            ? 'https'
            : 'http';
        $host = (string) ($_SERVER['HTTP_HOST'] ?? ($domain !== '' ? $domain : 'localhost'));
    }

    // Normalize the path: keep query strings intact, just trim a leading slash.
    $path = ltrim($path, '/');
    if ($path === '') {
        return $scheme . '://' . $host . '/';
    }

    return $scheme . '://' . $host . '/' . $path;
}
