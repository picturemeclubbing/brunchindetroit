<?php
declare(strict_types=1);

/**
 * Premium / Website Mode venue profile.
 *
 * Separate from the free tabbed profile so the directory profile remains stable.
 *
 * @var array|null $venue
 */

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

$hasNeighborhood = !empty($venue['neighborhood_name']);
$hasPrice        = !empty($venue['price_range']);
$hasHours        = !empty($venue['brunch_hours_note']);
$hasPhone        = !empty($venue['phone']);
$hasWebsite      = !empty($venue['website_url']);
$hasInstagram    = !empty($venue['instagram_url']);
$hasFacebook     = !empty($venue['facebook_url']);

$addressParts = array_filter([
    $venue['address_line1'] ?? '',
    $venue['address_line2'] ?? '',
    trim((($venue['city'] ?? '') . ' ' . ($venue['state'] ?? '') . ' ' . ($venue['zip'] ?? ''))),
], static fn ($v) => trim((string) $v) !== '');
$addressLine = implode(', ', $addressParts);

$hasImage = !empty($venue['main_image_path']);
$imageUrl = '';
if ($hasImage) {
    $imgPath = (string) $venue['main_image_path'];
    $imageUrl = str_starts_with($imgPath, 'http') ? $imgPath : asset_url($imgPath);
}

$phoneTel = $hasPhone ? preg_replace('/[^0-9+]/', '', (string) $venue['phone']) : '';
$directionsUrl = $addressLine !== ''
    ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($addressLine)
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

        $premiumNormalizedHoursPart = preg_replace(
            '/^([A-Za-z]{3})\s+and\s+([A-Za-z]{3}),?\s+(.+)$/i',
            '$1-$2 $3',
            $part
        ) ?? $part;

        $premiumNormalizedHoursPart = preg_replace(
            '/^([A-Za-z]{3}),\s+(.+)$/i',
            '$1 $2',
            $premiumNormalizedHoursPart
        ) ?? $premiumNormalizedHoursPart;

        if (!preg_match('/^([A-Za-z]{3})(?:\s*-\s*([A-Za-z]{3}))?\s+(.+)$/', $premiumNormalizedHoursPart, $matches)) {
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

        $range = $endIndex < $startIndex
            ? array_merge(array_slice($dayOrder, $startIndex), array_slice($dayOrder, 0, $endIndex + 1))
            : array_slice($dayOrder, $startIndex, $endIndex - $startIndex + 1);

        foreach ($range as $day) {
            $rows[] = ['day' => $day, 'hours' => $timeText];
        }
    }

    return $rows !== []
        ? $rows
        : [['day' => 'Hours', 'hours' => 'N/A']];
};

$venueHoursDisplay = $formatVenueHours((string) ($venue['brunch_hours_note'] ?? ''));
$premiumGlanceHours = str_ireplace([' and ', ', '], [' & ', ' '], $venueHoursDisplay);
$venueHoursRows = $expandVenueHoursRows((string) ($venue['brunch_hours_note'] ?? ''));

$venueHeroBlurb = trim((string) ($venue['hero_blurb'] ?? ''));
if ($venueHeroBlurb === '') {
    $venueHeroBlurb = trim((string) ($venue['description'] ?? ''));
}

// Single reusable stock fallback. Used only as a last resort when a venue
// has no real photos of its own anywhere (hero image or interior gallery).
$premiumStockFallbackImage = 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1600&q=80';

$premiumHeroImage = $hasImage ? $imageUrl : $premiumStockFallbackImage;

// About-section photo collage: prefer the venue's own main image plus any
// admin-uploaded interior photos. Stock photography is only used when the
// venue has no real images at all.
$venueInteriorImageRows = isset($venueInteriorImages) && is_array($venueInteriorImages)
    ? $venueInteriorImages
    : [];

$premiumAboutPhotos = [];

if ($hasImage) {
    $premiumAboutPhotos[] = $imageUrl;
}

foreach ($venueInteriorImageRows as $imageRow) {
    if (!is_array($imageRow)) {
        continue;
    }

    $interiorImagePath = trim((string) ($imageRow['file_path'] ?? ''));
    if ($interiorImagePath === '') {
        continue;
    }

    $premiumAboutPhotos[] = str_starts_with($interiorImagePath, 'http')
        ? $interiorImagePath
        : asset_url($interiorImagePath);
}

$premiumAboutPhotos = array_values(array_unique(array_filter($premiumAboutPhotos)));

