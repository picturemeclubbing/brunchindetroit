<?php
/** @var array $galleries */
/** @var string|null $flashSuccess */
/** @var string|null $flashError */
?>
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Gallery Management</h1>
        <p class="admin-page-lead">Add, edit, publish, and feature gallery landing pages.</p>
    </div>
    <div class="admin-page-header__actions">
        <a class="btn btn--outline" href="<?= e(admin_url('gallery-adwall.php')) ?>">
            <i class="fa-solid fa-rectangle-ad"></i> Ad Wall Settings
        </a>
        <a class="btn btn--primary" href="<?= e(admin_url('gallery-edit.php')) ?>">
            <i class="fa-solid fa-plus"></i> Add Gallery
        </a>
    </div>
</div>

<?php if ($flashSuccess !== null): ?>
    <div class="alert alert--success" role="alert">
        <i class="fa-solid fa-circle-check"></i>
        <?= e($flashSuccess) ?>
    </div>
<?php endif; ?>

<?php if ($flashError !== null): ?>
    <div class="alert alert--danger" role="alert">
        <i class="fa-solid fa-circle-exclamation"></i>
        <?= e($flashError) ?>
    </div>
<?php endif; ?>

<?php if (empty($galleries)): ?>
    <div class="empty-state">
        <h3>No galleries yet</h3>
        <p>Add your first gallery to publish an event photo landing page.</p>
        <p style="margin-top:1rem">
            <a class="btn btn--primary" href="<?= e(admin_url('gallery-edit.php')) ?>">
                <i class="fa-solid fa-plus"></i> Add Gallery
            </a>
        </p>
    </div>
