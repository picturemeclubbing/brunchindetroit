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
    <div class="venue-detail-page">
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
    </div>
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
$directionsUrl = $addressLine !== ''
    ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($addressLine)
    : '';
// Human-friendly last-updated date.
$updatedFormatted = $hasUpdated ? date('F j, Y', strtotime((string) $venue['updated_at'])) : '';

// Visual placeholder images for the Interior Gallery and Recent Event
// Galleries sections. These REUSE image URLs already present on the home
// page and DeepSite reference designs so the profile page feels populated
// before the admin-managed gallery feature is built. They are decorative
// placeholders only - not real venue data - and involve no DB queries.
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

$venueInteriorImageRows = isset($venueInteriorImages) && is_array($venueInteriorImages)
    ? $venueInteriorImages
    : [];

foreach ($venueInteriorImageRows as $imageIndex => $imageRow) {
    if ($imageIndex >= count($galleryImages) || !is_array($imageRow)) {
        break;
    }

    $interiorImagePath = trim((string) ($imageRow['file_path'] ?? ''));
    if ($interiorImagePath === '') {
        continue;
    }

    $galleryImages[$imageIndex] = str_starts_with($interiorImagePath, 'http')
        ? $interiorImagePath
        : asset_url($interiorImagePath);
}
// Default large interior image when the venue has no main_image_path
// (same Unsplash photo used for The Garden Rooftop on the home page slider).
$defaultInteriorImage = 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1074&q=80';
// Image for the recent event gallery cards (same photo used by the home
// page gallery cards for Sunday Jazz Brunch / The Grand Brunch House).
$eventGalleryImage = 'https://images.unsplash.com/photo-1551218808-94e220e084d2?auto=format&fit=crop&w=1074&q=80';




$venueGalleryFilterUrl = asset_url('gallery.php?location=' . urlencode((string) ($venue['name'] ?? '')));
$recentGallery = isset($recentVenueGallery) && is_array($recentVenueGallery) ? $recentVenueGallery : null;
$recentVenueGalleryList = isset($recentVenueGalleries) && is_array($recentVenueGalleries) ? $recentVenueGalleries : [];
$recentGalleryImage = !empty($recentGallery['cover_image_path'])
    ? (string) $recentGallery['cover_image_path']
    : $eventGalleryImage;
$recentGalleryTitle = !empty($recentGallery['title'])
    ? (string) $recentGallery['title']
    : 'Recent Gallery';
$recentGalleryDate = !empty($recentGallery['event_date'])
    ? date('F j, Y', strtotime((string) $recentGallery['event_date']))
    : 'Coming soon';
$recentGalleryText = !empty($recentGallery['description'])
    ? (string) $recentGallery['description']
    : 'Recent venue galleries will appear here once they are published.';
$recentGalleryUrl = !empty($recentGallery['gallery_url'])
    ? (string) $recentGallery['gallery_url']
    : '';




$formatVenueHours = static function (?string $hours): string {
    $hours = trim((string) $hours);

    $genericValues = [
        'Breakfast and brunch service; check official site for current daily hours.',
        'Breakfast and brunch service; check official site for current daily hours',
        'Check official site for current daily hours.',
        'Check official site for current daily hours',
    ];

    if ($hours === '' || in_array($hours, $genericValues, true)) {
        return 'N/A';
    }

    return str_ireplace(
        ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
        ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        $hours
    );
};