if ($premiumAboutPhotos === []) {
    $premiumAboutPhotos[] = $premiumStockFallbackImage;
}

$premiumPhotos = $premiumAboutPhotos;

$premiumMenuPreviewItems = [];
$premiumMenuCategoryRows = isset($menuCategories) && is_array($menuCategories) ? $menuCategories : [];

foreach ($premiumMenuCategoryRows as $premiumMenuCategoryRow) {
    if (!is_array($premiumMenuCategoryRow)) {
        continue;
    }

    $premiumMenuRows = isset($premiumMenuCategoryRow['items']) && is_array($premiumMenuCategoryRow['items'])
        ? $premiumMenuCategoryRow['items']
        : [];

    foreach ($premiumMenuRows as $premiumMenuRow) {
        if (!is_array($premiumMenuRow)) {
            continue;
        }

        $menuImagePath = trim((string) ($premiumMenuRow['image_url'] ?? ''));
        if ($menuImagePath === '') {
            continue;
        }

        $premiumMenuPreviewItems[] = [
            'name' => (string) ($premiumMenuRow['name'] ?? 'Menu item'),
            'image' => str_starts_with($menuImagePath, 'http') || str_starts_with($menuImagePath, '/')
                ? $menuImagePath
                : asset_url($menuImagePath),
            'alt' => trim((string) ($premiumMenuRow['image_alt_text'] ?? '')) !== ''
                ? (string) $premiumMenuRow['image_alt_text']
                : (string) ($premiumMenuRow['name'] ?? 'Menu item'),
        ];

        if (count($premiumMenuPreviewItems) >= 3) {
            break 2;
        }
    }
}

// If fewer than 3 published items have photos, fill the rest of the preview
// with polished text-only cards (name / price / description) rather than
// leaving the preview sparse or padding it with unrelated stock photos.
$premiumMenuFallbackItems = [];

if (count($premiumMenuPreviewItems) < 3) {
    foreach ($premiumMenuCategoryRows as $premiumMenuCategoryRow) {
        if (!is_array($premiumMenuCategoryRow)) {
            continue;
        }

        $premiumMenuRows = isset($premiumMenuCategoryRow['items']) && is_array($premiumMenuCategoryRow['items'])
            ? $premiumMenuCategoryRow['items']
            : [];

        foreach ($premiumMenuRows as $premiumMenuRow) {
            if (!is_array($premiumMenuRow)) {
                continue;
            }

            // Items with photos are already represented above; skip them here.
            if (trim((string) ($premiumMenuRow['image_url'] ?? '')) !== '') {
                continue;
            }

            $fallbackName = trim((string) ($premiumMenuRow['name'] ?? ''));
            if ($fallbackName === '') {
                continue;
            }

            $rawPrice = $premiumMenuRow['price'] ?? null;

            $premiumMenuFallbackItems[] = [
                'name'        => $fallbackName,
                'price'       => ($rawPrice !== null && $rawPrice !== '') ? '$' . number_format((float) $rawPrice, 2) : '',
                'description' => trim((string) ($premiumMenuRow['description'] ?? '')),
            ];

            if ((count($premiumMenuPreviewItems) + count($premiumMenuFallbackItems)) >= 3) {
                break 2;
            }
        }
    }
}

$venueGalleryFilterUrl = asset_url('gallery.php?location=' . urlencode((string) ($venue['name'] ?? '')));
$recentVenueGalleryList = isset($recentVenueGalleries) && is_array($recentVenueGalleries) ? $recentVenueGalleries : [];

$pageTitle = ($venue['name'] ?? 'Venue') . ' - Premium Profile';
require APP_ROOT . '/views/partials/header.php';
?>

