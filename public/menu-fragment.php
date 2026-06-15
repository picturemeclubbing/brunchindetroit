<?php
declare(strict_types=1);

/**
 * AJAX HTML fragment endpoint for the venue menu section.
 *
 * Returns ONLY the #venue-menu-section markup (no page header/footer, no JSON).
 * Used by public/assets/js/venue-menu.js to update the menu area without a
 * full page reload. Also works as a standalone URL for debugging.
 *
 * Query params:
 *   - slug     (required) venue slug
 *   - allergen (optional) allergen slug to filter by
 */

require_once __DIR__ . '/../app/bootstrap.php';
require_once APP_ROOT . '/models/Venue.php';
require_once APP_ROOT . '/models/Menu.php';

// ---- Resolve + validate the venue -----------------------------------------
$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';

if ($slug === '') {
    http_response_code(404);
    header('Content-Type: text/html; charset=utf-8');
    echo '<div class="menu-empty-state"><p>Venue not found.</p></div>';
    return;
}

$venue = Venue::findBySlug($slug);

if ($venue === null) {
    http_response_code(404);
    header('Content-Type: text/html; charset=utf-8');
    echo '<div class="menu-empty-state"><p>Venue not found.</p></div>';
    return;
}

// ---- Read + validate the optional allergen filter --------------------------
$selectedAllergen = '';
if (isset($_GET['allergen'])) {
    $candidate = strtolower(trim((string) $_GET['allergen']));
    if (preg_match('/^[a-z0-9-]+$/', $candidate)) {
        $selectedAllergen = $candidate;
    }
}

// ---- Load data -------------------------------------------------------------
$allergens      = Menu::allergens();
$menuCategories = Menu::categoriesWithItemsForVenue((int) $venue['id'], $selectedAllergen);

// Resolve the display name for the selected allergen (null if none/invalid).
$selectedAllergenName = null;
if ($selectedAllergen !== '') {
    foreach ($allergens as $a) {
        if ((string) $a['slug'] === $selectedAllergen) {
            $selectedAllergenName = (string) $a['name'];
            break;
        }
    }
    if ($selectedAllergenName === null) {
        $selectedAllergen = '';
    }
}

// Same approved disclaimer text used on the full page.
$allergyDisclaimer =
    'Allergy information is provided for general guidance only and may be ' .
    'incomplete, outdated, or subject to preparation changes. ' .
    'DetroitBrunch.com does not guarantee that any menu item is ' .
    'allergen-free. Always confirm ingredients and cross-contact risks ' .
    'directly with the restaurant before ordering.';

// ---- Serve the fragment ----------------------------------------------------
header('Content-Type: text/html; charset=utf-8');
require APP_ROOT . '/views/partials/venue-menu-section.php';