<?php
declare(strict_types=1);

/**
 * Admin Menu Management.
 *
 * First pass:
 * - Venue-scoped menu category CRUD
 * - Venue-scoped menu item add/edit
 * - Publish/unpublish menu items
 * - No allergen/diet tagging yet
 */

require_once __DIR__ . '/../../app/bootstrap.php';
require_once APP_ROOT . '/models/Venue.php';
require_once APP_ROOT . '/models/Menu.php';

admin_require_login();

$activeNav = 'menu';
$pageTitle = page_title('Menu Management');
$debug = (bool) (app_config()['debug'] ?? false);

$venueId = 0;
if (isset($_GET['venue_id']) && is_numeric($_GET['venue_id']) && (int) $_GET['venue_id'] > 0) {
    $venueId = (int) $_GET['venue_id'];
}
if (isset($_POST['venue_id']) && is_numeric($_POST['venue_id']) && (int) $_POST['venue_id'] > 0) {
    $venueId = (int) $_POST['venue_id'];
}

$flashSuccess = flash_get('success');
$flashError = flash_get('error');

try {
    $venues = Venue::all();
} catch (Throwable $ex) {
    $venues = [];
    $flashError = $debug ? $ex->getMessage() : 'Could not load venues.';
}

$selectedVenue = null;
if ($venueId > 0) {
    try {
        $selectedVenue = Venue::find($venueId);
    } catch (Throwable $ex) {
        $selectedVenue = null;
        $flashError = $debug ? $ex->getMessage() : 'Could not load that venue.';
    }

    if ($selectedVenue === null) {
        $venueId = 0;
        $flashError = $flashError ?? 'That venue could not be found.';
    }
}

