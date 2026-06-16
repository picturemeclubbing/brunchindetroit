<?php
declare(strict_types=1);

/**
 * @var array  $venues
 * @var array  $availableLetters
 * @var string $selectedLetter
 * @var string $searchQuery
 * @var int    $totalVenueCount
 * @var int    $filteredVenueCount
 */

require APP_ROOT . '/views/partials/header.php';

// Helper: build a directory URL preserving the current search query (q) when needed.
$directoryUrl = static function (string $letter = '') use ($searchQuery): string {
    $params = [];
    if ($searchQuery !== '') {
        $params['q'] = $searchQuery;
    }
    if ($letter !== '') {
        $params['letter'] = $letter;
    }
    return $params === []
        ? asset_url('directory.php')
        : asset_url('directory.php?' . http_build_query($params));
};

$hasActiveFilter = ($searchQuery !== '') || ($selectedLetter !== '');

// Natural-language result status line.
if (!$hasActiveFilter) {
    $statusText = 'Showing all ' . $filteredVenueCount . ' brunch spot' . ($filteredVenueCount === 1 ? '' : 's') . '.';
} elseif ($searchQuery !== '' && $selectedLetter !== '') {
    $statusText = 'Showing ' . $filteredVenueCount . ' result' . ($filteredVenueCount === 1 ? '' : 's') . ' for “' . $searchQuery . '” under ' . $selectedLetter . '.';
} elseif ($searchQuery !== '') {
    $statusText = 'Showing ' . $filteredVenueCount . ' result' . ($filteredVenueCount === 1 ? '' : 's') . ' for “' . $searchQuery . '”.';
} else {
    $statusText = 'Showing ' . $filteredVenueCount . ' brunch spot' . ($filteredVenueCount === 1 ? '' : 's') . ' under ' . $selectedLetter . '.';
}
?>

<main>
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

    <!-- Designed Directory Search + A–Z Browse panel -->
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
                        Search by name or browse Detroit brunch spots alphabetically.
                    </p>
                </div>
                <div class="directory-finder__status"><?= e($statusText) ?></div>
            </div>

            <form class="directory-search-form" method="get" action="<?= e(asset_url('directory.php')) ?>">
                <?php if ($selectedLetter !== ''): ?>
                    <input type="hidden" name="letter" value="<?= e($selectedLetter) ?>">
                <?php endif; ?>

                <label for="directory-search" class="sr-only">Search venues by name</label>
                <div class="directory-search-form__control">
                    <span class="directory-search-form__icon" aria-hidden="true">
                        <i class="fas fa-magnifying-glass"></i>
                    </span>
                    <input
                        id="directory-search"
                        type="search"
                        name="q"
                        value="<?= e($searchQuery) ?>"
                        placeholder="Search by venue name&hellip;"
                        maxlength="80"
                        autocomplete="off"
                    >
                    <button type="submit" class="btn btn--primary">
                        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                        <span>Search</span>
                    </button>
                </div>

                <?php if ($hasActiveFilter): ?>
                    <div class="directory-finder__clear">
                        <a href="<?= e($directoryUrl('')) ?>">
                            <i class="fas fa-arrow-rotate-left" aria-hidden="true"></i>
                            View All
                        </a>
                    </div>
                <?php endif; ?>
            </form>

            <div class="directory-alpha">
                <span class="directory-alpha__label">Browse A–Z</span>
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
                        <a class="btn btn--primary" href="<?= e($directoryUrl('')) ?>">
                            <i class="fas fa-arrow-rotate-left" aria-hidden="true"></i>
                            View All
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="directory-grid">
                    <?php foreach ($venues as $venue): ?>
                        <?php
                        // Build an optional single-line address from whatever fields are present.
                        $addressParts = array_filter([
                            $venue['address_line1'] ?? '',
                            $venue['address_line2'] ?? '',
                            trim((($venue['city'] ?? '') . ' ' . ($venue['state'] ?? '') . ' ' . ($venue['zip'] ?? ''))),
                        ], static fn ($v) => trim((string) $v) !== '');
                        $addressLine = implode(', ', $addressParts);
                        ?>
                        <article class="card card--hover venue-card">
                            <div class="venue-card__body">
                                <?php if (!empty($venue['is_featured'])): ?>
                                    <span class="badge badge--accent venue-card__featured">Featured</span>
                                <?php endif; ?>

                                <h3 class="venue-card__title"><?= e($venue['name']) ?></h3>

                                <?php
                                $hasNeighborhood = !empty($venue['neighborhood_name']);
                                $hasPrice = !empty($venue['price_range']);
                                if ($hasNeighborhood || $hasPrice):
                                ?>
                                    <p class="venue-card__meta">
                                        <?php if ($hasNeighborhood): ?>
                                            <span class="venue-card__meta-item">
                                                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                                                <?= e($venue['neighborhood_name']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($hasPrice): ?>
                                            <span class="venue-card__meta-item venue-card__price">
                                                <?= e($venue['price_range']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($venue['description'])): ?>
                                    <p class="venue-card__description"><?= e($venue['description']) ?></p>
                                <?php endif; ?>

                                <?php if ($addressLine !== ''): ?>
                                    <p class="venue-card__address">
                                        <i class="fas fa-location-dot" aria-hidden="true"></i>
                                        <?= e($addressLine) ?>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($venue['brunch_hours_note'])): ?>
                                    <p class="venue-card__hours">
                                        <i class="fas fa-clock" aria-hidden="true"></i>
                                        <strong>Brunch:</strong> <?= e($venue['brunch_hours_note']) ?>
                                    </p>
                                <?php endif; ?>

                                <div class="venue-card__actions">
                                    <a
                                        class="btn btn--primary venue-card__view-profile"
                                        href="<?= e(asset_url('venue.php?slug=' . urlencode((string) $venue['slug']))) ?>"
                                    >
                                        <i class="fas fa-circle-info" aria-hidden="true"></i>
                                        View Profile
                                    </a>

                                    <?php if (!empty($venue['website_url'])): ?>
                                        <a
                                            class="btn btn--outline venue-card__website"
                                            href="<?= e($venue['website_url']) ?>"
                                            target="_blank"
                                            rel="noopener"
                                        >
                                            <i class="fas fa-up-right-from-square" aria-hidden="true"></i>
                                            Website
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
require APP_ROOT . '/views/partials/footer.php';