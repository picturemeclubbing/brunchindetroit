<?php

declare(strict_types=1);

/** @var string $activeNav */
$activeNav = $activeNav ?? 'dashboard';
?>
<div class="admin-layout">
    <aside class="admin-sidebar" aria-label="Admin navigation">
        <nav class="admin-sidebar__nav">
            <a href="<?= e(admin_url('dashboard.php')) ?>" class="admin-sidebar__link<?= $activeNav === 'dashboard' ? ' admin-sidebar__link--active' : '' ?>">Dashboard</a>
            <span class="admin-sidebar__link admin-sidebar__link--disabled" title="Available in a later phase">Venues</span>
            <span class="admin-sidebar__link admin-sidebar__link--disabled" title="Available in a later phase">Menu items</span>
            <span class="admin-sidebar__link admin-sidebar__link--disabled" title="Available in a later phase">Blog</span>
            <span class="admin-sidebar__link admin-sidebar__link--disabled" title="Available in a later phase">Galleries</span>
            <span class="admin-sidebar__link admin-sidebar__link--disabled" title="Available in a later phase">Settings</span>
        </nav>
    </aside>
    <main class="admin-main">
