<?php
declare(strict_types=1);

/**
 * @var array  $venues
 * @var array  $visibleVenues
 * @var array  $availableLetters
 * @var string $searchQuery
 * @var string $selectedLetter
 * @var string $styleFilter
 * @var string $favoriteFilter
 * @var string $whenFilter
 * @var bool   $featuredFilter
 * @var int    $totalVenueCount
 * @var int    $filteredVenueCount
 * @var bool   $hasMore
 * @var int    $nextPage
 */

require APP_ROOT . '/views/partials/header.php';

$buildDirectoryUrl = static function (array $overrides = [], array $clear = []) use (
    $searchQuery,
    $selectedLetter,
    $styleFilter,
    $favoriteFilter,
    $whenFilter,
    $featuredFilter
): string {
    $params = [];

    if ($searchQuery !== '') {
        $params['q'] = $searchQuery;
    }

    if ($selectedLetter !== '') {
        $params['letter'] = $selectedLetter;
    }

    if ($styleFilter !== '') {
        $params['style'] = $styleFilter;
    }

    if ($favoriteFilter !== '') {
        $params['favorite'] = $favoriteFilter;
    }

    if ($whenFilter !== '') {
        $params['when'] = $whenFilter;
    }

    if ($featuredFilter) {
        $params['featured'] = '1';
    }

    foreach ($clear as $key) {
        unset($params[$key]);
    }

    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '' || $value === false) {
            unset($params[$key]);
            continue;
        }

        $params[$key] = (string) $value;
    }

    return $params === []
        ? asset_url('directory.php')
        : asset_url('directory.php?' . http_build_query($params));
};

$directoryUrl = static function (string $letter = '') use ($buildDirectoryUrl): string {
    return $buildDirectoryUrl(
        ['letter' => $letter],
        ['q', 'page']
    );
};

$pageUrl = static function (int $targetPage) use ($buildDirectoryUrl): string {
    return $buildDirectoryUrl(['page' => max(1, $targetPage)]);
};

$venueCardFallbackImage = asset_url('assets/images/blog-card-fallback.png');

$resolveVenueImage = static function (?string $imagePath) use ($venueCardFallbackImage): string {
    $imagePath = trim((string) $imagePath);

    if ($imagePath === '') {
        return $venueCardFallbackImage;
    }

    if (str_starts_with($imagePath, '/')) {
        $publicFile = dirname(APP_ROOT) . '/public' . str_replace('/', DIRECTORY_SEPARATOR, $imagePath);

        if (!is_file($publicFile)) {
            return $venueCardFallbackImage;
        }
    }

    return $imagePath;
};

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

$hasActiveFilter = ($searchQuery !== '')
    || ($selectedLetter !== '')
    || ($styleFilter !== '')
    || ($favoriteFilter !== '')
    || ($whenFilter !== '')
    || $featuredFilter;

$activeLabels = [];

if ($featuredFilter) {
    $activeLabels[] = 'featured spots';
}

if ($searchQuery !== '') {
    $activeLabels[] = '"' . $searchQuery . '"';
}

if ($selectedLetter !== '') {
    $activeLabels[] = 'A-Z: ' . $selectedLetter;
}

if ($styleFilter !== '') {
    $activeLabels[] = 'style: ' . $styleFilter;
}

if ($favoriteFilter !== '') {
    $activeLabels[] = 'favorite: ' . $favoriteFilter;
}

if ($whenFilter !== '') {
    $activeLabels[] = 'when: ' . $whenFilter;
}

if (!$hasActiveFilter) {
    $statusText = 'Showing all ' . $filteredVenueCount . ' brunch spot' . ($filteredVenueCount === 1 ? '' : 's') . '.';
} else {
    $statusText = 'Showing ' . $filteredVenueCount . ' result' . ($filteredVenueCount === 1 ? '' : 's') . ' for ' . implode(', ', $activeLabels) . '.';
}
?>