$expandVenueHoursRows = static function (?string $hours) use ($formatVenueHours): array {
    $display = $formatVenueHours($hours);

    if ($display === 'N/A') {
        return [
            ['day' => 'Hours', 'hours' => 'N/A'],
        ];
    }

    $dayOrder = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $rows = [];

    $parts = preg_split('/\s*;\s*/', rtrim($display, ". \t\n\r\0\x0B"));

    foreach ($parts as $part) {
        $part = trim((string) $part);
        if ($part === '') {
            continue;
        }

        if (!preg_match('/^([A-Za-z]{3})(?:\s*-\s*([A-Za-z]{3}))?\s+(.+)$/', $part, $matches)) {
            $rows[] = ['day' => 'Hours', 'hours' => $part];
            continue;
        }

        $startDay = ucfirst(strtolower($matches[1]));
        $endDay = isset($matches[2]) && $matches[2] !== '' ? ucfirst(strtolower($matches[2])) : null;
        $timeText = trim($matches[3]);

        $startIndex = array_search($startDay, $dayOrder, true);
        $endIndex = $endDay !== null ? array_search($endDay, $dayOrder, true) : $startIndex;

        if ($startIndex === false || $endIndex === false) {
            $rows[] = ['day' => $startDay, 'hours' => $timeText];
            continue;
        }

        if ($endIndex < $startIndex) {
            $range = array_merge(
                array_slice($dayOrder, $startIndex),
                array_slice($dayOrder, 0, $endIndex + 1)
            );
        } else {
            $range = array_slice($dayOrder, $startIndex, $endIndex - $startIndex + 1);
        }

        foreach ($range as $day) {
            $rows[] = ['day' => $day, 'hours' => $timeText];
        }
    }

    return $rows !== []
        ? $rows
        : [['day' => 'Hours', 'hours' => 'N/A']];
};

$venueHoursDisplay = $formatVenueHours((string) ($venue['brunch_hours_note'] ?? ''));
$venueHeroBlurb = trim((string) ($venue['hero_blurb'] ?? ''));
if ($venueHeroBlurb === '') {
    $venueHeroBlurb = trim((string) ($venue['description'] ?? ''));
}
$venueHoursRows = $expandVenueHoursRows((string) ($venue['brunch_hours_note'] ?? ''));
require APP_ROOT . '/views/partials/header.php';
?>

