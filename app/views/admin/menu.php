<?php
/**
 * @var array<int, array<string, mixed>> $venues
 * @var array<string, mixed>|null $selectedVenue
 * @var int $venueId
 * @var array<int, array<string, mixed>> $categories
 * @var array<int, array<string, mixed>> $items
 * @var array<string, mixed>|null $editCategory
 * @var array<string, mixed>|null $editItem
 * @var array<int, array<string, mixed>> $dietaryTags
 * @var array<int, array<string, mixed>> $allergens
 * @var array<int, int> $editItemDietaryTagIds
 * @var array<int, string> $editItemAllergenStatuses
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
    'image_url' => $editItem !== null ? (string) ($editItem['image_url'] ?? '') : '',
    'image_alt_text' => $editItem !== null ? (string) ($editItem['image_alt_text'] ?? '') : '',
    'price' => $editItem !== null && $editItem['price'] !== null ? (string) $editItem['price'] : '',
    'sort_order' => $editItem !== null ? (string) (int) $editItem['sort_order'] : '0',
    'is_published' => $editItem !== null ? !empty($editItem['is_published']) : true,
];

$editItemDietaryTagIds = isset($editItemDietaryTagIds) && is_array($editItemDietaryTagIds)
    ? array_map('intval', $editItemDietaryTagIds)
    : [];

$editItemAllergenStatuses = isset($editItemAllergenStatuses) && is_array($editItemAllergenStatuses)
    ? $editItemAllergenStatuses
    : [];

$allergenStatusOptions = [
    'unknown' => 'Unknown / not reviewed',
    'contains' => 'Contains',
    'does_not_contain' => 'Does not contain',
    'may_contain' => 'May contain',
    'cross_contact_risk' => 'Cross-contact risk',
];

$showItemEditor = $selectedVenue !== null
    && ((string) ($_GET['mode'] ?? '') === 'add-item' || $itemForm['id'] > 0);

$showCategoryEditor = $selectedVenue !== null
    && ((string) ($_GET['mode'] ?? '') === 'add-category' || $categoryForm['id'] > 0);

$publishedCount = 0;
$draftCount = 0;
foreach ($items as $item) {
    if (!empty($item['is_published'])) {
        $publishedCount++;
    } else {
        $draftCount++;
    }
}
?>
<div class="admin-page-header admin-menu-header">
    <div>
        <h1 class="admin-page-title">Menu Management</h1>
        <p class="admin-page-lead">
            Manage brunch categories, menu items, images, dietary tags, and allergen details.
        </p>
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

<div class="admin-panel admin-menu-toolbar">
    <form class="admin-menu-toolbar__venue" method="get" action="<?= e(admin_url('menu.php')) ?>">
        <label class="form-label" for="venue_id">Venue</label>
        <select id="venue_id" name="venue_id" class="form-control" onchange="this.form.submit()">
            <option value="">Select a venue</option>
            <?php foreach ($venues as $venue): ?>
                <?php $optionId = (int) ($venue['id'] ?? 0); ?>
                <option value="<?= $optionId ?>" <?= $optionId === $venueId ? 'selected' : '' ?>>
                    <?= e((string) ($venue['name'] ?? 'Untitled venue')) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($selectedVenue !== null): ?>
        <div class="admin-menu-toolbar__stats" aria-label="Menu stats">
            <div class="admin-menu-stat">
                <strong><?= count($categories) ?></strong>
                <span>Categories</span>
            </div>
            <div class="admin-menu-stat">
                <strong><?= count($items) ?></strong>
                <span>Items</span>
            </div>
            <div class="admin-menu-stat">
                <strong><?= $publishedCount ?></strong>
                <span>Published</span>
            </div>
            <div class="admin-menu-stat">
                <strong><?= $draftCount ?></strong>
                <span>Drafts</span>
            </div>
        </div>

        <div class="admin-menu-toolbar__actions">
            <a class="btn btn--outline" href="<?= e(asset_url('venue.php?slug=' . urlencode((string) $selectedVenue['slug']))) ?>" target="_blank" rel="noopener noreferrer">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> View Venue
            </a>
        </div>
    <?php endif; ?>
</div>

<?php if ($selectedVenue === null): ?>
    <div class="empty-state">
        <h3>Choose a venue</h3>
        <p>Select a venue above to manage its brunch menu.</p>
    </div>
<?php else: ?>
    <div class="admin-menu-layout">
        <section class="admin-panel admin-menu-card admin-menu-card--categories">
            <div class="admin-menu-card__header">
                <div>
                    <h2 class="admin-panel__title">Categories</h2>
                    <p class="admin-menu-card__lead">Organize the menu into groups.</p>
                </div>
                <a class="btn btn--primary btn--sm" href="<?= e(admin_url('menu.php?venue_id=' . (int) $venueId . '&mode=add-category')) ?>">
                    <i class="fa-solid fa-plus"></i> Add Category
                </a>
            </div>
            <?php if ($categories === []): ?>
                <div class="admin-menu-empty">
                    <p>No categories yet.</p>
                </div>
            <?php else: ?>
                <div class="admin-table__wrap">
                    <table class="admin-table admin-menu-table admin-menu-table--compact">
                        <thead>
                            <tr>
                                <th scope="col">Category</th>
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
                                <th scope="row" class="admin-table__title-cell">
                                    <?= e((string) ($category['name'] ?? '')) ?>
                                </th>
                                <td><?= $itemCount ?></td>
                                <td class="admin-menu-icon-actions">
                                    <a class="admin-icon-action" href="<?= e(admin_url('menu.php?venue_id=' . (int) $venueId . '&category_id=' . $categoryId)) ?>" aria-label="Edit category">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>

                                    <form method="post" action="<?= e(admin_url('menu.php')) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete_category">
                                        <input type="hidden" name="venue_id" value="<?= (int) $venueId ?>">
                                        <input type="hidden" name="category_id" value="<?= $categoryId ?>">
                                        <?php if ($itemCount > 0): ?>
                                            <button
                                                type="button"
                                                class="admin-icon-action admin-icon-action--disabled"
                                                aria-label="Category has items and cannot be deleted"
                                                title="Move or delete items before deleting this category"
                                                disabled
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button
                                                type="submit"
                                                class="admin-icon-action admin-icon-action--danger"
                                                aria-label="Delete category"
                                                onclick="return confirm('Delete this category? This cannot be undone.');"
                                            >
                                                <i class="fa-solid fa-trash"></i>
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
        </section>

        <section class="admin-panel admin-menu-card admin-menu-card--items">
            <div class="admin-menu-card__header">
                <div>
                    <h2 class="admin-panel__title">Menu Items</h2>
                    <p class="admin-menu-card__lead">Add, edit, and publish menu items.</p>
                </div>
                <a class="btn btn--primary btn--sm" href="<?= e(admin_url('menu.php?venue_id=' . (int) $venueId . '&mode=add-item')) ?>">
                    <i class="fa-solid fa-plus"></i> Add Item
                </a>
            </div>

            <?php if ($items === []): ?>
                <div class="admin-menu-empty">
                    <p>No menu items yet.</p>
                </div>
            <?php else: ?>
                <div class="admin-table__wrap">
                    <table class="admin-table admin-menu-table admin-menu-items-table">
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
                            $price = $item['price'] !== null && $item['price'] !== '' ? '$' . number_format((float) $item['price'], 2) : 'ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â';
                            $imageUrl = (string) ($item['image_url'] ?? '');
                            ?>
                            <tr>
                                <th scope="row" class="admin-menu-item-cell">
                                    <?php if ($imageUrl !== ''): ?>
                                        <img class="admin-menu-thumb" src="<?= e($imageUrl) ?>" alt="" loading="lazy">
                                    <?php else: ?>
                                        <span class="admin-menu-thumb admin-menu-thumb--empty">
                                            <i class="fa-solid fa-utensils"></i>
                                        </span>
                                    <?php endif; ?>
                                    <span>
                                        <strong><?= e((string) ($item['name'] ?? '')) ?></strong>
                                        <?php if (!empty($item['description'])): ?>
                                            <span class="admin-table__muted"><?= e((string) $item['description']) ?></span>
                                        <?php endif; ?>
                                    </span>
                                </th>
                                <td><?= e((string) ($item['category_name'] ?? 'No category')) ?></td>
                                <td><?= e($price) ?></td>                                <td class="admin-menu-status-cell">
                                    <form method="post" action="<?= e(admin_url('menu.php')) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="venue_id" value="<?= (int) $venueId ?>">
                                        <input type="hidden" name="item_id" value="<?= $itemId ?>">
                                        <?php if ($isPublished): ?>
                                            <input type="hidden" name="action" value="unpublish_item">
                                            <button type="submit" class="admin-status-toggle admin-status-toggle--published" title="Click to unpublish">
                                                Published
                                            </button>
                                        <?php else: ?>
                                            <input type="hidden" name="action" value="publish_item">
                                            <button type="submit" class="admin-status-toggle admin-status-toggle--draft" title="Click to publish">
                                                Draft
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                                <td class="admin-menu-icon-actions">
                                    <a class="admin-icon-action" href="<?= e(admin_url('menu.php?venue_id=' . (int) $venueId . '&item_id=' . $itemId)) ?>" aria-label="Edit menu item">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>

                                    <form method="post" action="<?= e(admin_url('menu.php')) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete_item">
                                        <input type="hidden" name="venue_id" value="<?= (int) $venueId ?>">
                                        <input type="hidden" name="item_id" value="<?= $itemId ?>">
                                        <button
                                            type="submit"
                                            class="admin-icon-action admin-icon-action--danger"
                                            aria-label="Delete menu item"
                                            onclick="return confirm('Delete this menu item? This cannot be undone.');"
                                        >
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <?php if ($showCategoryEditor): ?>
        <div class="admin-menu-modal" role="dialog" aria-modal="true" aria-label="<?= $categoryForm['id'] > 0 ? 'Edit category' : 'Add category' ?>">
            <div class="admin-menu-modal__backdrop"></div>

            <div class="admin-menu-modal__panel admin-menu-modal__panel--small">
                <div class="admin-menu-modal__header">
                    <div>
                        <h2><?= $categoryForm['id'] > 0 ? 'Edit Category' : 'Add Category' ?></h2>
                        <p>Use categories to group menu items on the public venue page.</p>
                    </div>
                    <a class="admin-menu-modal__close" href="<?= e(admin_url('menu.php?venue_id=' . (int) $venueId)) ?>" aria-label="Close editor">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                </div>

                <form class="admin-menu-editor" method="post" action="<?= e(admin_url('menu.php')) ?>">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="save_category">
                    <input type="hidden" name="venue_id" value="<?= (int) $venueId ?>">
                    <?php if ($categoryForm['id'] > 0): ?>
                        <input type="hidden" name="category_id" value="<?= (int) $categoryForm['id'] ?>">
                    <?php endif; ?>

                    <section class="admin-menu-editor__section">
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
                    </section>

                    <div class="admin-menu-editor__actions">
                        <a class="btn btn--outline" href="<?= e(admin_url('menu.php?venue_id=' . (int) $venueId)) ?>">Cancel</a>
                        <button type="submit" class="btn btn--primary">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <?= $categoryForm['id'] > 0 ? 'Save Category' : 'Add Category' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($showItemEditor): ?>
        <div class="admin-menu-modal" role="dialog" aria-modal="true" aria-label="<?= $itemForm['id'] > 0 ? 'Edit menu item' : 'Add menu item' ?>">
            <div class="admin-menu-modal__backdrop"></div>

            <div class="admin-menu-modal__panel">
                <div class="admin-menu-modal__header">
                    <div>
                        <h2><?= $itemForm['id'] > 0 ? 'Edit Menu Item' : 'Add Menu Item' ?></h2>
                        <p>Basic item details first. Dietary and allergen details are optional.</p>
                    </div>
                    <a class="admin-menu-modal__close" href="<?= e(admin_url('menu.php?venue_id=' . (int) $venueId)) ?>" aria-label="Close editor">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                </div>

                <form class="admin-menu-editor" method="post" action="<?= e(admin_url('menu.php')) ?>">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="save_item">
                    <input type="hidden" name="venue_id" value="<?= (int) $venueId ?>">
                    <?php if ($itemForm['id'] > 0): ?>
                        <input type="hidden" name="item_id" value="<?= (int) $itemForm['id'] ?>">
                    <?php endif; ?>

                    <section class="admin-menu-editor__section">
                        <h3>Basic Info</h3>

                        <div class="admin-form__grid">
                            <div class="admin-form__field">
                                <label class="form-label" for="item_name">Item Name <span class="admin-form__req">*</span></label>
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
                                <label class="form-label" for="item_image_url">Menu Item Image URL</label>
                                <input type="url" id="item_image_url" name="item_image_url" class="form-control" maxlength="500" placeholder="https://..." value="<?= e($itemForm['image_url']) ?>">
                                <span class="admin-form__hint">Use an externally hosted image URL for now. Upload support can be added later.</span>
                            </div>

                            <div class="admin-form__field admin-form__field--full">
                                <label class="form-label" for="item_image_alt_text">Image Alt Text</label>
                                <input type="text" id="item_image_alt_text" name="item_image_alt_text" class="form-control" maxlength="255" placeholder="Short description of the image" value="<?= e($itemForm['image_alt_text']) ?>">
                            </div>


                        </div>
                    </section>

                    <section class="admin-menu-editor__section">
                        <h3>Dietary Tags</h3>
                        <?php if (empty($dietaryTags)): ?>
                            <span class="admin-form__hint">No dietary tags are available. Run the seed file first.</span>
                        <?php else: ?>
                            <div class="admin-check-grid">
                                <?php foreach ($dietaryTags as $tag): ?>
                                    <?php $tagId = (int) ($tag['id'] ?? 0); ?>
                                    <label class="admin-checkbox">
                                        <input
                                            type="checkbox"
                                            name="dietary_tag_ids[]"
                                            value="<?= $tagId ?>"
                                            <?= in_array($tagId, $editItemDietaryTagIds, true) ? 'checked' : '' ?>
                                        >
                                        <span><?= e((string) ($tag['name'] ?? '')) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>

                    <details class="admin-menu-editor__section admin-menu-editor__details">
                        <summary>
                            <span>Allergen Info</span>
                            <small>Open only when allergen data needs review.</small>
                        </summary>

                        <p class="admin-form__hint">
                            Unknown statuses are not shown publicly. ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã¢â‚¬Å“Does not containÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â is required for an item to appear when visitors filter by that allergen.
                        </p>

                        <?php if (empty($allergens)): ?>
                            <span class="admin-form__hint">No allergens are available.</span>
                        <?php else: ?>
                            <div class="admin-allergen-grid">
                                <?php foreach ($allergens as $allergen): ?>
                                    <?php
                                    $allergenId = (int) ($allergen['id'] ?? 0);
                                    $selectedStatus = (string) ($editItemAllergenStatuses[$allergenId] ?? 'unknown');
                                    ?>
                                    <div class="admin-allergen-grid__row">
                                        <label class="form-label" for="allergen_status_<?= $allergenId ?>">
                                            <?= e((string) ($allergen['name'] ?? '')) ?>
                                        </label>
                                        <select
                                            id="allergen_status_<?= $allergenId ?>"
                                            name="allergen_statuses[<?= $allergenId ?>]"
                                            class="form-control"
                                        >
                                            <?php foreach ($allergenStatusOptions as $value => $label): ?>
                                                <option value="<?= e($value) ?>" <?= $selectedStatus === $value ? 'selected' : '' ?>>
                                                    <?= e($label) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </details>

                    <div class="admin-menu-editor__actions">
                        <label class="admin-checkbox admin-menu-footer-published">
                            <input type="checkbox" name="item_is_published" value="1" <?= !empty($itemForm['is_published']) ? 'checked' : '' ?>>
                            <span>Published</span>
                        </label>

                        <div class="admin-menu-editor__actions-main">
                            <a class="btn btn--outline" href="<?= e(admin_url('menu.php?venue_id=' . (int) $venueId)) ?>">Cancel</a>
                            <button type="submit" class="btn btn--primary">
                                <i class="fa-solid fa-floppy-disk"></i> Save Item
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
