<?php

declare(strict_types=1);

/** @var string $activeNav */
$activeNav = $activeNav ?? 'dashboard';
?>
<div class="admin-layout">
    <aside class="admin-sidebar" aria-label="Admin navigation">
        <nav class="admin-sidebar__nav">
            <a href="<?= e(admin_url('dashboard.php')) ?>" class="admin-sidebar__link<?= $activeNav === 'dashboard' ? ' admin-sidebar__link--active' : '' ?>">Dashboard</a>
            <a href="<?= e(admin_url('venues.php')) ?>" class="admin-sidebar__link<?= $activeNav === 'venues' ? ' admin-sidebar__link--active' : '' ?>">Venues</a>
            <span class="admin-sidebar__link admin-sidebar__link--disabled" title="Available in a later phase">Menu items</span>
            <a href="<?= e(admin_url('blog.php')) ?>" class="admin-sidebar__link<?= $activeNav === 'blog' ? ' admin-sidebar__link--active' : '' ?>">Blog</a>
            <a href="<?= e(admin_url('galleries.php')) ?>" class="admin-sidebar__link<?= $activeNav === 'galleries' ? ' admin-sidebar__link--active' : '' ?>">Galleries</a>
            <span class="admin-sidebar__link admin-sidebar__link--disabled" title="Available in a later phase">Settings</span>
        </nav>
    </aside>
    <main class="admin-main">
