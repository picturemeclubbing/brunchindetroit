<?php

declare(strict_types=1);

/** @var array<string, int> $stats */
?>
        <div class="admin-page-header">
            <h1 class="admin-page-title">Dashboard</h1>
            <p class="admin-page-lead">Overview of content on <?= e(site_domain()) ?>. Management tools arrive in Phase 5.</p>
        </div>

        <div class="admin-stats">
            <div class="admin-stat-card">
                <div class="admin-stat-card__icon" aria-hidden="true"><i class="fas fa-store"></i></div>
                <div>
                    <p class="admin-stat-card__label">Venues</p>
                    <p class="admin-stat-card__value"><?= (int) $stats['venues'] ?></p>
                    <p class="admin-stat-card__meta"><?= (int) $stats['venues_published'] ?> published</p>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-card__icon" aria-hidden="true"><i class="fas fa-utensils"></i></div>
                <div>
                    <p class="admin-stat-card__label">Menu items</p>
                    <p class="admin-stat-card__value"><?= (int) $stats['menu_items'] ?></p>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-card__icon" aria-hidden="true"><i class="fas fa-newspaper"></i></div>
                <div>
                    <p class="admin-stat-card__label">Blog posts</p>
                    <p class="admin-stat-card__value"><?= (int) $stats['blog_posts'] ?></p>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-card__icon" aria-hidden="true"><i class="fas fa-images"></i></div>
                <div>
                    <p class="admin-stat-card__label">Galleries</p>
                    <p class="admin-stat-card__value"><?= (int) $stats['galleries'] ?></p>
                </div>
            </div>
        </div>

        <div class="admin-panel">
            <h2 class="admin-panel__title">Next steps</h2>
            <ul class="admin-checklist">
                <li>Import <code>database/schema.sql</code> and <code>database/seed.sql</code> if you have not already.</li>
                <li>Change the default admin password before deploying to production.</li>
                <li>Phase 5 will add venue, menu, blog, and gallery managers from this dashboard.</li>
            </ul>
        </div>
