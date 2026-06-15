<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once APP_ROOT . '/models/Admin.php';

admin_require_login();

$pageTitle = 'Admin Dashboard';
$activeNav = 'dashboard';

try {
    $stats = DashboardStats::counts();
} catch (PDOException $e) {
    if (!empty(app_config()['debug'])) {
        throw $e;
    }
    $stats = [
        'venues' => 0,
        'venues_published' => 0,
        'blog_posts' => 0,
        'galleries' => 0,
        'menu_items' => 0,
    ];
}

require APP_ROOT . '/views/partials/admin-header.php';
require APP_ROOT . '/views/partials/admin-sidebar.php';
require APP_ROOT . '/views/admin/dashboard.php';
require APP_ROOT . '/views/partials/admin-footer.php';
