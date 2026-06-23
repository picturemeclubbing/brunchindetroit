<?php
/**
 * @var array<int, array<string, mixed>> $venues
 * @var array<string, mixed>|null $selectedVenue
 * @var int $venueId
 * @var array<int, array<string, mixed>> $categories
 * @var array<int, array<string, mixed>> $items
 * @var array<string, mixed>|null $editCategory
 * @var array<string, mixed>|null $editItem
 * @var string|null $flashSuccess
 * @var string|null $flashError
 */

$categoryForm = [
    'id' => $editCategory !== null ? (int) $editCategory['id'] : 0,
    'name' => $editCategory !== null ? (string) $editCategory['name'] : '',
    'sort_order' => $editCategory !== null ? (string) (int) $editCategory['sort_order'] : '0',
];

$itemForm = [
    'id' => $editItem !== null ? (int) $editItem['id'] : 0,
    'category_id' => $editItem !== null && $editItem['category_id'] !== null ? (string) (int) $editItem['category_id'] : '',
    'name' => $editItem !== null ? (string) $editItem['name'] : '',
    'description' => $editItem !== null ? (string) ($editItem['description'] ?? '') : '',
    'price' => $editItem !== null && $editItem['price'] !== null ? (string) $editItem['price'] : '',
    'sort_order' => $editItem !== null ? (string) (int) $editItem['sort_order'] : '0',
    'is_published' => $editItem !== null ? !empty($editItem['is_published']) : true,
];
?>
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Menu Management</h1>
        <p class="admin-page-lead">
            Manage brunch menu categories and items by venue.
        </p>
    </div>
    <?php if ($selectedVenue !== null && !empty($selectedVenue['slug'])): ?>
        <a class="btn btn--outline" href="<?= e(asset_url('venue.php?slug=' . urlencode((string) $selectedVenue['slug']))) ?>" target="_blank" rel="noopener noreferrer">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> View Venue
        </a>
    <?php endif; ?>
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

