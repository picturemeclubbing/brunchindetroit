<?php
declare(strict_types=1);

/**
 * Gallery list view (Phase 4B).
 *
 * @var array  $galleries
 * @var array  $locations
 * @var array  $years
 * @var string $q
 * @var string $location
 * @var int|null $year
 * @var int|null $month
 * @var array  $monthNames
 * @var bool   $hasActiveFilters
 */

require APP_ROOT . '/views/partials/header.php';

/** Small helper: human-readable event date. */
$formatDate = static function (?string $raw): string {
    if ($raw === null || $raw === '') {
        return '';
    }
    $ts = strtotime($raw);
    return $ts !== false ? date('F j, Y', $ts) : '';
};

/** Small helper: short month/year label for cards. */
$formatMonth = static function (?string $raw): string {
    if ($raw === null || $raw === '') {
        return '';
    }
    $ts = strtotime($raw);
    return $ts !== false ? date('M Y', $ts) : '';
};

/** Pick the most useful location string for a gallery card. */
$galleryLocation = static function (array $g): string {
    $label    = trim((string) ($g['location_label'] ?? ''));
    $venue    = trim((string) ($g['venue_name'] ?? ''));
    $neighbor = trim((string) ($g['neighborhood_name'] ?? ''));
    if ($label !== '') {
        return $label;
    }
    if ($neighbor !== '') {
        return $neighbor;
    }
    return $venue;
};

// Natural-language result status line.
$galleryCount = count($galleries);
if (!$hasActiveFilters) {
    $statusText = 'Showing all ' . $galleryCount . ' galler' . ($galleryCount === 1 ? 'y' : 'ies') . '.';
} else {
    $bits = [];
    if ($q !== '') {
        $bits[] = 'Ã¢â‚¬Å“' . $q . 'Ã¢â‚¬Â';
    }
    if ($location !== '') {
        $bits[] = $location;
    }
    if ($month !== null) {
        $bits[] = $monthNames[$month] ?? ('Month ' . $month);
    }
    if ($year !== null) {
        $bits[] = (string) $year;
    }
    $joined = implode(' Ã‚Â· ', $bits);
    $statusText = 'Showing ' . $galleryCount . ' galler' . ($galleryCount === 1 ? 'y' : 'ies') . ' matching ' . $joined . '.';
}

// Gallery hero background Ã¢â‚¬â€ a warm Detroit brunch photo.
$galleryHeroImage = 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?auto=format&fit=crop&w=1600&q=80';
?>

