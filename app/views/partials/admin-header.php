<?php

declare(strict_types=1);

/** @var string $pageTitle */
/** @var string|null $metaDescription */
$metaDescription = $metaDescription ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(page_title($pageTitle)) ?></title>
    <?php if ($metaDescription !== ''): ?>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <?php endif; ?>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/main.css')) ?>">
</head>
<body class="admin-body">
<header class="admin-topbar">
    <div class="container admin-topbar__inner">
        <a href="<?= e(admin_url('dashboard.php')) ?>" class="admin-topbar__brand"><?= e(site_name_display()) ?> <span class="admin-topbar__label">Admin</span></a>
        <div class="admin-topbar__actions">
            <a href="<?= e(asset_url('index.php')) ?>" class="admin-topbar__link" target="_blank" rel="noopener">View site</a>
            <?php if (admin_is_logged_in()): ?>
                <?php $user = admin_user(); ?>
                <span class="admin-topbar__user"><?= e($user['display_name'] ?? '') ?></span>
                <a href="<?= e(admin_url('logout.php')) ?>" class="admin-topbar__link admin-topbar__link--muted">Log out</a>
            <?php endif; ?>
        </div>
    </div>
</header>
