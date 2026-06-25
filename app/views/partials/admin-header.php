<?php

declare(strict_types=1);

/** @var string $pageTitle */
/** @var string|null $metaDescription */
/** @var string|null $activeNav */

$metaDescription = $metaDescription ?? '';
$activeNav = $activeNav ?? 'dashboard';
$adminScript = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));

$adminUser = admin_is_logged_in() ? admin_user() : null;
$adminDisplayName = $adminUser !== null ? (string) ($adminUser['display_name'] ?? 'Site Admin') : 'Site Admin';

$adminNavGroups = [
    [
        'label' => 'Main',
        'items' => [
            [
                'label' => 'Dashboard',
                'url' => admin_url('dashboard.php'),
                'icon' => 'fa-solid fa-gauge-high',
                'active' => $activeNav === 'dashboard',
            ],
        ],
    ],
    [
        'label' => 'Venues & Menus',
        'items' => [
            [
                'label' => 'Venues',
                'url' => admin_url('venues.php'),
                'icon' => 'fa-solid fa-store',
                'active' => $activeNav === 'venues',
            ],
            [
                'label' => 'Neighborhoods',
                'url' => admin_url('neighborhoods.php'),
                'icon' => 'fa-solid fa-map-location-dot',
                'active' => $activeNav === 'neighborhoods',
            ],
            [
                'label' => 'Menu Items',
                'url' => admin_url('menu.php'),
                'icon' => 'fa-solid fa-utensils',
                'active' => $activeNav === 'menu',
            ],
        ],
    ],
    [
        'label' => 'Content',
        'items' => [
            [
                'label' => 'Blog',
                'url' => admin_url('blog.php'),
                'icon' => 'fa-solid fa-newspaper',
                'active' => $activeNav === 'blog' && $adminScript !== 'blog-categories.php',
            ],
            [
                'label' => 'Blog Categories',
                'url' => admin_url('blog-categories.php'),
                'icon' => 'fa-solid fa-tags',
                'active' => $adminScript === 'blog-categories.php',
            ],
            [
                'label' => 'Galleries',
                'url' => admin_url('galleries.php'),
                'icon' => 'fa-solid fa-images',
                'active' => $activeNav === 'galleries',
            ],
            [
                'label' => 'Gallery Ad Wall',
                'url' => admin_url('gallery-adwall.php'),
                'icon' => 'fa-solid fa-rectangle-ad',
                'active' => $activeNav === 'gallery-adwall',
            ],
        ],
    ],
];
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/main.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/admin.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/admin-mobile.css')) ?>">
</head>
<body class="admin-body">
<header class="admin-topbar">
    <div class="container admin-topbar__inner">
        <a href="<?= e(admin_url('dashboard.php')) ?>" class="admin-topbar__brand">
            <?= e(site_name_display()) ?>
            <span class="admin-topbar__label">Admin</span>
        </a>

        <div class="admin-topbar__actions admin-topbar__actions--desktop">
            <a href="<?= e(asset_url('index.php')) ?>" class="admin-topbar__link" target="_blank" rel="noopener">View site</a>
            <?php if (admin_is_logged_in()): ?>
                <a href="<?= e(admin_url('dashboard.php')) ?>" class="admin-topbar__link"><?= e($adminDisplayName) ?></a>
                <a href="<?= e(admin_url('logout.php')) ?>" class="admin-topbar__link admin-topbar__link--muted">Log out</a>
            <?php endif; ?>
        </div>

        <button
            type="button"
            class="admin-nav-toggle"
            aria-controls="admin-mobile-menu"
            aria-expanded="false"
        >
            <span class="admin-nav-toggle__bars" aria-hidden="true"></span>
            <span class="admin-nav-toggle__label">Menu</span>
        </button>
    </div>

    <div class="admin-mobile-backdrop" data-admin-nav-close hidden></div>

    <aside id="admin-mobile-menu" class="admin-mobile-drawer" aria-label="Admin mobile navigation" hidden>
        <div class="admin-mobile-drawer__header">
            <div>
                <a href="<?= e(admin_url('dashboard.php')) ?>" class="admin-mobile-drawer__brand">
                    <?= e(site_name_display()) ?>
                </a>
                <p>Admin area</p>
            </div>

            <button type="button" class="admin-mobile-drawer__close" data-admin-nav-close aria-label="Close admin menu">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </div>

        <div class="admin-mobile-drawer__quick">
            <a href="<?= e(asset_url('index.php')) ?>" target="_blank" rel="noopener">
                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                View site
            </a>
            <a href="<?= e(admin_url('dashboard.php')) ?>">
                <i class="fa-solid fa-user-gear" aria-hidden="true"></i>
                Site Admin
            </a>
            <?php if (admin_is_logged_in()): ?>
                <a href="<?= e(admin_url('logout.php')) ?>">
                    <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                    Log out
                </a>
            <?php endif; ?>
        </div>

        <nav class="admin-mobile-nav">
            <?php foreach ($adminNavGroups as $group): ?>
                <?php
                $groupActive = false;
                foreach ($group['items'] as $item) {
                    if (!empty($item['active'])) {
                        $groupActive = true;
                        break;
                    }
                }
                ?>
                <details class="admin-mobile-nav__group" <?= $groupActive ? 'open' : '' ?>>
                    <summary>
                        <span><?= e((string) $group['label']) ?></span>
                        <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                    </summary>

                    <div class="admin-mobile-nav__links">
                        <?php foreach ($group['items'] as $item): ?>
                            <a
                                href="<?= e((string) $item['url']) ?>"
                                class="admin-mobile-nav__link<?= !empty($item['active']) ? ' admin-mobile-nav__link--active' : '' ?>"
                            >
                                <i class="<?= e((string) $item['icon']) ?>" aria-hidden="true"></i>
                                <span><?= e((string) $item['label']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endforeach; ?>

            <div class="admin-mobile-nav__disabled">
                <i class="fa-solid fa-gear" aria-hidden="true"></i>
                <span>Settings</span>
                <small>Later phase</small>
            </div>
        </nav>
    </aside>
</header>
