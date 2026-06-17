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
        <div class="admin-table__wrap">
            <table class="admin-table">
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
                <?php foreach ($venues as $v):
                    $vid      = (int) ($v['id'] ?? 0);
                    $isPub    = !empty($v['is_published']);
                    $isFeat   = !empty($v['is_featured']);
                    $name     = (string) ($v['name'] ?? '(untitled)');
                    $nbhd     = (string) ($v['neighborhood_name'] ?? '');
                    $priceRaw = $v['price_range'] ?? null;
                    $priceStr = ($priceRaw !== null && $priceRaw !== '') ? (string) $priceRaw : '—';
                    ?>
                    <tr>
                        <th scope="row" class="admin-table__title-cell">
                            <a href="<?= e(admin_url('venue-edit.php?id=' . $vid)) ?>">
                                <?= e($name) ?>
                            </a>
                            <?php if (!empty($v['slug'])): ?>
                                <a href="<?= e(asset_url('venue.php?slug=' . urlencode((string) $v['slug']))) ?>"
                                   target="_blank" rel="noopener noreferrer"
                                   class="admin-table__sublink" title="View public venue page">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> View
                                </a>
                            <?php endif; ?>
                        </th>
                        <td>
                            <?php if ($nbhd !== ''): ?>
                                <div><?= e($nbhd) ?></div>
                            <?php else: ?>
                                <span class="admin-table__muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($priceStr) ?></td>
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
                               href="<?= e(admin_url('venue-edit.php?id=' . $vid)) ?>">
                                <i class="fa-solid fa-pen"></i> Edit
                            </a>

                            <form method="post" action="<?= e(admin_url('venues.php')) ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $vid ?>">
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

                            <form method="post" action="<?= e(admin_url('venues.php')) ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $vid ?>">
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