<?php else: ?>
    <div class="admin-panel">
        <div class="admin-table__wrap admin-galleries-table-wrap">
            <table class="admin-table admin-galleries-table">
                <thead>
                    <tr>
                        <th scope="col">Title</th>
                        <th scope="col">Venue / Location</th>
                        <th scope="col">Event Date</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="admin-table__actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($galleries as $g): ?>
                    <?php
                    $gid        = (int) ($g['id'] ?? 0);
                    $isPub      = !empty($g['is_published']);
                    $isFeat     = !empty($g['is_featured']);
                    $title      = (string) ($g['title'] ?? '(untitled)');
                    $slug       = (string) ($g['slug'] ?? '');
                    $venueName  = (string) ($g['venue_name'] ?? '');
                    $locLabel   = (string) ($g['location_label'] ?? '');
                    $dateRaw    = $g['event_date'] ?? null;
                    $dateStr    = ($dateRaw !== null && $dateRaw !== '')
                        ? date('M j, Y', strtotime((string) $dateRaw))
                        : '-';
                    $landingUrl = $slug !== '' ? asset_url('gallery-view.php?slug=' . urlencode($slug)) : '';
                    ?>
                    <tr class="<?= $isFeat ? 'admin-gallery-row--featured' : '' ?>">
                        <th scope="row" class="admin-table__title-cell">
                            <a href="<?= e(admin_url('gallery-edit.php?id=' . $gid)) ?>">
                                <?= e($title) ?>
                            </a>
                            <?php if ($landingUrl !== ''): ?>
                                <a href="<?= e($landingUrl) ?>"
                                   target="_blank" rel="noopener noreferrer"
                                   class="admin-table__sublink" title="View gallery landing page">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> View Landing Page
                                </a>
                            <?php endif; ?>
                        </th>
                        <td>
                            <?php if ($venueName !== ''): ?>
                                <div><?= e($venueName) ?></div>
                            <?php endif; ?>
                            <?php if ($locLabel !== ''): ?>
                                <div class="admin-table__muted"><?= e($locLabel) ?></div>
                            <?php endif; ?>
                            <?php if ($venueName === '' && $locLabel === ''): ?>
                                <span class="admin-table__muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($dateStr) ?></td>
                        <td class="admin-gallery-status-cell">
                            <form method="post" action="<?= e(admin_url('galleries.php')) ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $gid ?>">
                                <input type="hidden" name="action" value="<?= $isPub ? 'unpublish' : 'publish' ?>">
                                <?php if ($isPub): ?>
                                    <button type="submit" class="admin-status-toggle admin-status-toggle--published" title="Click to unpublish">
                                        Published
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="admin-status-toggle admin-status-toggle--draft" title="Click to publish">
                                        Draft
                                    </button>
                                <?php endif; ?>
                            </form>
                        </td>
                        <td class="admin-menu-icon-actions admin-gallery-icon-actions">
                            <a class="admin-icon-action" href="<?= e(admin_url('gallery-edit.php?id=' . $gid)) ?>" aria-label="Edit gallery">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            <form method="post" action="<?= e(admin_url('galleries.php')) ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $gid ?>">
                                <input type="hidden" name="action" value="<?= $isFeat ? 'unfeature' : 'feature' ?>">
                                <button
                                    type="submit"
                                    class="admin-icon-action admin-icon-action--feature<?= $isFeat ? ' admin-icon-action--featured' : '' ?>"
                                    aria-label="<?= $isFeat ? 'Unfeature gallery' : 'Feature gallery' ?>"
                                    title="<?= $isFeat ? 'Unfeature' : 'Feature' ?>"
                                >
                                    <i class="<?= $isFeat ? 'fa-solid' : 'fa-regular' ?> fa-star"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="admin-mobile-card-list admin-galleries-mobile-list">
            <?php foreach ($galleries as $g): ?>
                <?php
                $gid        = (int) ($g['id'] ?? 0);
                $isPub      = !empty($g['is_published']);
                $isFeat     = !empty($g['is_featured']);
                $title      = (string) ($g['title'] ?? '(untitled)');
                $slug       = (string) ($g['slug'] ?? '');
                $venueName  = (string) ($g['venue_name'] ?? '');
                $locLabel   = (string) ($g['location_label'] ?? '');
                $dateRaw    = $g['event_date'] ?? null;
                $dateStr    = ($dateRaw !== null && $dateRaw !== '')
                    ? date('M j, Y', strtotime((string) $dateRaw))
                    : 'No date';
                $placeLabel = $venueName !== '' ? $venueName : ($locLabel !== '' ? $locLabel : 'No venue/location');
                $landingUrl = $slug !== '' ? asset_url('gallery-view.php?slug=' . urlencode($slug)) : '';
                ?>
                <article class="admin-mobile-card admin-gallery-mobile-card<?= $isFeat ? ' admin-mobile-card--featured' : '' ?>">
                    <div class="admin-mobile-card__main">
                        <h3><?= e($title) ?></h3>
                        <p><?= e($placeLabel) ?> Â· <?= e($dateStr) ?></p>
                        <?php if ($landingUrl !== ''): ?>
                            <p>
                                <a href="<?= e($landingUrl) ?>" target="_blank" rel="noopener noreferrer">
                                    View landing page
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="admin-mobile-card__badges">
                        <form method="post" action="<?= e(admin_url('galleries.php')) ?>">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $gid ?>">
                            <input type="hidden" name="action" value="<?= $isPub ? 'unpublish' : 'publish' ?>">
                            <?php if ($isPub): ?>
                                <button type="submit" class="admin-status-toggle admin-status-toggle--published" title="Click to unpublish">
                                    Published
                                </button>
                            <?php else: ?>
                                <button type="submit" class="admin-status-toggle admin-status-toggle--draft" title="Click to publish">
                                    Draft
                                </button>
                            <?php endif; ?>
                        </form>

                        <?php if ($isFeat): ?>
                            <span class="badge badge--accent">Featured</span>
                        <?php endif; ?>
                    </div>

                    <div class="admin-mobile-card__actions">
                        <a class="admin-icon-action" href="<?= e(admin_url('gallery-edit.php?id=' . $gid)) ?>" aria-label="Edit gallery">
                            <i class="fa-solid fa-pen"></i>
                        </a>

                        <form method="post" action="<?= e(admin_url('galleries.php')) ?>">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $gid ?>">
                            <input type="hidden" name="action" value="<?= $isFeat ? 'unfeature' : 'feature' ?>">
                            <button
                                type="submit"
                                class="admin-icon-action admin-icon-action--feature<?= $isFeat ? ' admin-icon-action--featured' : '' ?>"
                                aria-label="<?= $isFeat ? 'Unfeature gallery' : 'Feature gallery' ?>"
                                title="<?= $isFeat ? 'Unfeature' : 'Feature' ?>"
                            >
                                <i class="<?= $isFeat ? 'fa-solid' : 'fa-regular' ?> fa-star"></i>
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