<div class="gallery-list-page">
    <!-- Page hero: same system as Home/Directory/Blog -->
    <section class="main-page-hero main-page-hero--gallery" style="--hero-bg-image:url('<?= e($galleryHeroImage) ?>');">
        <div class="container main-page-hero__inner">
            <div class="main-page-hero__content">
                <span class="main-page-hero__badge">
                    <i class="fas fa-camera-retro" aria-hidden="true"></i>
                    Detroit Brunch Galleries
                </span>
                <h1 class="main-page-hero__title">Event Gallery Archives</h1>
                <p class="main-page-hero__subtitle">
                    Browse brunch event galleries, venue highlights, and photo collections from around Detroit.
                </p>
            </div>
        </div>
    </section>

    <!-- Gallery finder: designed like the Directory finder -->
    <section class="gallery-finder" aria-label="Find a gallery">
        <div class="container gallery-finder__inner">
            <div class="gallery-finder__header">
                <div class="gallery-finder__heading">
                    <span class="gallery-finder__eyebrow">
                        <i class="fas fa-images" aria-hidden="true"></i>
                        Gallery Search
                    </span>
                    <h2 class="gallery-finder__title">Find a Gallery</h2>
                    <p class="gallery-finder__subtitle">
                        Search by event, venue, neighborhood, or date.
                    </p>
                </div>
                <div class="gallery-finder__status"><?= e($statusText) ?></div>
            </div>

            <form class="gallery-filter-form" method="get" action="<?= e(asset_url('gallery.php')) ?>">
                <div class="gallery-filter-form__grid">
                    <div class="gallery-filter-form__field gallery-filter-form__field--search">
                        <label for="gallery-q" class="gallery-filter-form__label">Search</label>
                        <div class="gallery-filter-form__control">
                            <span class="gallery-filter-form__icon" aria-hidden="true">
                                <i class="fas fa-magnifying-glass"></i>
                            </span>
                            <input
                                id="gallery-q"
                                type="search"
                                name="q"
                                value="<?= e($q) ?>"
                                placeholder="Event, venue, or keyword&hellip;"
                                maxlength="80"
                                autocomplete="off"
                            >
                        </div>
                    </div>

                    <div class="gallery-filter-form__field">
                        <label for="gallery-location" class="gallery-filter-form__label">Location</label>
                        <select id="gallery-location" name="location">
                            <option value="">All locations</option>
                            <?php foreach ($locations as $loc): ?>
                                <option
                                    value="<?= e($loc) ?>"
                                    <?= $location === $loc ? ' selected' : '' ?>
                                ><?= e($loc) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="gallery-filter-form__field">
                        <label for="gallery-year" class="gallery-filter-form__label">Year</label>
                        <select id="gallery-year" name="year">
                            <option value="">All years</option>
                            <?php foreach ($years as $yr): ?>
                                <option
                                    value="<?= e((string) $yr) ?>"
                                    <?= $year === $yr ? ' selected' : '' ?>
                                ><?= e((string) $yr) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="gallery-filter-form__field">
                        <label for="gallery-month" class="gallery-filter-form__label">Month</label>
                        <select id="gallery-month" name="month">
                            <option value="">All months</option>
                            <?php foreach ($monthNames as $num => $name): ?>
                                <option
                                    value="<?= e((string) $num) ?>"
                                    <?= $month === $num ? ' selected' : '' ?>
                                ><?= e($name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="gallery-filter-form__actions">
                        <button type="submit" class="btn btn--primary">
                            <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                            <span>Apply Filters</span>
                        </button>
                        <?php if ($hasActiveFilters): ?>
                            <a class="btn btn--outline gallery-filter-form__clear" href="<?= e(asset_url('gallery.php')) ?>">
                                <i class="fas fa-arrow-rotate-left" aria-hidden="true"></i>
                                View All
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="section section--muted">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2 class="section-title">
                        <?= $hasActiveFilters ? 'Matching Galleries' : 'Brunch Galleries' ?>
                    </h2>
                    <p class="section-subtitle">
                        <?= e($statusText) ?>
                    </p>
                </div>
            </div>

            <?php if (empty($galleries)): ?>
                <div class="gallery-empty-state empty-state">
                    <i class="fas fa-images" aria-hidden="true"></i>
                    <h3>No galleries matched your search</h3>
                    <p>Try a different keyword, location, or date, or browse all galleries.</p>
                    <a class="btn btn--primary" href="<?= e(asset_url('gallery.php')) ?>">
                        <i class="fas fa-arrow-rotate-left" aria-hidden="true"></i>
                        View All Galleries
                    </a>
                </div>
            <?php else: ?>
                <div class="gallery-grid">
                    <?php foreach ($galleries as $gallery): ?>
                        <?php
                        $locText   = $galleryLocation($gallery);
                        $dateFull  = $formatDate($gallery['event_date'] ?? null);
                        $dateShort = $formatMonth($gallery['event_date'] ?? null);
                        $hasUrl    = !empty($gallery['gallery_url']);
                        ?>
                        <article class="gallery-card card card--hover">
                            <div class="gallery-card__media">
                                <?php if (!empty($gallery['cover_image_path'])): ?>
                                    <img
                                        src="<?= e($gallery['cover_image_path']) ?>"
                                        alt="<?= e($gallery['title'] ?? 'Gallery cover') ?>"
                                        loading="lazy"
                                    >
                                <?php else: ?>
                                    <div class="gallery-card__placeholder" aria-hidden="true">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($gallery['is_featured'])): ?>
                                    <span class="badge badge--accent gallery-card__badge-featured">Featured</span>
                                <?php endif; ?>
                            </div>

                            <div class="gallery-card__body">
                                <h3 class="gallery-card__title"><?= e($gallery['title'] ?? 'Untitled Gallery') ?></h3>

                                <?php
                                $metaBits = [];
                                if ($locText !== '') {
                                    $metaBits[] = '<i class="fas fa-location-dot" aria-hidden="true"></i> ' . e($locText);
                                }
                                if ($dateFull !== '') {
                                    $metaBits[] = '<i class="fas fa-calendar-day" aria-hidden="true"></i> ' . e($dateFull);
                                }
                                ?>
                                <?php if ($metaBits !== []): ?>
                                    <p class="gallery-card__meta">
                                        <?php foreach ($metaBits as $i => $bit): ?>
                                            <span class="gallery-card__meta-item"><?= $bit ?></span>
                                        <?php endforeach; ?>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($gallery['description'])): ?>
                                    <p class="gallery-card__description"><?= e($gallery['description']) ?></p>
                                <?php endif; ?>

                                <div class="gallery-card__badges">
                                    <?php if ($locText !== ''): ?>
                                        <span class="badge badge--location">
                                            <i class="fas fa-map-pin" aria-hidden="true"></i>
                                            <?= e($locText) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($dateShort !== ''): ?>
                                        <span class="badge badge--date">
                                            <i class="fas fa-calendar" aria-hidden="true"></i>
                                            <?= e($dateShort) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="gallery-card__actions">
                                    <?php if ($hasUrl): ?>
                                        <a
                                            class="btn btn--primary btn--block"
                                            href="<?= e(asset_url('gallery-view.php?slug=' . urlencode((string) $gallery['slug']))) ?>"
                                        >
                                            <i class="fas fa-images" aria-hidden="true"></i>
                                            View Gallery Details Details
                                        </a>
                                    <?php else: ?>
                                        <span class="btn btn--primary btn--block is-disabled" aria-disabled="true">
                                            <i class="fas fa-clock" aria-hidden="true"></i>
                                            Gallery Coming Soon
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php
require APP_ROOT . '/views/partials/footer.php';
