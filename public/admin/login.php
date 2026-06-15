<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once APP_ROOT . '/models/Admin.php';

if (admin_is_logged_in()) {
    redirect(admin_url('dashboard.php'));
}

$error = null;
$email = '';
$returnUrl = (string) ($_GET['return'] ?? admin_url('dashboard.php'));
if (!str_starts_with($returnUrl, '/') || str_contains($returnUrl, '//')) {
    $returnUrl = admin_url('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $returnUrl = (string) ($_POST['return'] ?? admin_url('dashboard.php'));
    if (!str_starts_with($returnUrl, '/') || str_contains($returnUrl, '//')) {
        $returnUrl = admin_url('dashboard.php');
    }
    $email = trim((string) ($_POST['email'] ?? ''));

    if (!csrf_verify((string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Invalid session. Please try again.';
    } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } else {
        try {
            $admin = Admin::findByEmail($email);
            $password = (string) ($_POST['password'] ?? '');

            if ($admin === null || !Admin::verifyPassword($password, (string) $admin['password_hash'])) {
                $error = 'Invalid email or password.';
            } else {
                admin_login([
                    'id' => (int) $admin['id'],
                    'email' => (string) $admin['email'],
                    'display_name' => (string) $admin['display_name'],
                ]);
                Admin::updateLastLogin((int) $admin['id']);
                redirect($returnUrl);
            }
        } catch (PDOException $e) {
            if (!empty(app_config()['debug'])) {
                $error = 'Database error: ' . $e->getMessage()
                    . ' — Start MySQL, import database/schema.sql and seed.sql, then check app/config/config.php.';
            } else {
                $error = 'Unable to sign in right now. Please try again later.';
            }
        }
    }
}

require APP_ROOT . '/views/admin/login.php';
