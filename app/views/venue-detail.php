<?php
declare(strict_types=1);

/**
 * Read-only venue profile page (Phase 3B).
 *
 * Renders a polished, location-profile-style layout for a single published
 * venue. No reviews, ratings, RSVP, events, or user accounts in this MVP.
 *
 * Desktop layout follows the profile mockup:
 *   - Wide centered container (~1200px)
 *   - Top grid: large hero card (left) + Contact & Location card (right)
 *   - Quick info strip directly under the top grid
 *   - Main two-column area with a TABBED left column:
 *       Overview (About + What to Know), Photos (Interior + Event Galleries),
 *       Menu (Allergy Filtering), Events (placeholder)
 *     and a narrower sidebar (Quick Facts, Upcoming Events, Ad placeholder)
 *   - On mobile everything stacks into a single readable column.
 *
 * Tab behavior is progressive: without JS all panels are visible; with JS
 * only the active panel shows (see public/assets/js/venue-tabs.js).
 *
 * @var array|null $venue Single venue row from Venue::findBySlug().
 */

// Defensive guard: if we ever reach this view without a venue, render the
// styled not-found state instead of fataling on missing array keys.
if (!isset($venue) || empty($venue)) {
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

// ---- Pre-compute display values so the markup below stays clean. ----------

$hasNeighborhood = !empty($venue['neighborhood_name']);
$hasPrice        = !empty($venue['price_range']);
$hasHours        = !empty($venue['brunch_hours_note']);
$hasPhone        = !empty($venue['phone']);
$hasWebsite      = !empty($venue['website_url']);
$hasInstagram    = !empty($venue['instagram_url']);
$hasFacebook     = !empty($venue['facebook_url']);
$hasUpdated      = !empty($venue['updated_at']);

// Build a safe single-line address from whichever fields are present.
$addressParts = array_filter([
    $venue['address_line1'] ?? '',
    $venue['address_line2'] ?? '',
    trim((($venue['city'] ?? '') . ' ' . ($venue['state'] ?? '') . ' ' . ($venue['zip'] ?? ''))),
], static fn ($v) => trim((string) $v) !== '');
$addressLine = implode(', ', $addressParts);

// Hero background image (if any). Supports full URLs and project-relative paths.
$hasImage  = !empty($venue['main_image_path']);
$imageUrl  = '';
if ($hasImage) {
    $imgPath  = (string) $venue['main_image_path'];
    $imageUrl = str_starts_with($imgPath, 'http') ? $imgPath : asset_url($imgPath);
}

// Sanitized phone number for tel: links (keep digits and a leading +).
$phoneTel = $hasPhone ? preg_replace('/[^0-9+]/', '', (string) $venue['phone']) : '';

// Human-friendly last-updated date.
$updatedFormatted = $hasUpdated ? date('F j, Y', strtotime((string) $venue['updated_at'])) : '';

// Visual placeholder images for the Interior Gallery and Recent Event
// Galleries sections. These REUSE image URLs already present on the home
// page and DeepSite reference designs so the profile page feels populated
// before the admin-managed gallery feature is built. They are decorative
// placeholders only — not real venue data — and involve no DB queries.
//
// Sources (all already referenced in the project):
//   - app/views/home.php (featured slider, articles, gallery cards)
//   - deepsite-originals/home_index.html + index_gallery.html
$galleryImages = [
    'https://images.unsplash.com/photo-1559847844-5315695dadae?auto=format&fit=crop&w=1198&q=80',
    'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1170&q=80',
    'https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=1153&q=80',
    'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=1170&q=80',
];
// Default large interior image when the venue has no main_image_path
// (same Unsplash photo used for The Garden Rooftop on the home page slider).
$defaultInteriorImage = 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1074&q=80';
// Image for the recent event gallery cards (same photo used by the home
// page gallery cards for Sunday Jazz Brunch / The Grand Brunch House).
$eventGalleryImage = 'https://images.unsplash.com/photo-1551218808-94e220e084d2?auto=format&fit=crop&w=1074&q=80';

require APP_ROOT . '/views/partials/header.php';
?>

<main>
    <section class="section section--muted venue-profile">
        <div class="container">

            <!-- A. Breadcrumb -->
            <nav class="venue-breadcrumb" aria-label="Breadcrumb">
                <a href="<?= e(asset_url('index.php')) ?>">Home</a>
                <span class="venue-breadcrumb__sep" aria-hidden="true">&rsaquo;</span>
                <a href="<?= e(asset_url('directory.php')) ?>">Directory</a>
                <span class="venue-breadcrumb__sep" aria-hidden="true">&rsaquo;</span>
                <span class="venue-breadcrumb__current"><?= e($venue['name']) ?></span>
            </nav>

            <!-- B. Top profile area: hero (left) + Contact & Location (right) -->
            <div class="venue-profile-top">

                <!-- Hero profile card -->
                <section class="venue-profile-hero <?= $hasImage ? '' : 'venue-profile-hero--fallback' ?>"<?php if ($hasImage): ?> style="background-image: url('<?= e($imageUrl) ?>');"<?php endif; ?>>
                    <div class="venue-profile-hero__content">
                        <?php if (!empty($venue['is_featured'])): ?>
                            <span class="badge badge--accent venue-profile-hero__featured">Featured</span>
                        <?php endif; ?>

                        <p class="eyebrow">Detroit Brunch Guide</p>
                        <h1 class="venue-profile-hero__title"><?= e($venue['name']) ?></h1>

                        <?php if ($hasNeighborhood || $hasPrice): ?>
                            <p class="venue-profile-hero__meta">
                                <?php if ($hasNeighborhood): ?>
                                    <span class="venue-profile-hero__meta-item">
                                        <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                                        <?= e($venue['neighborhood_name']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($hasNeighborhood && $hasPrice): ?>
                                    <span class="venue-profile-hero__meta-sep" aria-hidden="true">&middot;</span>
                                <?php endif; ?>
                                <?php if ($hasPrice): ?>
                                    <span class="venue-profile-hero__price"><?= e($venue['price_range']) ?></span>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($hasHours): ?>
                            <p class="venue-profile-hero__hours">
                                <i class="fas fa-clock" aria-hidden="true"></i>
                                <?= e($venue['brunch_hours_note']) ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($venue['description'])): ?>
                            <p class="venue-profile-hero__description"><?= e($venue['description']) ?></p>
                        <?php endif; ?>

                        <div class="venue-profile-hero__actions">
                            <a class="btn btn--primary" href="<?= e(asset_url('directory.php')) ?>">
                                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                                Back to Directory
                            </a>
                            <?php if ($hasWebsite): ?>
                                <a class="btn btn--outline-light" href="<?= e($venue['website_url']) ?>" target="_blank" rel="noopener">
                                    <i class="fas fa-up-right-from-square" aria-hidden="true"></i>
                                    Website
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <!-- Contact & Location sidebar card (sits beside the hero) -->
                <aside class="venue-profile-contact">
                    <article class="venue-profile-panel">
                        <h2 class="venue-profile-panel__title">Contact & Location</h2>
                        <ul class="venue-profile-info-list">
                            <?php if ($addressLine !== ''): ?>
                                <li class="venue-profile-info-list__item">
                                    <i class="fas fa-location-dot" aria-hidden="true"></i>
                                    <span><?= e($addressLine) ?></span>
                                </li>
                            <?php endif; ?>

                            <?php if ($hasPhone): ?>
                                <li class="venue-profile-info-list__item">
                                    <i class="fas fa-phone" aria-hidden="true"></i>
                                    <a href="tel:<?= e($phoneTel) ?>"><?= e($venue['phone']) ?></a>
                                </li>
                            <?php endif; ?>

                            <?php if ($hasWebsite): ?>
                                <li class="venue-profile-info-list__item">
                                    <i class="fas fa-globe" aria-hidden="true"></i>
                                    <a href="<?= e($venue['website_url']) ?>" target="_blank" rel="noopener">Website</a>
                                </li>
                            <?php endif; ?>

                            <?php if ($hasInstagram): ?>
                                <li class="venue-profile-info-list__item">
                                    <i class="fab fa-instagram" aria-hidden="true"></i>
                                    <a href="<?= e($venue['instagram_url']) ?>" target="_blank" rel="noopener">Instagram</a>
                                </li>
                            <?php endif; ?>

                            <?php if ($hasFacebook): ?>
                                <li class="venue-profile-info-list__item">
                                    <i class="fab fa-facebook" aria-hidden="true"></i>
                                    <a href="<?= e($venue['facebook_url']) ?>" target="_blank" rel="noopener">Facebook</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </article>
                </aside>

            </div>

            <!-- C. Quick info strip -->
            <div class="venue-profile-strip">
                <div class="venue-profile-strip__item">
                    <span class="venue-profile-strip__label">Neighborhood</span>
                    <span class="venue-profile-strip__value"><?= $hasNeighborhood ? e($venue['neighborhood_name']) : '&mdash;' ?></span>
                </div>
                <div class="venue-profile-strip__item">
                    <span class="venue-profile-strip__label">Price</span>
                    <span class="venue-profile-strip__value"><?= $hasPrice ? e($venue['price_range']) : '&mdash;' ?></span>
                </div>
                <div class="venue-profile-strip__item">
                    <span class="venue-profile-strip__label">Brunch Hours</span>
                    <span class="venue-profile-strip__value"><?= $hasHours ? e($venue['brunch_hours_note']) : '&mdash;' ?></span>
                </div>
                <div class="venue-profile-strip__item">
                    <span class="venue-profile-strip__label">Last Updated</span>
                    <span class="venue-profile-strip__value"><?= $updatedFormatted !== '' ? e($updatedFormatted) : '&mdash;' ?></span>
                </div>
            </div>

            <!-- D & E. Main content (tabbed) + sidebar -->
            <div class="venue-profile-layout">

                <!-- D. Main content column — now using a tab component -->
                <div class="venue-profile-main">
                    <div class="venue-tabs" data-venue-tabs>

                        <!-- Tab navigation -->
                        <div class="venue-tabs__nav" role="tablist" aria-label="<?= e($venue['name']) ?> details">
                            <button class="venue-tabs__button" type="button" role="tab"
                                    id="venue-tab-overview"
                                    data-venue-tab="overview"
                                    aria-selected="false"
                                    aria-controls="venue-tabpanel-overview"
                                    tabindex="-1">
                                <i class="fas fa-circle-info" aria-hidden="true"></i>
                                <span>Overview</span>
                            </button>
                            <button class="venue-tabs__button" type="button" role="tab"
                                    id="venue-tab-photos"
                                    data-venue-tab="photos"
                                    aria-selected="false"
                                    aria-controls="venue-tabpanel-photos"
                                    tabindex="-1">
                                <i class="fas fa-images" aria-hidden="true"></i>
                                <span>Photos</span>
                            </button>
                            <button class="venue-tabs__button" type="button" role="tab"
                                    id="venue-tab-menu"
                                    data-venue-tab="menu"
                                    aria-selected="false"
                                    aria-controls="venue-tabpanel-menu"
                                    tabindex="-1">
                                <i class="fas fa-utensils" aria-hidden="true"></i>
                                <span>Menu</span>
                            </button>
                            <button class="venue-tabs__button" type="button" role="tab"
                                    id="venue-tab-events"
                                    data-venue-tab="events"
                                    aria-selected="false"
                                    aria-controls="venue-tabpanel-events"
                                    tabindex="-1">
                                <i class="fas fa-calendar-days" aria-hidden="true"></i>
                                <span>Events</span>
                            </button>
                        </div>

                        <!-- Panel 1: Overview (About + What to Know) -->
                        <div class="venue-tabs__panel" role="tabpanel"
                             id="venue-tabpanel-overview"
                             aria-labelledby="venue-tab-overview"
                             data-venue-tab-panel="overview">

                            <article class="venue-profile-panel">
                                <h2 class="venue-profile-panel__title">About</h2>
                                <?php if (!empty($venue['description'])): ?>
                                    <p class="venue-profile-about__text"><?= e($venue['description']) ?></p>
                                <?php else: ?>
                                    <p class="venue-profile-about__text venue-profile-about__text--muted">
                                        No description has been added for this venue yet.
                                    </p>
                                <?php endif; ?>
                            </article>

                            <article class="venue-profile-panel venue-profile-placeholder">
                                <h2 class="venue-profile-panel__title">What to Know</h2>
                                <p class="venue-profile-placeholder__text">
                                    <?php if ($hasHours): ?>
                                        Brunch is served <?= e($venue['brunch_hours_note']) ?>.
                                    <?php endif; ?>
                                    <?php if ($hasNeighborhood): ?>
                                        Located in the <?= e($venue['neighborhood_name']) ?> neighborhood.
                                    <?php endif; ?>
                                    Details are curated by DetroitBrunch.com &mdash; please confirm hours and
                                    menus directly with the venue before visiting.
                                </p>
                            </article>
                        </div>

                        <!-- Panel 2: Photos (Interior Gallery + Recent Event Galleries) -->
                        <div class="venue-tabs__panel" role="tabpanel"
                             id="venue-tabpanel-photos"
                             aria-labelledby="venue-tab-photos"
                             data-venue-tab-panel="photos">

                            <article class="venue-profile-panel">
                                <h2 class="venue-profile-panel__title">Interior Gallery</h2>
                                <div class="venue-gallery-grid">
                                    <img
                                        class="venue-gallery-grid__large"
                                        src="<?= e($hasImage ? $imageUrl : $defaultInteriorImage) ?>"
                                        alt="Interior preview of <?= e($venue['name']) ?>"
                                        loading="lazy"
                                    >
                                    <img
                                        class="venue-gallery-grid__small"
                                        src="<?= e($galleryImages[0]) ?>"
                                        alt="Brunch atmosphere preview"
                                        loading="lazy"
                                    >
                                    <img
                                        class="venue-gallery-grid__small"
                                        src="<?= e($galleryImages[1]) ?>"
                                        alt="Brunch dining preview"
                                        loading="lazy"
                                    >
                                    <img
                                        class="venue-gallery-grid__small"
                                        src="<?= e($galleryImages[2]) ?>"
                                        alt="Brunch dishes preview"
                                        loading="lazy"
                                    >
                                    <img
                                        class="venue-gallery-grid__small"
                                        src="<?= e($galleryImages[3]) ?>"
                                        alt="Brunch pastry preview"
                                        loading="lazy"
                                    >
                                </div>
                                <p class="venue-profile-gallery__note">
                                    <i class="fas fa-circle-info" aria-hidden="true"></i>
                                    Interior photos will be managed from the admin area in a later phase.
                                </p>
                            </article>

                            <article class="venue-profile-panel">
                                <h2 class="venue-profile-panel__title">Recent Event Galleries</h2>
                                <div class="venue-event-gallery-list">
                                    <article class="venue-event-gallery-card">
                                        <img
                                            class="venue-event-gallery-card__image"
                                            src="<?= e($eventGalleryImage) ?>"
                                            alt="Sunday Brunch Pop-Up preview"
                                            loading="lazy"
                                        >
                                        <div class="venue-event-gallery-card__body">
                                            <p class="venue-event-gallery-card__date">
                                                <i class="fas fa-calendar" aria-hidden="true"></i>
                                                Coming soon
                                            </p>
                                            <h3 class="venue-event-gallery-card__title">Sunday Brunch Pop-Up</h3>
                                            <p class="venue-event-gallery-card__text">
                                                Photos from special brunch events and pop-ups will appear here
                                                once the gallery feature launches.
                                            </p>
                                            <span class="venue-event-gallery-card__action" aria-disabled="true">
                                                <i class="fas fa-images" aria-hidden="true"></i>
                                                View Gallery Coming Soon
                                            </span>
                                        </div>
                                    </article>

                                    <article class="venue-event-gallery-card">
                                        <img
                                            class="venue-event-gallery-card__image"
                                            src="<?= e($galleryImages[1]) ?>"
                                            alt="Seasonal Brunch Tasting preview"
                                            loading="lazy"
                                        >
                                        <div class="venue-event-gallery-card__body">
                                            <p class="venue-event-gallery-card__date">
                                                <i class="fas fa-calendar" aria-hidden="true"></i>
                                                Coming soon
                                            </p>
                                            <h3 class="venue-event-gallery-card__title">Seasonal Brunch Tasting</h3>
                                            <p class="venue-event-gallery-card__text">
                                                Seasonal brunch tasting events will be featured here in a future
                                                phase of DetroitBrunch.com.
                                            </p>
                                            <span class="venue-event-gallery-card__action" aria-disabled="true">
                                                <i class="fas fa-images" aria-hidden="true"></i>
                                                View Gallery Coming Soon
                                            </span>
                                        </div>
                                    </article>
                                </div>
                            </article>
                        </div>

                        <!-- Panel 3: Menu (Allergy Filtering) -->
                        <div class="venue-tabs__panel" role="tabpanel"
                             id="venue-tabpanel-menu"
                             aria-labelledby="venue-tab-menu"
                             data-venue-tab-panel="menu">

                            <?php
                            // Menu & Allergy Filtering section is rendered from a shared
                            // partial so the same markup can be served as an AJAX HTML
                            // fragment by public/menu-fragment.php.
                            // The #venue-menu-section inside this panel is the AJAX
                            // replacement target for venue-menu.js — it must stay here.
                            require APP_ROOT . '/views/partials/venue-menu-section.php';
                            ?>
                        </div>

                        <!-- Panel 4: Events (placeholder) -->
                        <div class="venue-tabs__panel" role="tabpanel"
                             id="venue-tabpanel-events"
                             aria-labelledby="venue-tab-events"
                             data-venue-tab-panel="events">

                            <article class="venue-profile-panel venue-profile-panel--note">
                                <h2 class="venue-profile-panel__title">Upcoming Events</h2>
                                <div class="venue-profile-events-empty">
                                    <i class="fas fa-calendar-xmark" aria-hidden="true"></i>
                                    <p class="venue-profile-note__text">No upcoming events listed yet.</p>
                                    <p class="venue-profile-note__text venue-profile-note__text--muted">
                                        Check back soon for brunch events at this location.
                                    </p>
                                </div>
                            </article>
                        </div>

                    </div>
                </div>

                <!-- E. Sidebar column -->
                <aside class="venue-profile-sidebar">

                    <!-- 1. Quick Facts -->
                    <article class="venue-profile-panel">
                        <h2 class="venue-profile-panel__title">Quick Facts</h2>
                        <ul class="venue-profile-info-list venue-profile-info-list--facts">
                            <li class="venue-profile-info-list__item">
                                <span class="venue-profile-info-list__label">Cuisine</span>
                                <span class="venue-profile-info-list__value">Coming soon</span>
                            </li>
                            <li class="venue-profile-info-list__item">
                                <span class="venue-profile-info-list__label">Neighborhood</span>
                                <span class="venue-profile-info-list__value"><?= $hasNeighborhood ? e($venue['neighborhood_name']) : 'Not listed' ?></span>
                            </li>
                            <li class="venue-profile-info-list__item">
                                <span class="venue-profile-info-list__label">Price Range</span>
                                <span class="venue-profile-info-list__value"><?= $hasPrice ? e($venue['price_range']) : 'Not listed' ?></span>
                            </li>
                            <li class="venue-profile-info-list__item">
                                <span class="venue-profile-info-list__label">Reservations</span>
                                <span class="venue-profile-info-list__value">Check with venue</span>
                            </li>
                            <li class="venue-profile-info-list__item">
                                <span class="venue-profile-info-list__label">Brunch Hours</span>
                                <span class="venue-profile-info-list__value"><?= $hasHours ? e($venue['brunch_hours_note']) : 'Not listed' ?></span>
                            </li>
                            <li class="venue-profile-info-list__item">
                                <span class="venue-profile-info-list__label">Family Friendly</span>
                                <span class="venue-profile-info-list__value">Coming soon</span>
                            </li>
                        </ul>
                    </article>

                    <!-- 2. Upcoming Events (sidebar summary) -->
                    <article class="venue-profile-panel venue-profile-panel--note">
                        <h2 class="venue-profile-panel__title">Upcoming Events</h2>
                        <p class="venue-profile-note__text">No upcoming events listed yet.</p>
                        <p class="venue-profile-note__text venue-profile-note__text--muted">
                            Check back soon for brunch events at this location.
                        </p>
                    </article>

                    <!-- 3. Advertisement placeholder -->
                    <div class="venue-profile-panel ad-placeholder">
                        <span class="ad-placeholder__label">Advertisement</span>
                        <span class="ad-placeholder__size">300 &times; 250</span>
                    </div>

                </aside>
            </div>
        </div>
    </section>
</main>
<?php
require APP_ROOT . '/views/partials/footer.php';

// Progressive enhancement scripts (this page only).
// venue-tabs.js adds the tab component to the main content area.
// venue-menu.js adds AJAX allergen filtering inside the Menu tab — both
// work independently and degrade gracefully without JS.
?>
<script src="<?= e(asset_url('assets/js/venue-tabs.js')) ?>"></script>
<script src="<?= e(asset_url('assets/js/venue-menu.js')) ?>"></script>