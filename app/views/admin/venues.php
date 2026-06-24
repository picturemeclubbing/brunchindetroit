<?php
/** @var array $venues */
/** @var string|null $flashSuccess */
/** @var string|null $flashError */
?>
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Venue Management</h1>
        <p class="admin-page-lead">Add, edit, publish, and feature public venue profiles.</p>
    </div>
    <a class="btn btn--primary" href="<?= e(admin_url('venue-edit.php')) ?>">
        <i class="fa-solid fa-plus"></i> Add Venue
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

<?php if (empty($venues)): ?>
    <div class="empty-state">
        <h3>No venues yet</h3>
        <p>Add your first venue to publish a profile on the public Directory page.</p>
        <p style="margin-top:1rem">
            <a class="btn btn--primary" href="<?= e(admin_url('venue-edit.php')) ?>">
                <i class="fa-solid fa-plus"></i> Add Venue
            </a>
        </p>
    </div>
<?php else: ?>
    <div class="admin-panel">
        <div class="admin-table__wrap admin-venues-table-wrap">
            <table class="admin-table admin-venues-table">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Neighborhood</th>
                        <th scope="col">Price</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="admin-table__actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($venues as $v): ?>
                    <?php
                    $vid      = (int) ($v['id'] ?? 0);
                    $isPub    = !empty($v['is_published']);
                    $isFeat   = !empty($v['is_featured']);
                    $name     = (string) ($v['name'] ?? '(untitled)');
                    $nbhd     = (string) ($v['neighborhood_name'] ?? '');
                    $priceRaw = $v['price_range'] ?? null;
                    $priceStr = ($priceRaw !== null && $priceRaw !== '') ? (string) $priceRaw : '-';
                    $slug     = (string) ($v['slug'] ?? '');
                    ?>
                    <tr class="<?= $isFeat ? 'admin-venue-row--featured' : '' ?>">
                        <th scope="row" class="admin-table__title-cell">
                            <a href="<?= e(admin_url('venue-edit.php?id=' . $vid)) ?>">
                                <?= e($name) ?>
                            </a>
                            <?php if ($slug !== ''): ?>
                                <a href="<?= e(asset_url('venue.php?slug=' . urlencode($slug))) ?>"
                                   target="_blank" rel="noopener noreferrer"
                                   class="admin-table__sublink" title="View public venue page">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> View
                                </a>
                            <?php endif; ?>
                        </th>
                        <td>
                            <?php if ($nbhd !== ''): ?>
                                <?= e($nbhd) ?>
                            <?php else: ?>
                                <span class="admin-table__muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($priceStr) ?></td>
                        <td class="admin-venue-status-cell">
                            <form method="post" action="<?= e(admin_url('venues.php')) ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $vid ?>">
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
                        <td class="admin-menu-icon-actions admin-venue-icon-actions">
                            <a class="admin-icon-action" href="<?= e(admin_url('venue-edit.php?id=' . $vid)) ?>" aria-label="Edit venue">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            <form method="post" action="<?= e(admin_url('venues.php')) ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $vid ?>">
                                <input type="hidden" name="action" value="<?= $isFeat ? 'unfeature' : 'feature' ?>">
                                <button
                                    type="submit"
                                    class="admin-icon-action admin-icon-action--feature<?= $isFeat ? ' admin-icon-action--featured' : '' ?>"
                                    aria-label="<?= $isFeat ? 'Unfeature venue' : 'Feature venue' ?>"
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

        <div class="admin-mobile-card-list admin-venues-mobile-list">
            <?php foreach ($venues as $v): ?>
                <?php
                $vid      = (int) ($v['id'] ?? 0);
                $isPub    = !empty($v['is_published']);
                $isFeat   = !empty($v['is_featured']);
                $name     = (string) ($v['name'] ?? '(untitled)');
                $nbhd     = (string) ($v['neighborhood_name'] ?? '');
                $priceRaw = $v['price_range'] ?? null;
                $priceStr = ($priceRaw !== null && $priceRaw !== '') ? (string) $priceRaw : '-';
                $slug     = (string) ($v['slug'] ?? '');
                ?>
                <article class="admin-mobile-card admin-venue-mobile-card<?= $isFeat ? ' admin-mobile-card--featured' : '' ?>">
                    <div class="admin-mobile-card__main">
                        <h3><?= e($name) ?></h3>
                        <p>
                            <?= e($nbhd !== '' ? $nbhd : 'No neighborhood') ?>
                            · Price <?= e($priceStr) ?>
                        </p>
                        <?php if ($slug !== ''): ?>
                            <p>
                                <a href="<?= e(asset_url('venue.php?slug=' . urlencode($slug))) ?>" target="_blank" rel="noopener noreferrer">
                                    View public venue
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="admin-mobile-card__badges">
                        <form method="post" action="<?= e(admin_url('venues.php')) ?>">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $vid ?>">
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
                        <a class="admin-icon-action" href="<?= e(admin_url('venue-edit.php?id=' . $vid)) ?>" aria-label="Edit venue">
                            <i class="fa-solid fa-pen"></i>
                        </a>

                        <form method="post" action="<?= e(admin_url('venues.php')) ?>">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $vid ?>">
                            <input type="hidden" name="action" value="<?= $isFeat ? 'unfeature' : 'feature' ?>">
                            <button
                                type="submit"
                                class="admin-icon-action admin-icon-action--feature<?= $isFeat ? ' admin-icon-action--featured' : '' ?>"
                                aria-label="<?= $isFeat ? 'Unfeature venue' : 'Feature venue' ?>"
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
