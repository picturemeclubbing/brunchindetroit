<?php
declare(strict_types=1);

/**
 * Frontend admin quick-edit bar.
 *
 * Expected variables:
 * @var array<int, array{label:string,url:string,icon?:string}> $frontendAdminActions
 * @var string|null $frontendAdminTitle
 *
 * Controllers must only set $frontendAdminActions for logged-in admins.
 * This partial intentionally does not call admin_is_logged_in() because views
 * may be included after headers have already been prepared.
 */

$frontendAdminActions = isset($frontendAdminActions) && is_array($frontendAdminActions)
    ? $frontendAdminActions
    : [];

$frontendAdminTitle = isset($frontendAdminTitle) && trim((string) $frontendAdminTitle) !== ''
    ? (string) $frontendAdminTitle
    : 'Admin tools';

if ($frontendAdminActions === []) {
    return;
}
?>

<div class="frontend-admin-bar" role="region" aria-label="Admin quick edit tools">
    <div class="container">
        <div class="frontend-admin-bar__inner">
            <span class="frontend-admin-bar__title">
                <i class="fas fa-screwdriver-wrench" aria-hidden="true"></i>
                <?= e($frontendAdminTitle) ?>
            </span>

            <div class="frontend-admin-bar__actions">
                <?php foreach ($frontendAdminActions as $action): ?>
                    <?php
                    $label = trim((string) ($action['label'] ?? ''));
                    $url   = trim((string) ($action['url'] ?? ''));
                    $icon  = trim((string) ($action['icon'] ?? 'fas fa-pen'));

                    if ($label === '' || $url === '') {
                        continue;
                    }
                    ?>
                    <a class="frontend-admin-bar__link" href="<?= e($url) ?>">
                        <i class="<?= e($icon) ?>" aria-hidden="true"></i>
                        <?= e($label) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