// POST actions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify((string) ($_POST['csrf_token'] ?? ''))) {
        flash_set('error', 'Security check failed. Please try again.');
        redirect(admin_url('menu.php' . ($venueId > 0 ? '?venue_id=' . $venueId : '')));
    }

    if ($venueId <= 0) {
        flash_set('error', 'Choose a venue before editing menu data.');
        redirect(admin_url('menu.php'));
    }

    $action = (string) ($_POST['action'] ?? '');

    try {
        switch ($action) {
            case 'save_category':
                $categoryId = isset($_POST['category_id']) && is_numeric($_POST['category_id'])
                    ? (int) $_POST['category_id']
                    : 0;
                $name = trim((string) ($_POST['category_name'] ?? ''));
                $sortOrder = isset($_POST['category_sort_order']) && is_numeric($_POST['category_sort_order'])
                    ? (int) $_POST['category_sort_order']
                    : 0;

                if ($name === '') {
                    flash_set('error', 'Category name is required.');
                    break;
                }
                if (mb_strlen($name) > 160) {
                    flash_set('error', 'Category name must be 160 characters or fewer.');
                    break;
                }

                $data = [
                    'venue_id' => $venueId,
                    'name' => $name,
                    'sort_order' => $sortOrder,
                ];

                if ($categoryId > 0) {
                    Menu::updateCategory($categoryId, $data);
                    flash_set('success', 'Menu category updated.');
                } else {
                    Menu::createCategory($data);
                    flash_set('success', 'Menu category added.');
                }
                break;

            case 'delete_category':
                $categoryId = isset($_POST['category_id']) && is_numeric($_POST['category_id'])
                    ? (int) $_POST['category_id']
                    : 0;

                if ($categoryId <= 0) {
                    flash_set('error', 'Invalid category.');
                    break;
                }

                if (Menu::deleteCategoryIfEmpty($categoryId, $venueId)) {
                    flash_set('success', 'Menu category deleted.');
                } else {
                    flash_set('error', 'That category still has menu items. Move or remove the items before deleting it.');
                }
                break;

            case 'save_item':
                $itemId = isset($_POST['item_id']) && is_numeric($_POST['item_id'])
                    ? (int) $_POST['item_id']
                    : 0;
                $name = trim((string) ($_POST['item_name'] ?? ''));
                $description = trim((string) ($_POST['item_description'] ?? ''));
                $categoryIdRaw = trim((string) ($_POST['item_category_id'] ?? ''));
                $priceRaw = trim((string) ($_POST['item_price'] ?? ''));
                $sortOrder = isset($_POST['item_sort_order']) && is_numeric($_POST['item_sort_order'])
                    ? (int) $_POST['item_sort_order']
                    : 0;

                if ($name === '') {
                    flash_set('error', 'Menu item name is required.');
                    break;
                }
                if (mb_strlen($name) > 200) {
                    flash_set('error', 'Menu item name must be 200 characters or fewer.');
                    break;
                }

                $categoryId = null;
                if ($categoryIdRaw !== '') {
                    $categoryId = is_numeric($categoryIdRaw) && (int) $categoryIdRaw > 0
                        ? (int) $categoryIdRaw
                        : null;
                }

                $price = null;
                if ($priceRaw !== '') {
                    if (!is_numeric($priceRaw) || (float) $priceRaw < 0) {
                        flash_set('error', 'Price must be a positive number or blank.');
                        break;
                    }
                    $price = number_format((float) $priceRaw, 2, '.', '');
                }

                $data = [
                    'venue_id' => $venueId,
                    'category_id' => $categoryId,
                    'name' => $name,
                    'description' => $description !== '' ? $description : null,
                    'price' => $price,
                    'sort_order' => $sortOrder,
                    'is_published' => isset($_POST['item_is_published']),
                ];

                if ($itemId > 0) {
                    Menu::updateItem($itemId, $data);
                    flash_set('success', 'Menu item updated.');
                } else {
                    Menu::createItem($data);
                    flash_set('success', 'Menu item added.');
                }
                break;

            case 'publish_item':
            case 'unpublish_item':
                $itemId = isset($_POST['item_id']) && is_numeric($_POST['item_id'])
                    ? (int) $_POST['item_id']
                    : 0;

                if ($itemId <= 0) {
                    flash_set('error', 'Invalid menu item.');
                    break;
                }

                Menu::setItemPublished($itemId, $venueId, $action === 'publish_item');
                flash_set('success', $action === 'publish_item' ? 'Menu item published.' : 'Menu item unpublished.');
                break;

            default:
                flash_set('error', 'Unknown menu action.');
                break;
        }
    } catch (Throwable $ex) {
        flash_set('error', $debug ? $ex->getMessage() : 'Could not save menu changes.');
    }

    redirect(admin_url('menu.php?venue_id=' . $venueId));
}

$categories = [];
$items = [];
$editCategory = null;
$editItem = null;

if ($venueId > 0) {
    try {
        $categories = Menu::adminCategoriesForVenue($venueId);
        $items = Menu::adminItemsForVenue($venueId);
    } catch (Throwable $ex) {
        $flashError = $debug ? $ex->getMessage() : 'Could not load menu data.';
    }

    if (isset($_GET['category_id']) && is_numeric($_GET['category_id']) && (int) $_GET['category_id'] > 0) {
        $editCategory = Menu::findCategory((int) $_GET['category_id']);
        if ($editCategory !== null && (int) $editCategory['venue_id'] !== $venueId) {
            $editCategory = null;
        }
    }

    if (isset($_GET['item_id']) && is_numeric($_GET['item_id']) && (int) $_GET['item_id'] > 0) {
        $editItem = Menu::findItem((int) $_GET['item_id']);
        if ($editItem !== null && (int) $editItem['venue_id'] !== $venueId) {
            $editItem = null;
        }
    }
}

require APP_ROOT . '/views/partials/admin-header.php';
require APP_ROOT . '/views/partials/admin-sidebar.php';
require APP_ROOT . '/views/admin/menu.php';
require APP_ROOT . '/views/partials/admin-footer.php';