<div class="premium-venue-page">
    <?php require APP_ROOT . '/views/partials/frontend-admin-bar.php'; ?>

    <div class="premium-venue-bar">
        <div class="container premium-venue-bar__inner">
            <a class="premium-venue-bar__back" href="<?= e(asset_url('directory.php')) ?>">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Back to Directory
            </a>

        </div>
    </div>

    <header class="premium-venue-hero" style="background-image: url('<?= e($premiumHeroImage) ?>');">
        <div class="container premium-venue-hero__inner">
            <?php if (!empty($venue['is_featured'])): ?>
                <span class="premium-venue-hero__badge">Featured</span>
            <?php endif; ?>

            <p class="premium-venue-hero__eyebrow">
                <?= $hasNeighborhood ? e($venue['neighborhood_name']) : 'Detroit' ?>
                <?php if ($hasPrice): ?>
                    <span aria-hidden="true">·</span> <?= e($venue['price_range']) ?>
                <?php endif; ?>
                <span aria-hidden="true">·</span> Brunch
            </p>

            <h1 class="premium-venue-hero__title"><?= e((string) ($venue['name'] ?? 'Venue')) ?></h1>

            <?php if ($venueHeroBlurb !== ''): ?>
                <p class="premium-venue-hero__tagline"><?= e($venueHeroBlurb) ?></p>
            <?php endif; ?>

            <div class="premium-venue-hero__facts">
                <span>
                    <i class="fas fa-clock" aria-hidden="true"></i>
                    <?= e($venueHoursDisplay) ?>
                </span>
                <?php if ($addressLine !== ''): ?>
                    <a href="#premium-visit">
                        <i class="fas fa-location-dot" aria-hidden="true"></i>
                        <?= e($addressLine) ?>
                    </a>
                <?php endif; ?>
            </div>

            <div class="premium-venue-hero__actions">
                <button
                    class="btn premium-venue-hero__cta premium-venue-hero__cta--rsvp"
                    type="button"
                    data-rsvp-trigger
                    data-rsvp-venue-slug="<?= e((string) ($venue['slug'] ?? '')) ?>"
                    data-rsvp-venue-id="<?= (int) ($venue['id'] ?? 0) ?>"
                    data-rsvp-venue-name="<?= e((string) ($venue['name'] ?? 'venue')) ?>"
                    data-rsvp-source="premium_hero"
                >
                    <i class="fas fa-calendar-check" aria-hidden="true"></i>
                    RSVP
                </button>

                <?php if ($directionsUrl !== ''): ?>
                    <a class="btn premium-venue-hero__cta premium-venue-hero__cta--directions" href="<?= e($directionsUrl) ?>" target="_blank" rel="noopener">
                        <i class="fas fa-diamond-turn-right" aria-hidden="true"></i>
                        Get directions
                    </a>
                <?php endif; ?>

                <?php if ($hasPhone && $phoneTel !== ''): ?>
                    <a class="btn premium-venue-hero__cta premium-venue-hero__cta--call" href="tel:<?= e($phoneTel) ?>">
                        <i class="fas fa-phone" aria-hidden="true"></i>
                        Call
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>
<nav class="premium-venue-subnav" aria-label="Premium venue sections">
        <div class="container premium-venue-subnav__inner">
            <a href="#premium-about">About</a>
            <a href="#premium-menu">Menu</a>
            <a href="#premium-photos">Photos</a>
            <a href="#premium-events">Events</a>
            <a href="#premium-visit">Visit</a>
        </div>
    </nav>