<div class="venue-detail-page">
    <?php require APP_ROOT . '/views/partials/frontend-admin-bar.php'; ?>
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
                        <?php if ($hasHours): ?>
                            <p class="venue-profile-hero__hours">
                                <i class="fas fa-clock" aria-hidden="true"></i>
                                <?= e($venueHoursDisplay) ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($venueHeroBlurb !== ''): ?>
                            <p class="venue-profile-hero__description"><?= e($venueHeroBlurb) ?></p>
                        <?php endif; ?>
                        <div class="venue-profile-hero__actions">
                            <?php if ($directionsUrl !== ''): ?>
                                <a class="btn btn--primary" href="<?= e($directionsUrl) ?>" target="_blank" rel="noopener">
                                    <i class="fas fa-diamond-turn-right" aria-hidden="true"></i>
                                    Directions
                                </a>
                            <?php endif; ?>

                            <?php if ($hasPhone && $phoneTel !== ''): ?>
                                <a class="btn btn--accent" href="tel:<?= e($phoneTel) ?>">
                                    <i class="fas fa-phone" aria-hidden="true"></i>
                                    Call
                                </a>
                            <?php endif; ?>
                            <button
                                class="btn btn--outline-light venue-profile-rsvp-button"
                                type="button"
                                data-rsvp-trigger
                                data-rsvp-venue-slug="<?= e((string) ($venue['slug'] ?? '')) ?>"
                                data-rsvp-venue-id="<?= (int) ($venue['id'] ?? 0) ?>"
                                data-rsvp-venue-name="<?= e((string) ($venue['name'] ?? 'venue')) ?>"
                                data-rsvp-source="free_profile_hero"
                                aria-label="RSVP for <?= e((string) ($venue['name'] ?? 'venue')) ?>"
                            >
                                <i class="fas fa-calendar-check" aria-hidden="true"></i>
                                RSVP
                            </button>
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

                        <?php if ($directionsUrl !== ''): ?>
                            <a class="btn btn--primary btn--block venue-profile-contact__directions" href="<?= e($directionsUrl) ?>" target="_blank" rel="noopener">
                                <i class="fas fa-diamond-turn-right" aria-hidden="true"></i>
                                Get directions
                            </a>
                        <?php endif; ?>
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
                    <span class="venue-profile-strip__label">Phone</span>
                    <span class="venue-profile-strip__value"><?= $hasPhone ? e($venue['phone']) : '&mdash;' ?></span>
                </div>
                <div class="venue-profile-strip__item">
                    <span class="venue-profile-strip__label">Price Range</span>
                    <span class="venue-profile-strip__value"><?= $hasPrice ? e($venue['price_range']) : 'N/A' ?></span>
                </div>
                <div class="venue-profile-strip__item">
                    <span class="venue-profile-strip__label">Last Updated</span>
                    <span class="venue-profile-strip__value"><?= $updatedFormatted !== '' ? e($updatedFormatted) : '&mdash;' ?></span>
                </div>
            </div>

            <!-- D & E. Main content (tabbed) + sidebar -->
            <div class="venue-profile-layout">

                <!-- D. Main content column - now using a tab component -->
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
                                <span>Event Galleries</span>
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
                                <span class="venue-profile-panel__eyebrow">The place</span>
                                <h2 class="venue-profile-panel__title">About</h2>
                                <?php if (!empty($venue['description'])): ?>
                                    <p class="venue-profile-about__text"><?= e($venue['description']) ?></p>
                                <?php else: ?>
                                    <p class="venue-profile-about__text venue-profile-about__text--muted">
                                        No description has been added for this venue yet.
                                    </p>
                                <?php endif; ?>
                            </article>

                            <article class="venue-profile-panel venue-profile-interior-gallery-panel">
                                <div class="venue-profile-panel__header">
                                    <div>
                                        <span class="venue-profile-panel__eyebrow">Interior shots</span>
                                        <h2 class="venue-profile-panel__title">Inside <?= e($venue['name']) ?></h2>
                                    </div>
                                </div>

                                <div class="venue-gallery-grid">
                                    <button
                                        type="button"
                                        class="venue-gallery-grid__large venue-gallery-grid__lightbox-button"
                                        data-photo-src="<?= e($hasImage ? $imageUrl : $defaultInteriorImage) ?>"
                                        data-photo-alt="Interior preview of <?= e($venue['name']) ?>"
                                    >
                                        <img
                                            src="<?= e($hasImage ? $imageUrl : $defaultInteriorImage) ?>"
                                            alt="Interior preview of <?= e($venue['name']) ?>"
                                            loading="lazy"
                                        >
                                    </button>

                                    <button
                                        type="button"
                                        class="venue-gallery-grid__small venue-gallery-grid__lightbox-button"
                                        data-photo-src="<?= e($galleryImages[0]) ?>"
                                        data-photo-alt="Brunch atmosphere preview"
                                    >
                                        <img
                                            src="<?= e($galleryImages[0]) ?>"
                                            alt="Brunch atmosphere preview"
                                            loading="lazy"
                                        >
                                    </button>

                                    <button
                                        type="button"
                                        class="venue-gallery-grid__small venue-gallery-grid__lightbox-button"
                                        data-photo-src="<?= e($galleryImages[1]) ?>"
                                        data-photo-alt="Brunch dining preview"
                                    >
                                        <img
                                            src="<?= e($galleryImages[1]) ?>"
                                            alt="Brunch dining preview"
                                            loading="lazy"
                                        >
                                    </button>

                                    <button
                                        type="button"
                                        class="venue-gallery-grid__small venue-gallery-grid__lightbox-button"
                                        data-photo-src="<?= e($galleryImages[2]) ?>"
                                        data-photo-alt="Brunch dishes preview"
                                    >
                                        <img
                                            src="<?= e($galleryImages[2]) ?>"
                                            alt="Brunch dishes preview"
                                            loading="lazy"
                                        >
                                    </button>

                                    <button
                                        type="button"
                                        class="venue-gallery-grid__small venue-gallery-grid__lightbox-button"
                                        data-photo-src="<?= e($galleryImages[3]) ?>"
                                        data-photo-alt="Brunch pastry preview"
                                    >
                                        <img
                                            src="<?= e($galleryImages[3]) ?>"
                                            alt="Brunch pastry preview"
                                            loading="lazy"
                                        >
                                    </button>
                                </div>

                                <div class="venue-photo-lightbox" aria-hidden="true" role="dialog" aria-label="Expanded venue photo">
                                    <button type="button" class="venue-photo-lightbox__close" aria-label="Close expanded photo">
                                        <i class="fas fa-xmark" aria-hidden="true"></i>
                                    </button>
                                    <img class="venue-photo-lightbox__image" src="" alt="">
                                </div>
                            </article>

                            <article class="venue-profile-panel venue-profile-placeholder">
                                <h2 class="venue-profile-panel__title">What to Know</h2>
                                <p class="venue-profile-placeholder__text">
                                    Brunch hours: <?= e($venueHoursDisplay) ?>.
                                    <?php if ($hasNeighborhood): ?>
                                        Located in the <?= e($venue['neighborhood_name']) ?> neighborhood.
                                    <?php endif; ?>
                                    Details are curated by BrunchInDetroit &mdash; please confirm hours and
                                    menus directly with the venue before visiting.
                                </p>
                            </article>
                        </div>

                        <!-- Panel 2: Event Galleries (photo shoots at this location) -->
                        <div class="venue-tabs__panel" role="tabpanel"
                             id="venue-tabpanel-photos"
                             aria-labelledby="venue-tab-photos"
                             data-venue-tab-panel="photos">

                            <article class="venue-profile-panel venue-profile-event-galleries-panel">
                                <div class="venue-profile-panel__header">
                                    <div>
                                        <h2 class="venue-profile-panel__title"><?= e($venue['name']) ?> Event Galleries</h2>
                                    </div>
                                    <a class="venue-profile-panel__link" href="<?= e($venueGalleryFilterUrl) ?>">
                                        View all from this venue
                                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                                    </a>
                                </div>

                                <?php if (!empty($recentVenueGalleryList)): ?>
                                    <div class="venue-event-gallery-list">
                                        <?php foreach ($recentVenueGalleryList as $venueGallery): ?>
                                            <?php
                                            $venueGalleryImage = !empty($venueGallery['cover_image_path'])
                                                ? (string) $venueGallery['cover_image_path']
                                                : $eventGalleryImage;
                                            $venueGalleryTitle = !empty($venueGallery['title'])
                                                ? (string) $venueGallery['title']
                                                : 'Recent Gallery';
                                            $venueGalleryDate = !empty($venueGallery['event_date'])
                                                ? date('F j, Y', strtotime((string) $venueGallery['event_date']))
                                                : 'Coming soon';
                                            $venueGalleryText = !empty($venueGallery['description'])
                                                ? (string) $venueGallery['description']
                                                : 'Recent venue galleries will appear here once they are published.';
                                            $venueGalleryUrl = !empty($venueGallery['gallery_url'])
                                                ? (string) $venueGallery['gallery_url']
                                                : '';
                                            ?>
                                            <article class="venue-event-gallery-card">
                                                <img
                                                    class="venue-event-gallery-card__image"
                                                    src="<?= e($venueGalleryImage) ?>"
                                                    alt="<?= e($venueGalleryTitle) ?> preview"
                                                    loading="lazy"
                                                >

                                                <div class="venue-event-gallery-card__body">
                                                    <p class="venue-event-gallery-card__date">
                                                        <i class="fas fa-calendar" aria-hidden="true"></i>
                                                        <?= e($venueGalleryDate) ?>
                                                    </p>

                                                    <h3 class="venue-event-gallery-card__title"><?= e($venueGalleryTitle) ?></h3>
                                                    <p class="venue-event-gallery-card__text"><?= e($venueGalleryText) ?></p>

                                                    <?php if ($venueGalleryUrl !== ''): ?>
                                                        <a class="btn btn--primary venue-event-gallery-card__button" href="<?= e($venueGalleryUrl) ?>" target="_blank" rel="noopener">
                                                            <i class="fas fa-images" aria-hidden="true"></i>
                                                            View Gallery
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="venue-event-gallery-card__action">
                                                            <i class="fas fa-clock" aria-hidden="true"></i>
                                                            Gallery Coming Soon
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="venue-profile-events-empty">
                                        <i class="fas fa-images" aria-hidden="true"></i>
                                        <p class="venue-profile-note__text">No event galleries listed yet.</p>
                                        <p class="venue-profile-note__text venue-profile-note__text--muted">
                                            Check back after this location has published event galleries.
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <p class="venue-profile-gallery__note">
                                    <i class="fas fa-camera" aria-hidden="true"></i>
                                    This tab shows event galleries photographed at this venue. Use View all from this venue for the full filtered gallery page.
                                </p>
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
                            // replacement target for venue-menu.js - it must stay here.
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
                    <?php if (!empty($nearbyVenues) && is_array($nearbyVenues)): ?>
                        <article class="venue-profile-panel venue-profile-nearby">
                            <div class="venue-profile-panel__header">
                                <div>
                                    <span class="venue-profile-panel__eyebrow">More nearby</span>
                                    <h2 class="venue-profile-panel__title">Nearby Brunch Spots</h2>
                                </div>
                                <a class="venue-profile-panel__link" href="<?= e(asset_url('directory.php')) ?>">
                                    View directory
                                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            </div>

                            <div class="venue-profile-nearby__grid">
                                <?php foreach ($nearbyVenues as $nearbyVenue): ?>
                                    <?php
                                    $nearbyImagePath = trim((string) ($nearbyVenue['main_image_path'] ?? ''));
                                    if ($nearbyImagePath !== '') {
                                        $nearbyImage = (str_starts_with($nearbyImagePath, 'http') || str_starts_with($nearbyImagePath, '/'))
                                            ? $nearbyImagePath
                                            : asset_url($nearbyImagePath);
                                    } else {
                                        $nearbyImage = 'https://images.unsplash.com/photo-1559847844-5315695dadae?auto=format&fit=crop&w=1200&q=80';
                                    }

                                    $nearbyUrl = asset_url('venue.php?slug=' . urlencode((string) ($nearbyVenue['slug'] ?? '')));
                                    $nearbyArea = trim((string) ($nearbyVenue['neighborhood_name'] ?? ''));
                                    $nearbyHours = $formatVenueHours((string) ($nearbyVenue['brunch_hours_note'] ?? ''));
                                    ?>
                                    <a class="venue-profile-nearby-card" href="<?= e($nearbyUrl) ?>">
                                        <span class="venue-profile-nearby-card__media">
                                            <img
                                                src="<?= e($nearbyImage) ?>"
                                                alt="<?= e((string) ($nearbyVenue['name'] ?? 'Nearby brunch spot')) ?>"
                                                loading="lazy"
                                            >
                                            <?php if (!empty($nearbyVenue['is_featured'])): ?>
                                                <span class="venue-profile-nearby-card__badge">Featured</span>
                                            <?php endif; ?>
                                        </span>

                                        <span class="venue-profile-nearby-card__body">
                                            <?php if ($nearbyArea !== ''): ?>
                                                <span class="venue-profile-nearby-card__eyebrow"><?= e($nearbyArea) ?></span>
                                            <?php endif; ?>

                                            <strong class="venue-profile-nearby-card__title">
                                                <?= e((string) ($nearbyVenue['name'] ?? 'Nearby brunch spot')) ?>
                                            </strong>

                                            <span class="venue-profile-nearby-card__meta">
                                                <i class="fas fa-clock" aria-hidden="true"></i>
                                                <?= e($nearbyHours) ?>
                                            </span>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </div>

                            <p class="venue-profile-nearby__note">
                                <i class="fas fa-circle-info" aria-hidden="true"></i>
                                More places to check out near this location.
                            </p>
                        </article>
                    <?php endif; ?>

                </div>

                <!-- E. Sidebar column -->
                <aside class="venue-profile-sidebar">
                    <!-- Brunch Hours -->
                    <article class="venue-profile-panel venue-profile-hours-card">
                        <h2 class="venue-profile-panel__title">Brunch Hours</h2>

                        <dl class="venue-profile-hours-list">
                            <?php foreach ($venueHoursRows as $hoursRow): ?>
                                <div class="venue-profile-hours-list__row">
                                    <dt><?= e((string) ($hoursRow['day'] ?? 'Hours')) ?></dt>
                                    <dd><?= e((string) ($hoursRow['hours'] ?? 'N/A')) ?></dd>
                                </div>
                            <?php endforeach; ?>
                        </dl>

                        <p class="venue-profile-hours-card__note">Brunch hours may vary.</p>
                    </article>
                    <!-- 1. Recent Gallery -->
                    <article class="venue-profile-panel venue-profile-recent-gallery">
                        <h2 class="venue-profile-panel__title">Recent Gallery</h2>

                        <article class="venue-event-gallery-card venue-event-gallery-card--single">
                            <img
                                class="venue-event-gallery-card__image"
                                src="<?= e($recentGalleryImage) ?>"
                                alt="<?= e($recentGalleryTitle) ?> preview"
                                loading="lazy"
                            >

                            <div class="venue-event-gallery-card__body">
                                <p class="venue-event-gallery-card__date">
                                    <i class="fas fa-calendar" aria-hidden="true"></i>
                                    <?= e($recentGalleryDate) ?>
                                </p>

                                <h3 class="venue-event-gallery-card__title"><?= e($recentGalleryTitle) ?></h3>

                                <p class="venue-event-gallery-card__text"><?= e($recentGalleryText) ?></p>

                                <?php if ($recentGalleryUrl !== ''): ?>
                                    <a class="btn btn--primary venue-profile-recent-gallery__primary" href="<?= e($recentGalleryUrl) ?>" target="_blank" rel="noopener">
                                        <i class="fas fa-images" aria-hidden="true"></i>
                                        View Recent Gallery
                                    </a>
                                <?php else: ?>
                                    <span class="btn btn--primary venue-profile-recent-gallery__primary is-disabled" aria-disabled="true">
                                        <i class="fas fa-clock" aria-hidden="true"></i>
                                        Recent Gallery Coming Soon
                                    </span>
                                <?php endif; ?>

                                <a class="venue-profile-recent-gallery__all-link" href="<?= e($venueGalleryFilterUrl) ?>">
                                    View all galleries from this venue
                                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            </div>
                        </article>
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
</div>
<?php
require APP_ROOT . '/views/partials/footer.php';

