<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_once APP_ROOT . '/models/Venue.php';
require_once APP_ROOT . '/models/Menu.php';
require_once APP_ROOT . '/models/Gallery.php';

// Read & validate the slug from the query string.
$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';

if ($slug === '') {
    http_response_code(404);
    $pageTitle = 'Venue not found';
    require APP_ROOT . '/views/partials/header.php';
    ?>
    <main>
        <section class="section section--muted">
            <div class="container">
                <div class="not-found">
                    <p class="eyebrow eyebrow--dark">404</p>
                    <h1 class="not-found__title">Venue not found</h1>
                    <p class="not-found__text">This venue is not published or does not exist.</p>
                    <a class="btn btn--primary" href="<?= e(asset_url('directory.php')) ?>">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i>
                        Back to Directory
                    </a>
                </div>
            </div>
        </section>
    </main>
    <?php
    require APP_ROOT . '/views/partials/footer.php';
    return;
}

$venue = Venue::findBySlug($slug);

if ($venue === null) {
    http_response_code(404);
    $pageTitle = 'Venue not found';
    require APP_ROOT . '/views/partials/header.php';
    ?>
    <main>
        <section class="section section--muted">
            <div class="container">
                <div class="not-found">
                    <p class="eyebrow eyebrow--dark">404</p>
                    <h1 class="not-found__title">Venue not found</h1>
                    <p class="not-found__text">This venue is not published or does not exist.</p>
                    <a class="btn btn--primary" href="<?= e(asset_url('directory.php')) ?>">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i>
                        Back to Directory
                    </a>
                </div>
            </div>
        </section>
    </main>
    <?php
    require APP_ROOT . '/views/partials/footer.php';
    return;
}

// Read the optional allergen filter from the query string and sanitize it
// to a simple slug (lowercase letters, digits, hyphens).
$selectedAllergen = '';
if (isset($_GET['allergen'])) {
    $candidate = strtolower(trim((string) $_GET['allergen']));
    if (preg_match('/^[a-z0-9-]+$/', $candidate)) {
        $selectedAllergen = $candidate;
    }
}

// Load allergen list + venue-scoped menu categories/items (with filtering).
$allergens       = Menu::allergens();
$menuCategories  = Menu::categoriesWithItemsForVenue((int) $venue['id'], $selectedAllergen);

// Resolve a human-readable name for the selected allergen (or null).
// An invalid slug is treated as "no filter selected".
$selectedAllergenName = null;
if ($selectedAllergen !== '') {
    foreach ($allergens as $a) {
        if ((string) $a['slug'] === $selectedAllergen) {
            $selectedAllergenName = (string) $a['name'];
            break;
        }
    }
    // If the slug didn't match a known allergen, reset to no-filter.
    if ($selectedAllergenName === null) {
        $selectedAllergen = '';
    }
}

// Allergy disclaimer shown above the menu filter. No site_setting helper
// exists yet, so use the approved safe fallback text directly.
$allergyDisclaimer =
    'Allergy information is provided for general guidance only and may be ' .
    'incomplete, outdated, or subject to preparation changes. ' .
    'DetroitBrunch.com does not guarantee that any menu item is ' .
    'allergen-free. Always confirm ingredients and cross-contact risks ' .
    'directly with the restaurant before ordering.';

$recentVenueGallery = Gallery::recentForVenue((int) $venue['id']);

$pageTitle = $venue['name'];
require APP_ROOT . '/views/venue-detail.php';