<section class="premium-venue-glance" aria-label="Venue quick facts">
        <div class="container premium-venue-glance__grid">
            <div class="premium-venue-glance__item">
                <i class="fas fa-building" aria-hidden="true"></i>
                <span>Neighborhood</span>
                <strong><?= $hasNeighborhood ? e((string) $venue['neighborhood_name']) : 'Detroit' ?></strong>
            </div>
            <div class="premium-venue-glance__item">
                <i class="fas fa-dollar-sign" aria-hidden="true"></i>
                <span>Price Range</span>
                <strong><?= $hasPrice ? e((string) $venue['price_range']) : 'N/A' ?></strong>
            </div>
            <div class="premium-venue-glance__item">
                <i class="fas fa-clock" aria-hidden="true"></i>
                <span>Brunch Hours</span>
                <strong><?= e($premiumGlanceHours) ?></strong>
            </div>
            <div class="premium-venue-glance__item">
                <i class="fas fa-phone" aria-hidden="true"></i>
                <span>Phone</span>
                <strong><?= $hasPhone ? e((string) $venue['phone']) : 'N/A' ?></strong>
            </div>
        </div>
    </section>

    <section class="premium-venue-section" id="premium-about">
        <div class="container">
            <?php
            $premiumAboutDisplayPhotos = array_slice($premiumPhotos, 0, 4);
            $premiumAboutPhotoCount = count($premiumAboutDisplayPhotos);
            $premiumAboutMainPhoto = $premiumAboutDisplayPhotos[0] ?? '';
            $premiumAboutThumbPhotos = array_slice($premiumAboutDisplayPhotos, 1);
            ?>
            <div class="premium-venue-about__grid">
                <div class="premium-venue-about__copy">
                    <span class="premium-venue-section__eyebrow">The place</span>
                    <h2 class="premium-venue-section__title">About <?= e((string) ($venue['name'] ?? 'this venue')) ?></h2>
                    <?php if (!empty($venue['description'])): ?>
                        <p class="premium-venue-section__lead"><?= e((string) $venue['description']) ?></p>
                    <?php else: ?>
                        <p class="premium-venue-section__lead premium-venue-section__lead--muted">
                            More details about this venue are coming soon.
                        </p>
                    <?php endif; ?>
                </div>

                <?php if ($premiumAboutPhotoCount > 0): ?>
                    <div class="premium-venue-about__gallery">
                        <div class="premium-about-gallery" data-photo-count="<?= $premiumAboutPhotoCount ?>">
                            <button
                                type="button"
                                class="premium-about-gallery__main"
                                data-photo-src="<?= e($premiumAboutMainPhoto) ?>"
                                data-photo-alt="<?= e((string) ($venue['name'] ?? 'Venue')) ?> photo 1"
                                data-lightbox-trigger
                            >
                                <img src="<?= e($premiumAboutMainPhoto) ?>" alt="<?= e((string) ($venue['name'] ?? 'Venue')) ?> photo 1" loading="lazy">
                            </button>

                            <?php if ($premiumAboutThumbPhotos !== []): ?>
                                <div class="premium-about-gallery__thumbs">
                                    <?php foreach ($premiumAboutThumbPhotos as $photoIndex => $photoUrl): ?>
                                        <button
                                            type="button"
                                            class="premium-about-gallery__thumb"
                                            data-photo-src="<?= e($photoUrl) ?>"
                                            data-photo-alt="<?= e((string) ($venue['name'] ?? 'Venue')) ?> photo <?= (int) $photoIndex + 2 ?>"
                                            data-lightbox-trigger
                                        >
                                            <img src="<?= e($photoUrl) ?>" alt="<?= e((string) ($venue['name'] ?? 'Venue')) ?> photo <?= (int) $photoIndex + 2 ?>" loading="lazy">
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="premium-venue-section premium-venue-menu-preview" id="premium-menu">
        <div class="container premium-venue-menu-preview__inner">
            <div class="premium-venue-menu-preview__copy">
                <span class="premium-venue-section__eyebrow">On the menu</span>
                <h2 class="premium-venue-section__title">Brunch Menu</h2>
                <p>
                    From signature plates to brunch classics, the menu is made for good food and good company.
                </p>
                <a class="btn btn--primary" href="#premium-menu-full">
                    View full menu
                </a>
            </div>

            <div class="premium-venue-menu-preview__photos" aria-label="Featured menu items">
                <?php if (!empty($premiumMenuPreviewItems) || !empty($premiumMenuFallbackItems)): ?>
                    <?php foreach ($premiumMenuPreviewItems as $premiumMenuPreviewItem): ?>
                        <figure class="premium-venue-menu-preview__photo">
                            <img
                                src="<?= e((string) $premiumMenuPreviewItem['image']) ?>"
                                alt="<?= e((string) $premiumMenuPreviewItem['alt']) ?>"
                                loading="lazy"
                            >
                        </figure>
                    <?php endforeach; ?>
                    <?php foreach ($premiumMenuFallbackItems as $premiumMenuFallbackItem): ?>
                        <div class="premium-venue-menu-preview__card">
                            <strong class="premium-venue-menu-preview__card-name"><?= e($premiumMenuFallbackItem['name']) ?></strong>
                            <?php if ($premiumMenuFallbackItem['price'] !== ''): ?>
                                <span class="premium-venue-menu-preview__card-price"><?= e($premiumMenuFallbackItem['price']) ?></span>
                            <?php endif; ?>
                            <?php if ($premiumMenuFallbackItem['description'] !== ''): ?>
                                <p class="premium-venue-menu-preview__card-desc"><?= e($premiumMenuFallbackItem['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="premium-venue-menu-preview__empty">
                        <i class="fas fa-utensils" aria-hidden="true"></i>
                        <span>Menu photos coming soon.</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="container premium-venue-menu-preview__full" id="premium-menu-full">
            <?php require APP_ROOT . '/views/partials/venue-menu-section.php'; ?>
        </div>
    </section>

    <section class="premium-venue-section" id="premium-photos">
        <div class="container">
            <div class="premium-venue-section__header">
                <div>
                    <span class="premium-venue-section__eyebrow">Shot at this location</span>
                    <h2 class="premium-venue-section__title">Event Photos</h2>
                </div>
                <a class="premium-venue-section__link" href="<?= e($venueGalleryFilterUrl) ?>">
                    View all galleries
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>

            <?php if (!empty($recentVenueGalleryList)): ?>
                <div class="premium-venue-event-grid">
                    <?php foreach ($recentVenueGalleryList as $venueGallery): ?>
                        <?php
                        $venueGalleryCoverImage = trim((string) ($venueGallery['cover_image_path'] ?? ''));
                        $venueGalleryTitle = !empty($venueGallery['title'])
                            ? (string) $venueGallery['title']
                            : 'Recent Gallery';
                        $venueGalleryDate = !empty($venueGallery['event_date'])
                            ? date('F j, Y', strtotime((string) $venueGallery['event_date']))
                            : 'Coming soon';
                        $venueGalleryUrl = !empty($venueGallery['gallery_url'])
                            ? (string) $venueGallery['gallery_url']
                            : '';
                        ?>
                        <a class="premium-venue-event-card" href="<?= e($venueGalleryUrl !== '' ? $venueGalleryUrl : $venueGalleryFilterUrl) ?>"<?= $venueGalleryUrl !== '' ? ' target="_blank" rel="noopener"' : '' ?>>
                            <span class="premium-venue-event-card__image">
                                <?php if ($venueGalleryCoverImage !== ''): ?>
                                    <img src="<?= e($venueGalleryCoverImage) ?>" alt="<?= e($venueGalleryTitle) ?> preview" loading="lazy">
                                <?php else: ?>
                                    <span class="premium-venue-event-card__image-placeholder" aria-hidden="true">
                                        <i class="fas fa-images"></i>
                                    </span>
                                <?php endif; ?>
                            </span>
                            <span class="premium-venue-event-card__body">
                                <span class="premium-venue-event-card__date">
                                    <i class="fas fa-calendar" aria-hidden="true"></i>
                                    <?= e($venueGalleryDate) ?>
                                </span>
                                <strong><?= e($venueGalleryTitle) ?></strong>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="premium-venue-empty">
                    <i class="fas fa-images" aria-hidden="true"></i>
                    <p>No event galleries listed yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="premium-venue-section" id="premium-events">
        <div class="container">
            <span class="premium-venue-section__eyebrow">What's coming</span>
            <h2 class="premium-venue-section__title">Upcoming Events</h2>
            <?php
            /*
             * Future Events batch: once an events table/model exists, loop
             * real upcoming events here using markup similar to
             * .premium-venue-event-card (see the Event Photos section above),
             * and only fall back to the empty state below when the venue
             * truly has no upcoming events.
             */
            ?>
            <div class="premium-venue-empty">
                <i class="fas fa-calendar-days" aria-hidden="true"></i>
                <p>No upcoming events listed yet. Check back soon for brunch events at this location.</p>
            </div>
        </div>
    </section>

    <section class="premium-venue-section" id="premium-visit">
        <div class="container">
            <span class="premium-venue-section__eyebrow">Plan your visit</span>
            <h2 class="premium-venue-section__title">Hours & Location</h2>

            <div class="premium-venue-visit">
                <div>
                    <ul class="premium-venue-info-list">
                        <?php if ($addressLine !== ''): ?>
                            <li>
                                <i class="fas fa-location-dot" aria-hidden="true"></i>
                                <span><?= e($addressLine) ?></span>
                            </li>
                        <?php endif; ?>

                        <?php if ($hasPhone): ?>
                            <li>
                                <i class="fas fa-phone" aria-hidden="true"></i>
                                <a href="tel:<?= e($phoneTel) ?>"><?= e((string) $venue['phone']) ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if ($hasWebsite): ?>
                            <li>
                                <i class="fas fa-globe" aria-hidden="true"></i>
                                <a href="<?= e((string) $venue['website_url']) ?>" target="_blank" rel="noopener">Website</a>
                            </li>
                        <?php endif; ?>

                        <?php if ($hasInstagram): ?>
                            <li>
                                <i class="fab fa-instagram" aria-hidden="true"></i>
                                <a href="<?= e((string) $venue['instagram_url']) ?>" target="_blank" rel="noopener">Instagram</a>
                            </li>
                        <?php endif; ?>

                        <?php if ($hasFacebook): ?>
                            <li>
                                <i class="fab fa-facebook" aria-hidden="true"></i>
                                <a href="<?= e((string) $venue['facebook_url']) ?>" target="_blank" rel="noopener">Facebook</a>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <dl class="premium-venue-hours">
                        <?php foreach ($venueHoursRows as $hoursRow): ?>
                            <div>
                                <dt><?= e((string) ($hoursRow['day'] ?? 'Hours')) ?></dt>
                                <dd><?= e((string) ($hoursRow['hours'] ?? 'N/A')) ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                </div>

                <div class="premium-venue-directions-card">
                    <div>
                        <span class="premium-venue-directions-card__eyebrow">Getting there</span>
                        <h3>Plan your route before you go</h3>
                        <?php if ($addressLine !== ''): ?>
                            <p><?= e($addressLine) ?></p>
                        <?php else: ?>
                            <p>Address details are coming soon.</p>
                        <?php endif; ?>
                    </div>

                    <?php if ($directionsUrl !== ''): ?>
                        <a class="btn btn--primary" href="<?= e($directionsUrl) ?>" target="_blank" rel="noopener">
                            <i class="fas fa-diamond-turn-right" aria-hidden="true"></i>
                            Open directions
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <footer class="premium-venue-footer">
        <div class="container">
            <div class="premium-venue-cta">
                <div>
                    <span class="premium-venue-cta__eyebrow">Ready for brunch?</span>
                    <h2>Plan your visit to <?= e((string) ($venue['name'] ?? 'this venue')) ?></h2>
                    <p>Reserve a table, get directions, or call ahead to confirm your plans.</p>
                </div>
                <div class="premium-venue-cta__actions">
                    <button class="btn btn--accent" type="button" data-rsvp-trigger data-rsvp-venue-slug="<?= e((string) ($venue['slug'] ?? '')) ?>" data-rsvp-venue-id="<?= (int) ($venue['id'] ?? 0) ?>" data-rsvp-venue-name="<?= e((string) ($venue['name'] ?? 'venue')) ?>" data-rsvp-source="premium_footer_cta">
                        <i class="fas fa-calendar-check" aria-hidden="true"></i>
                        RSVP
                    </button>
                    <?php if ($directionsUrl !== ''): ?>
                        <a class="btn btn--outline-light" href="<?= e($directionsUrl) ?>" target="_blank" rel="noopener">
                            <i class="fas fa-diamond-turn-right" aria-hidden="true"></i>
                            Directions
                        </a>
                    <?php endif; ?>
                    <?php if ($hasPhone && $phoneTel !== ''): ?>
                        <a class="btn btn--primary" href="tel:<?= e($phoneTel) ?>">
                            <i class="fas fa-phone" aria-hidden="true"></i>
                            Call
                        </a>
                    <?php endif; ?>
                    <?php if ($hasWebsite): ?>
                        <a class="btn btn--outline-light" href="<?= e((string) $venue['website_url']) ?>" target="_blank" rel="noopener">
                            <i class="fas fa-globe" aria-hidden="true"></i>
                            Website
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>

    <div class="premium-venue-mobile-bar">
        <button
            class="btn btn--accent premium-venue-mobile-bar__rsvp"
            type="button"
            data-rsvp-trigger
            data-rsvp-venue-slug="<?= e((string) ($venue['slug'] ?? '')) ?>"
            data-rsvp-venue-id="<?= (int) ($venue['id'] ?? 0) ?>"
            data-rsvp-venue-name="<?= e((string) ($venue['name'] ?? 'venue')) ?>"
            data-rsvp-source="premium_mobile_bar"
        >
            <i class="fas fa-calendar-check" aria-hidden="true"></i>
            RSVP
        </button>

        <?php if ($hasPhone && $phoneTel !== ''): ?>
            <a class="btn btn--primary" href="tel:<?= e($phoneTel) ?>">
                <i class="fas fa-phone" aria-hidden="true"></i>
                Call
            </a>
        <?php endif; ?>

        <?php if ($directionsUrl !== ''): ?>
            <a class="btn btn--accent" href="<?= e($directionsUrl) ?>" target="_blank" rel="noopener">
                <i class="fas fa-diamond-turn-right" aria-hidden="true"></i>
                Directions
            </a>
        <?php endif; ?>
    </div>

    <div class="venue-photo-lightbox" aria-hidden="true" role="dialog" aria-label="Expanded venue photo">
        <button type="button" class="venue-photo-lightbox__close" aria-label="Close expanded photo">
            <i class="fas fa-xmark" aria-hidden="true"></i>
        </button>
        <img class="venue-photo-lightbox__image" src="" alt="">
    </div>
</div>

<script src="<?= e(asset_url('assets/js/venue-lightbox.js')) ?>"></script>
<?php require APP_ROOT . '/views/partials/footer.php'; ?>