<div class="directory-page">
    <section class="main-page-hero main-page-hero--directory" style="--hero-bg-image:url('https://images.unsplash.com/photo-1551218808-94e220e084d2?auto=format&fit=crop&w=1600&q=80');">
        <div class="container main-page-hero__inner">
            <div class="main-page-hero__content">
                <span class="main-page-hero__badge">
                    <i class="fas fa-map-location-dot" aria-hidden="true"></i>
                    Detroit Brunch Directory
                </span>
                <h1 class="main-page-hero__title">Browse Detroit Brunch Spots</h1>
                <p class="main-page-hero__subtitle">
                    Explore local brunch restaurants, featured neighborhoods, food styles,
                    and menu details curated for Detroit brunch lovers.
                </p>
            </div>
        </div>
    </section>

    <!-- Designed Directory Search + A-Z Browse panel -->
    <section class="directory-finder" aria-label="Find a brunch spot">
        <div class="container directory-finder__inner">
            <div class="directory-finder__header">
                <div class="directory-finder__heading">
                    <span class="directory-finder__eyebrow">
                        <i class="fas fa-compass" aria-hidden="true"></i>
                        Directory Search
                    </span>
                    <h2 class="directory-finder__title">Find a Brunch Spot</h2>
                    <p class="directory-finder__subtitle">
                        Search by location, address, venue name, dish, or vibe.
                    </p>
                </div>
                <div class="directory-finder__status"><?= e($statusText) ?></div>
            </div>

            <form class="directory-search-form" method="get" action="<?= e(asset_url('directory.php')) ?>">
                <?php if ($selectedLetter !== ''): ?>
                    <input type="hidden" name="letter" value="<?= e($selectedLetter) ?>">
                <?php endif; ?>
                <?php if ($styleFilter !== ''): ?>
                    <input type="hidden" name="style" value="<?= e($styleFilter) ?>">
                <?php endif; ?>
                <?php if ($favoriteFilter !== ''): ?>
                    <input type="hidden" name="favorite" value="<?= e($favoriteFilter) ?>">
                <?php endif; ?>
                <?php if ($whenFilter !== ''): ?>
                    <input type="hidden" name="when" value="<?= e($whenFilter) ?>">
                <?php endif; ?>
                <?php if ($featuredFilter): ?>
                    <input type="hidden" name="featured" value="1">
                <?php endif; ?>

                <label for="directory-search" class="sr-only">Search venues by location, address, or name</label>
                <div class="directory-search-form__control">
                    <span class="directory-search-form__icon" aria-hidden="true">
                        <i class="fas fa-magnifying-glass"></i>
                    </span>
                    <input
                        id="directory-search"
                        type="search"
                        name="q"
                        value="<?= e($searchQuery) ?>"
                        placeholder="Brunch near me, city, address, zip, or venue..."
                        maxlength="80"
                        autocomplete="off"
                    >
                    <button type="submit" class="btn btn--primary">
                        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                        <span>Search</span>
                    </button>
                    <?php if ($hasActiveFilter): ?>
                        <a class="btn btn--outline directory-search-form__reset" href="<?= e(asset_url('directory.php')) ?>">
                            <i class="fas fa-arrow-rotate-left" aria-hidden="true"></i>
                            <span>Reset</span>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="directory-alpha">
                <span class="directory-alpha__label">BROWSE A-Z</span>
                <nav class="directory-alpha__nav" aria-label="A to Z venue filter">
                    <a
                        class="directory-alpha__link<?= $selectedLetter === '' ? ' is-active' : '' ?>"
                        href="<?= e($directoryUrl('')) ?>"
                    >All</a>
                    <?php foreach (range('A', 'Z') as $letter):
                        $isAvailable = in_array($letter, $availableLetters, true);
                        if (!$isAvailable): ?>
                            <span class="directory-alpha__link is-disabled" aria-disabled="true">
                                <?= $letter ?>
                            </span>
                        <?php else: ?>
                            <a
                                class="directory-alpha__link<?= $selectedLetter === $letter ? ' is-active' : '' ?>"
                                href="<?= e($directoryUrl($letter)) ?>"
                            ><?= $letter ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>
    </section>

    <section class="section section--muted">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2 class="section-title">
                        <?= $hasActiveFilter ? 'Matching Brunch Spots' : 'Brunch Directory' ?>
                    </h2>
                    <p class="section-subtitle">
                        <?= $hasActiveFilter
                            ? e($statusText)
                            : 'Showing published venues from the database.' ?>
                    </p>
                </div>
            </div>

            <?php if (empty($venues)): ?>
                <?php if ($totalVenueCount === 0): ?>
                    <div class="empty-state">
                        <h3>No venues published yet</h3>
                        <p>
                            The directory is connected to the database, but no published venues
                            have been added yet. Add demo venue rows to test this page.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="empty-state directory-empty-filtered">
                        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                        <h3>No brunch spots matched your search</h3>
                        <p>Try a different name or letter, or browse the full directory.</p>
                        <a class="btn btn--primary" href="<?= e(asset_url('directory.php')) ?>">
                            <i class="fas fa-arrow-rotate-left" aria-hidden="true"></i>
                            View All
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="directory-grid" id="directory-grid">
                    <?php foreach ($visibleVenues as $venue): ?>
                        <?php
                        // Build an optional single-line address from whatever fields are present.
                        $addressParts = array_filter([
                            $venue['address_line1'] ?? '',
                            $venue['address_line2'] ?? '',
                            trim((($venue['city'] ?? '') . ' ' . ($venue['state'] ?? '') . ' ' . ($venue['zip'] ?? ''))),
                        ], static fn ($v) => trim((string) $v) !== '');
                        $addressLine = implode(', ', $addressParts);
                        $venueHoursDisplay = $formatVenueHours((string) ($venue['brunch_hours_note'] ?? ''));

                        $venueImage = $resolveVenueImage((string) ($venue['main_image_path'] ?? ''));
                        $profileUrl = asset_url('venue.php?slug=' . urlencode((string) ($venue['slug'] ?? '')));
                        $areaText = trim((string) ($venue['neighborhood_name'] ?? ''));
                        $priceText = trim((string) ($venue['price_range'] ?? ''));
                        $phoneText = trim((string) ($venue['phone'] ?? ''));
                        $phoneHref = $phoneText !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $phoneText) : '';
                        $directionsUrl = $addressLine !== ''
                            ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($addressLine)
                            : '';
                        $venueCardTone = !empty($venue['is_featured']) ? 'directory-venue-card--featured' : 'directory-venue-card--standard';
                        ?>
                        <article class="venue-card directory-venue-card <?= e($venueCardTone) ?>">
                            <a
                                class="directory-venue-card__stretched-link"
                                href="<?= e($profileUrl) ?>"
                                aria-label="View <?= e((string) ($venue['name'] ?? 'venue')) ?> profile"
                            ></a>

                            <div class="directory-venue-card__media">
                                <img
                                    src="<?= e($venueImage) ?>"
                                    alt="<?= e((string) ($venue['name'] ?? 'Venue')) ?>"
                                    loading="lazy"
                                >
                                <?php if (!empty($venue['is_featured'])): ?>
                                    <span class="directory-venue-card__featured-chip">
                                        <i class="fas fa-star" aria-hidden="true"></i>
                                        Featured
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="directory-venue-card__body">
                                <?php if ($areaText !== '' || $priceText !== ''): ?>
                                    <p class="directory-venue-card__eyebrow">
                                        <?php if ($areaText !== ''): ?>
                                            <span><?= e($areaText) ?></span>
                                        <?php endif; ?>
                                        <?php if ($areaText !== '' && $priceText !== ''): ?>
                                            <span class="directory-venue-card__dot">•</span>
                                        <?php endif; ?>
                                        <?php if ($priceText !== ''): ?>
                                            <span class="directory-venue-card__price"><?= e($priceText) ?></span>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>

                                <h3 class="directory-venue-card__title"><?= e((string) ($venue['name'] ?? '')) ?></h3>

                                <?php if (!empty($venue['description'])): ?>
                                    <p class="directory-venue-card__description"><?= e((string) $venue['description']) ?></p>
                                <?php endif; ?>

                                <div class="directory-venue-card__facts">
                                    <p>
                                        <i class="fas fa-clock" aria-hidden="true"></i>
                                        <span><strong>Brunch</strong> <?= e($venueHoursDisplay) ?></span>
                                    </p>
                                    <?php if ($addressLine !== ''): ?>
                                        <p>
                                            <i class="fas fa-location-dot" aria-hidden="true"></i>
                                            <span><?= e($addressLine) ?></span>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="directory-venue-card__footer">
                                    <span class="directory-venue-card__view">
                                        View profile
                                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                                    </span>

                                    <div class="directory-venue-card__icons" aria-label="Quick actions">
                                        <?php if ($phoneHref !== ''): ?>
                                            <a class="directory-venue-card__icon" href="<?= e($phoneHref) ?>" aria-label="Call <?= e((string) ($venue['name'] ?? 'venue')) ?>">
                                                <i class="fas fa-phone" aria-hidden="true"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($directionsUrl !== ''): ?>
                                            <a class="directory-venue-card__icon" href="<?= e($directionsUrl) ?>" target="_blank" rel="noopener" aria-label="Get directions to <?= e((string) ($venue['name'] ?? 'venue')) ?>">
                                                <i class="fas fa-diamond-turn-right" aria-hidden="true"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($hasMore): ?>
                    <div class="directory-load-more">
                        <a class="btn btn--primary directory-load-more__btn" href="<?= e($pageUrl($nextPage)) ?>#directory-grid">
                            <i class="fas fa-plus" aria-hidden="true"></i>
                            Load More
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php
require APP_ROOT . '/views/partials/footer.php';