// Progressive enhancement scripts (this page only).
// venue-tabs.js adds the tab component to the main content area.
// venue-menu.js adds AJAX allergen filtering inside the Menu tab - both
// work independently and degrade gracefully without JS.
?>
<script src="<?= e(asset_url('assets/js/venue-tabs.js')) ?>"></script>
<script src="<?= e(asset_url('assets/js/venue-menu.js')) ?>"></script>

<script>
(() => {
    const lightbox = document.querySelector('.venue-photo-lightbox');
    if (!lightbox) return;

    const lightboxImage = lightbox.querySelector('.venue-photo-lightbox__image');
    const closeButton = lightbox.querySelector('.venue-photo-lightbox__close');
    const triggers = document.querySelectorAll('.venue-gallery-grid__lightbox-button');

    const closeLightbox = () => {
        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
        lightboxImage.setAttribute('src', '');
        lightboxImage.setAttribute('alt', '');
        document.body.classList.remove('venue-photo-lightbox-open');
    };

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const src = trigger.getAttribute('data-photo-src') || '';
            const alt = trigger.getAttribute('data-photo-alt') || 'Expanded venue photo';

            if (!src) return;

            lightboxImage.setAttribute('src', src);
            lightboxImage.setAttribute('alt', alt);
            lightbox.classList.add('is-open');
            lightbox.setAttribute('aria-hidden', 'false');
            document.body.classList.add('venue-photo-lightbox-open');
            closeButton.focus();
        });
    });

    closeButton.addEventListener('click', closeLightbox);

    lightbox.addEventListener('click', (event) => {
        if (event.target === lightbox) {
            closeLightbox();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && lightbox.classList.contains('is-open')) {
            closeLightbox();
        }
    });
})();
</script>
