<?php
/** @var array $galleries */
/** @var string|null $flashSuccess */
/** @var string|null $flashError */
?>
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Gallery Management</h1>
        <p class="admin-page-lead">Add, edit, publish, and feature public gallery cards.</p>
    </div>
    <a class="btn btn--primary" href="<?= e(admin_url('gallery-edit.php')) ?>">
        <i class="fa-solid fa-plus"></i> Add Gallery
    </a>
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
        <p>Add your first gallery to publish event photos on the public Gallery page.</p>
        <p style="margin-top:1rem">
            <a class="btn btn--primary" href="<?= e(admin_url('gallery-edit.php')) ?>">
                <i class="fa-solid fa-plus"></i> Add Gallery
            </a>
        </p>
    </div>
<?php else: ?>
    <div class="admin-panel">
        <div class="admin-table__wrap">
            <table class="admin-table">
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
                <?php foreach ($galleries as $g):
                    $gid        = (int) ($g['id'] ?? 0);
                    $isPub      = !empty($g['is_published']);
                    $isFeat     = !empty($g['is_featured']);
                    $venueName  = (string) ($g['venue_name'] ?? '');
                    $locLabel   = (string) ($g['location_label'] ?? '');
                    $dateRaw    = $g['event_date'] ?? null;
                    $dateStr    = ($dateRaw !== null && $dateRaw !== '')
                        ? date('M j, Y', strtotime((string) $dateRaw))
                        : '—';
                    ?>
                    <tr>
                        <th scope="row" class="admin-table__title-cell">
                            <a href="<?= e(admin_url('gallery-edit.php?id=' . $gid)) ?>">
                                <?= e((string) ($g['title'] ?? '(untitled)')) ?>
                            </a>
                            <?php if (!empty($g['gallery_url'])): ?>
                                <a href="<?= e((string) $g['gallery_url']) ?>" target="_blank" rel="noopener noreferrer"
                                   class="admin-table__sublink" title="Open gallery URL">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> View
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
                                <span class="admin-table__muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($dateStr) ?></td>
                        <td class="admin-table__badges">
                            <?php if ($isPub): ?>
                                <span class="badge badge--success">Published</span>
                            <?php else: ?>
                                <span class="badge badge--draft">Draft</span>
                            <?php endif; ?>
                            <?php if ($isFeat): ?>
                                <span class="badge badge--accent">Featured</span>
                            <?php endif; ?>
                        </td>
                        <td class="admin-table__actions">
                            <a class="btn btn--outline btn--sm"
                               href="<?= e(admin_url('gallery-edit.php?id=' . $gid)) ?>">
                                <i class="fa-solid fa-pen"></i> Edit
                            </a>

                            <form method="post" action="<?= e(admin_url('galleries.php')) ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $gid ?>">
                                <?php if ($isPub): ?>
                                    <input type="hidden" name="action" value="unpublish">
                                    <button type="submit" class="btn btn--outline btn--sm">
                                        <i class="fa-solid fa-eye-slash"></i> Unpublish
                                    </button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="publish">
                                    <button type="submit" class="btn btn--outline btn--sm">
                                        <i class="fa-solid fa-eye"></i> Publish
                                    </button>
                                <?php endif; ?>
                            </form>

                            <form method="post" action="<?= e(admin_url('galleries.php')) ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $gid ?>">
                                <?php if ($isFeat): ?>
                                    <input type="hidden" name="action" value="unfeature">
                                    <button type="submit" class="btn btn--outline btn--sm">
                                        <i class="fa-solid fa-star"></i> Unfeature
                                    </button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="feature">
                                    <button type="submit" class="btn btn--outline btn--sm">
                                        <i class="fa-regular fa-star"></i> Feature
                                    </button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>