<div class="admin-panel">
    <form class="admin-form" method="get" action="<?= e(admin_url('menu.php')) ?>">
        <div class="admin-form__grid">
            <div class="admin-form__field admin-form__field--full">
                <label class="form-label" for="venue_id">Choose Venue</label>
                <select id="venue_id" name="venue_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Select a venue</option>
                    <?php foreach ($venues as $venue): ?>
                        <?php $optionId = (int) ($venue['id'] ?? 0); ?>
                        <option value="<?= $optionId ?>" <?= $optionId === $venueId ? 'selected' : '' ?>>
                            <?= e((string) ($venue['name'] ?? 'Untitled venue')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="admin-form__hint">Select a venue to manage its menu.</span>
            </div>
        </div>
        <div class="admin-form__actions">
            <button type="submit" class="btn btn--primary">Load Menu</button>
        </div>
    </form>
</div>

<?php if ($selectedVenue !== null): ?>
    <div class="admin-page-header" style="margin-top: 1.5rem;">
        <div>
            <h2 class="admin-page-title" style="font-size:1.35rem;"><?= e((string) $selectedVenue['name']) ?></h2>
            <p class="admin-page-lead">Edit categories first, then attach items to categories.</p>
        </div>
    </div>

    <div class="admin-panel" style="margin-bottom: 1.5rem;">
        <h3 class="admin-panel__title"><?= $categoryForm['id'] > 0 ? 'Edit Category' : 'Add Category' ?></h3>

        <form class="admin-form" method="post" action="<?= e(admin_url('menu.php')) ?>">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="save_category">
            <input type="hidden" name="venue_id" value="<?= (int) $venueId ?>">
            <?php if ($categoryForm['id'] > 0): ?>
                <input type="hidden" name="category_id" value="<?= (int) $categoryForm['id'] ?>">
            <?php endif; ?>

            <div class="admin-form__grid">
                <div class="admin-form__field">
                    <label class="form-label" for="category_name">Category Name</label>
                    <input type="text" id="category_name" name="category_name" class="form-control" maxlength="160" required value="<?= e($categoryForm['name']) ?>">
                </div>

                <div class="admin-form__field">
                    <label class="form-label" for="category_sort_order">Sort Order</label>
                    <input type="number" id="category_sort_order" name="category_sort_order" class="form-control" step="1" value="<?= e($categoryForm['sort_order']) ?>">
                </div>
            </div>

            <div class="admin-form__actions">
                <button type="submit" class="btn btn--primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <?= $categoryForm['id'] > 0 ? 'Update Category' : 'Add Category' ?>
                </button>
                <?php if ($categoryForm['id'] > 0): ?>
                    <a class="btn btn--outline" href="<?= e(admin_url('menu.php?venue_id=' . (int) $venueId)) ?>">Cancel Edit</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($categories === []): ?>
            <div class="empty-state" style="margin-top: 1rem;">
                <h3>No categories yet</h3>
                <p>Add a category such as Brunch Plates, Sides, Drinks, or Specials.</p>
            </div>
        <?php else: ?>
            <div class="admin-table__wrap" style="margin-top: 1rem;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Category</th>
                            <th scope="col">Sort</th>
                            <th scope="col">Items</th>
                            <th scope="col" class="admin-table__actions-col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($categories as $category): ?>
                        <?php
                        $categoryId = (int) ($category['id'] ?? 0);
                        $itemCount = (int) ($category['item_count'] ?? 0);
                        ?>
                        <tr>
                            <th scope="row" class="admin-table__title-cell"><?= e((string) ($category['name'] ?? '')) ?></th>
                            <td><?= (int) ($category['sort_order'] ?? 0) ?></td>
                            <td><?= $itemCount ?></td>
                            <td class="admin-table__actions">
                                <a class="btn btn--outline btn--sm" href="<?= e(admin_url('menu.php?venue_id=' . (int) $venueId . '&category_id=' . $categoryId)) ?>">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <?php if ($itemCount === 0): ?>
                                    <form method="post" action="<?= e(admin_url('menu.php')) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete_category">
                                        <input type="hidden" name="venue_id" value="<?= (int) $venueId ?>">
                                        <input type="hidden" name="category_id" value="<?= $categoryId ?>">
                                        <button type="submit" class="btn btn--outline btn--sm">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="admin-panel">
        <h3 class="admin-panel__title"><?= $itemForm['id'] > 0 ? 'Edit Menu Item' : 'Add Menu Item' ?></h3>

        <form class="admin-form" method="post" action="<?= e(admin_url('menu.php')) ?>">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="save_item">
            <input type="hidden" name="venue_id" value="<?= (int) $venueId ?>">
            <?php if ($itemForm['id'] > 0): ?>
                <input type="hidden" name="item_id" value="<?= (int) $itemForm['id'] ?>">
            <?php endif; ?>

            <div class="admin-form__grid">
                <div class="admin-form__field">
                    <label class="form-label" for="item_name">Item Name</label>
                    <input type="text" id="item_name" name="item_name" class="form-control" maxlength="200" required value="<?= e($itemForm['name']) ?>">
                </div>

                <div class="admin-form__field">
                    <label class="form-label" for="item_category_id">Category</label>
                    <select id="item_category_id" name="item_category_id" class="form-control">
                        <option value="">No category</option>
                        <?php foreach ($categories as $category): ?>
                            <?php $categoryId = (int) ($category['id'] ?? 0); ?>
                            <option value="<?= $categoryId ?>" <?= (string) $categoryId === (string) $itemForm['category_id'] ? 'selected' : '' ?>>
                                <?= e((string) ($category['name'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-form__field">
                    <label class="form-label" for="item_price">Price</label>
                    <input type="number" id="item_price" name="item_price" class="form-control" min="0" step="0.01" placeholder="14.00" value="<?= e($itemForm['price']) ?>">
                </div>

                <div class="admin-form__field">
                    <label class="form-label" for="item_sort_order">Sort Order</label>
                    <input type="number" id="item_sort_order" name="item_sort_order" class="form-control" step="1" value="<?= e($itemForm['sort_order']) ?>">
                </div>

                <div class="admin-form__field admin-form__field--full">
                    <label class="form-label" for="item_description">Description</label>
                    <textarea id="item_description" name="item_description" class="form-control" rows="3"><?= e($itemForm['description']) ?></textarea>
                </div>

                <div class="admin-form__field admin-form__field--full">
                    <label class="admin-checkbox">
                        <input type="checkbox" name="item_is_published" value="1" <?= !empty($itemForm['is_published']) ? 'checked' : '' ?>>
                        <span>Published</span>
                    </label>
                </div>
            </div>

            <div class="admin-form__actions">
                <button type="submit" class="btn btn--primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <?= $itemForm['id'] > 0 ? 'Update Menu Item' : 'Add Menu Item' ?>
                </button>
                <?php if ($itemForm['id'] > 0): ?>
                    <a class="btn btn--outline" href="<?= e(admin_url('menu.php?venue_id=' . (int) $venueId)) ?>">Cancel Edit</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($items === []): ?>
            <div class="empty-state" style="margin-top: 1rem;">
                <h3>No menu items yet</h3>
                <p>Add the first brunch menu item for this venue.</p>
            </div>
        <?php else: ?>
            <div class="admin-table__wrap" style="margin-top: 1rem;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Item</th>
                            <th scope="col">Category</th>
                            <th scope="col">Price</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="admin-table__actions-col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <?php
                        $itemId = (int) ($item['id'] ?? 0);
                        $isPublished = !empty($item['is_published']);
                        $price = $item['price'] !== null && $item['price'] !== '' ? '$' . number_format((float) $item['price'], 2) : '—';
                        ?>
                        <tr>
                            <th scope="row" class="admin-table__title-cell">
                                <?= e((string) ($item['name'] ?? '')) ?>
                                <?php if (!empty($item['description'])): ?>
                                    <span class="admin-table__muted"><?= e((string) $item['description']) ?></span>
                                <?php endif; ?>
                            </th>
                            <td><?= e((string) ($item['category_name'] ?? 'No category')) ?></td>
                            <td><?= e($price) ?></td>
                            <td class="admin-table__badges">
                                <?php if ($isPublished): ?>
                                    <span class="badge badge--success">Published</span>
                                <?php else: ?>
                                    <span class="badge badge--draft">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td class="admin-table__actions">
                                <a class="btn btn--outline btn--sm" href="<?= e(admin_url('menu.php?venue_id=' . (int) $venueId . '&item_id=' . $itemId)) ?>">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>

                                <form method="post" action="<?= e(admin_url('menu.php')) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="venue_id" value="<?= (int) $venueId ?>">
                                    <input type="hidden" name="item_id" value="<?= $itemId ?>">
                                    <?php if ($isPublished): ?>
                                        <input type="hidden" name="action" value="unpublish_item">
                                        <button type="submit" class="btn btn--outline btn--sm">
                                            <i class="fa-solid fa-eye-slash"></i> Unpublish
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="action" value="publish_item">
                                        <button type="submit" class="btn btn--outline btn--sm">
                                            <i class="fa-solid fa-eye"></i> Publish
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
