<?php

declare(strict_types=1);

function admin_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $config = app_config();
    $name = (string) ($config['session']['name'] ?? 'brunch_admin_session');
    session_name($name);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function admin_is_logged_in(): bool
{
    admin_session_start();

    return !empty($_SESSION['admin_id']) && is_int($_SESSION['admin_id']);
}

function admin_user(): ?array
{
    if (!admin_is_logged_in()) {
        return null;
    }

    return [
        'id' => (int) $_SESSION['admin_id'],
        'email' => (string) ($_SESSION['admin_email'] ?? ''),
        'display_name' => (string) ($_SESSION['admin_display_name'] ?? ''),
    ];
}

function admin_login(array $admin): void
{
    admin_session_start();
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_email'] = (string) $admin['email'];
    $_SESSION['admin_display_name'] = (string) $admin['display_name'];
}

function admin_logout(): void
{
    admin_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}

function admin_require_login(): void
{
    if (admin_is_logged_in()) {
        return;
    }

    $return = $_SERVER['REQUEST_URI'] ?? admin_url('dashboard.php');
    header('Location: ' . admin_url('login.php') . '?return=' . rawurlencode($return));
    exit;
}

function csrf_token(): string
{
    admin_session_start();
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['_csrf'];
}

function csrf_verify(?string $token): bool
{
    admin_session_start();
    if ($token === null || $token === '' || empty($_SESSION['_csrf'])) {
        return false;
    }

    return hash_equals((string) $_SESSION['_csrf'], $token);
}

function flash_set(string $key, string $message): void
{
    admin_session_start();
    $_SESSION['_flash'][$key] = $message;
}

function flash_get(string $key): ?string
{
    admin_session_start();
    if (!isset($_SESSION['_flash'][$key])) {
        return null;
    }
    $message = (string) $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);

    return $message;
}
