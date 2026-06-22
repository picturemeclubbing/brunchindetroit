<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $neighborhoods
 * @var array<string, string> $errors
 * @var array<string, mixed> $form
 */

$isEdit = (int) ($form['id'] ?? 0) > 0;
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Neighborhoods</h1>
        <p class="admin-page-lead">Manage Detroit neighborhood labels used by venue profiles, gallery filters, and venue organization.</p>
    </div>
</div>

<?php if (!empty($errors['form'])): ?>
    <div class="admin-alert admin-alert--error"><?= e($errors['form']) ?></div>
<?php endif; ?>

<section class="admin-card">
    <div class="admin-card__header">
        <div>
            <h2 class="admin-card__title"><?= $isEdit ? 'Edit Neighborhood' : 'Add Neighborhood' ?></h2>
            <p class="admin-card__subtitle">Slugs are used internally for future neighborhood URLs and filters.</p>
        </div>
    </div>

    <form class="admin-form admin-category-form" method="post" action="<?= e(admin_url('neighborhoods.php')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="save">

        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= e((string) ($form['id'] ?? 0)) ?>">
        <?php endif; ?>

        <div class="admin-form__grid">
            <div class="admin-form__field">
                <label class="admin-form__label" for="name">Name</label>
                <input
                    class="admin-form__input"
                    type="text"
                    id="name"
                    name="name"
                    value="<?= e((string) ($form['name'] ?? '')) ?>"
                    maxlength="120"
                    required
                >
                <?php if (!empty($errors['name'])): ?>
                    <span class="admin-form__error"><?= e($errors['name']) ?></span>
                <?php endif; ?>
            </div>

            <div class="admin-form__field">
                <label class="admin-form__label" for="slug">Slug</label>
                <input
                    class="admin-form__input"
                    type="text"
                    id="slug"
                    name="slug"
                    value="<?= e((string) ($form['slug'] ?? '')) ?>"
                    maxlength="120"
                    placeholder="leave blank to auto-generate"
                >
                <?php if (!empty($errors['slug'])): ?>
                    <span class="admin-form__error"><?= e($errors['slug']) ?></span>
                <?php endif; ?>
            </div>

            <div class="admin-form__field">
                <label class="admin-form__label" for="sort_order">Sort Order</label>
                <input
                    class="admin-form__input"
                    type="number"
                    id="sort_order"
                    name="sort_order"
                    value="<?= e((string) ($form['sort_order'] ?? '0')) ?>"
                    step="1"
                >
                <span class="admin-form__hint">Lower numbers appear first in dropdowns.</span>
                <?php if (!empty($errors['sort_order'])): ?>
                    <span class="admin-form__error"><?= e($errors['sort_order']) ?></span>
                <?php endif; ?>
            </div>

            <div class="admin-form__field admin-form__field--checkbox">
                <label class="admin-check">
                    <input type="checkbox" name="is_active" value="1" <?= (string) ($form['is_active'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <span><strong>Active</strong> - show this neighborhood in venue dropdowns.</span>
                </label>
            </div>
        </div>

        <div class="admin-form__actions">
            <button type="submit" class="btn btn--primary">
                <?= $isEdit ? 'Save Neighborhood' : 'Add Neighborhood' ?>
            </button>

            <?php if ($isEdit): ?>
                <a class="btn btn--outline" href="<?= e(admin_url('neighborhoods.php')) ?>">Cancel Edit</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="admin-card">
    <div class="admin-card__header">
        <div>
            <h2 class="admin-card__title">Current Neighborhoods</h2>
            <p class="admin-card__subtitle">Delete is only available when no venues are assigned.</p>
        </div>
    </div>

    <?php if (empty($neighborhoods)): ?>
        <p class="admin-empty-state">No neighborhoods have been added yet.</p>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th scope="col">Neighborhood</th>
                        <th scope="col">Slug</th>
                        <th scope="col">Sort</th>
                        <th scope="col">Venues</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="admin-table__actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($neighborhoods as $neighborhood): ?>
                        <?php
                        $neighborhoodId = (int) ($neighborhood['id'] ?? 0);
                        $venueCount = (int) ($neighborhood['venue_count'] ?? 0);
                        $isActive = !empty($neighborhood['is_active']);
                        ?>
                        <tr>
                            <td class="admin-table__title-cell">
                                <strong><?= e((string) ($neighborhood['name'] ?? 'Untitled Neighborhood')) ?></strong>
                            </td>
                            <td><code><?= e((string) ($neighborhood['slug'] ?? '')) ?></code></td>
                            <td><?= e((string) ($neighborhood['sort_order'] ?? '0')) ?></td>
                            <td>
                                <span class="badge <?= $venueCount > 0 ? 'badge--success' : 'badge--draft' ?>">
                                    <?= $venueCount ?> <?= $venueCount === 1 ? 'venue' : 'venues' ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $isActive ? 'badge--success' : 'badge--draft' ?>">
                                    <?= $isActive ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="admin-table__actions">
                                <a class="btn btn--sm btn--outline" href="<?= e(admin_url('neighborhoods.php?edit=' . $neighborhoodId)) ?>">
                                    Edit
                                </a>

                                <?php if ($venueCount === 0): ?>
                                    <form method="post" action="<?= e(admin_url('neighborhoods.php')) ?>" onsubmit="return confirm('Delete this neighborhood?');">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= e((string) $neighborhoodId) ?>">
                                        <button type="submit" class="btn btn--sm btn--danger">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <button type="button" class="btn btn--sm btn--outline" disabled title="Neighborhoods with venues cannot be deleted.">
                                        Locked
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>