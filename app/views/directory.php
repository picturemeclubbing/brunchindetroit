<?php
declare(strict_types=1);

/** @var array $venues */
require APP_ROOT . '/views/partials/header.php';
?>

<main>
    <section class="page-hero">
        <div class="container">
            <div class="page-hero__content">
                <p class="eyebrow">Detroit Brunch Guide</p>
                <h1>Browse Detroit Brunch Spots</h1>
                <p>
                    Explore local brunch restaurants, featured neighborhoods, food styles,
                    and menu details curated for Detroit brunch lovers.
                </p>
            </div>
        </div>
    </section>

    <section class="section section--muted">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Brunch Directory</h2>
                    <p class="section-subtitle">
                        Showing published venues from the database.
                    </p>
                </div>
            </div>

            <?php if (empty($venues)): ?>
                <div class="empty-state">
                    <h3>No venues published yet</h3>
                    <p>
                        The directory is connected to the database, but no published venues
                        have been added yet. Add demo venue rows to test this page.
                    </p>
                </div>
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