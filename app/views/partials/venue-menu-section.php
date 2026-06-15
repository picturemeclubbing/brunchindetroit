<?php
declare(strict_types=1);

/**
 * Menu & Allergy Filtering section (partial).
 *
 * Renders the full menu block inside #venue-menu-section so it can be:
 *   - included by app/views/venue-detail.php (full page), or
 *   - served standalone by public/menu-fragment.php (AJAX HTML fragment).
 *
 * This partial does NOT output <header>/<footer>; it expects the caller to
 * have already set up the page (full render) or to be an endpoint (fragment).
 *
 * Required variables (all set by public/venue.php and public/menu-fragment.php):
 * @var array  $venue                 Single venue row.
 * @var array  $allergens             List of allergens (id, name, slug).
 * @var string $selectedAllergen      Validated allergen slug ('' = none).
 * @var string|null $selectedAllergenName  Display name for the active filter.
 * @var array  $menuCategories        Categories+items from Menu::categoriesWithItemsForVenue().
 * @var string $allergyDisclaimer     Disclaimer text shown above the filter.
 */

if (!defined('APP_ROOT')) {
    // Fragment requests load bootstrap before this partial, so APP_ROOT
    // always exists in normal use. This guard keeps the partial safe if it
    // is ever included directly without the app context.
    return;
}

// Map allergen slug -> display name for the full details list.
$allergenNameBySlug = [];
foreach ($allergens as $a) {
    $allergenNameBySlug[(string) $a['slug']] = (string) $a['name'];
}

// Human labels + CSS classes for allergen statuses.
$menuStatusLabel = [
    'contains'           => 'Contains',
    'does_not_contain'   => 'Does not contain',
    'may_contain'        => 'May contain',
    'cross_contact_risk' => 'Cross-contact risk',
    'unknown'            => 'Unknown',
];
$menuStatusClass = [
    'contains'           => 'allergen-status-badge--contains',
    'does_not_contain'   => 'allergen-status-badge--does-not-contain',
    'may_contain'        => 'allergen-status-badge--may-contain',
    'cross_contact_risk' => 'allergen-status-badge--cross-contact-risk',
    'unknown'            => 'allergen-status-badge--unknown',
];

// Statuses considered "risk/important" and shown as summary badges when no
// filter is active. "does_not_contain" is deliberately excluded from the
// summary — it would flood the cards with up to 9 badges per item.
$riskStatuses = ['contains', 'may_contain', 'cross_contact_risk', 'unknown'];

// Count the total number of menu items currently shown (after filtering) so
// we can surface a clear "N menu items match this filter" feedback message.
$totalItemsShown = 0;
foreach ($menuCategories as $category) {
    $totalItemsShown += count($category['items'] ?? []);
}
?>
<section id="venue-menu-section" class="venue-profile-panel menu-filter-panel">

    <h2 class="venue-profile-panel__title">Menu & Allergy Filtering</h2>

    <!-- Disclaimer -->
    <p class="allergy-disclaimer">
        <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
        <span><?= e($allergyDisclaimer) ?></span>
    </p>

    <!-- Filter form (progressive enhancement: works without JS via GET submit) -->
    <form
        class="allergen-filter-form"
        method="get"
        action="<?= e(asset_url('venue.php')) ?>"
        data-venue-menu-filter
        data-slug="<?= e($venue['slug']) ?>"
    >
        <input type="hidden" name="slug" value="<?= e($venue['slug']) ?>">
        <label for="allergen" class="allergen-filter-form__label">
            <i class="fas fa-filter" aria-hidden="true"></i>
            Show items marked &ldquo;does not contain&rdquo;:
        </label>
        <select id="allergen" name="allergen" class="form-control allergen-select">
            <option value="">All items (no filter)</option>
            <?php foreach ($allergens as $a): ?>
                <?php $aSlug = (string) $a['slug']; ?>
                <option value="<?= e($aSlug) ?>" <?= $selectedAllergen === $aSlug ? 'selected' : '' ?>>
                    <?= e($a['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <noscript>
            <button type="submit" class="btn btn--primary">Apply Filter</button>
        </noscript>
    </form>

    <!-- Active filter status: clearly tells the user what is filtered and how
         many items match. Shown both when a filter is active (prominent
         teal/yellow callout) and when none is active (neutral summary). -->
    <div
        class="menu-filter-status <?= $selectedAllergen !== '' ? 'menu-filter-status--active' : 'menu-filter-status--default' ?>"
        role="status"
        aria-live="polite"
    >
        <?php if ($selectedAllergen !== ''): ?>
            <div class="menu-filter-status__row">
                <span class="menu-filter-status__icon" aria-hidden="true">
                    <i class="fas fa-circle-check"></i>
                </span>
                <span class="menu-filter-status__text">
                    Filtering by: <strong><?= e($selectedAllergenName) ?></strong>
                    &mdash; showing only items marked &lsquo;does not contain.&rsquo;
                </span>
            </div>
            <div class="menu-filter-status__row menu-filter-status__row--meta">
                <span class="menu-filter-status__count">
                    <?php if ($totalItemsShown > 0): ?>
                        <?= $totalItemsShown ?> menu <?= $totalItemsShown === 1 ? 'item' : 'items' ?> <?= $totalItemsShown === 1 ? 'matches' : 'match' ?> this filter.
                    <?php else: ?>
                        No menu items match this filter.
                    <?php endif; ?>
                </span>
                <a class="menu-filter-status__clear" href="<?= e(asset_url('venue.php')) ?>?slug=<?= e($venue['slug']) ?>">
                    <i class="fas fa-xmark" aria-hidden="true"></i>
                    Clear filter
                </a>
            </div>
        <?php else: ?>
            <div class="menu-filter-status__row">
                <span class="menu-filter-status__icon" aria-hidden="true">
                    <i class="fas fa-list"></i>
                </span>
                <span class="menu-filter-status__text">
                    Showing all published menu items
                    <?php if ($totalItemsShown > 0): ?>
                        (<?= $totalItemsShown ?> <?= $totalItemsShown === 1 ? 'item' : 'items' ?>)
                    <?php endif; ?>.
                </span>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($menuCategories === []): ?>
        <div class="menu-empty-state">
            <i class="fas <?= $selectedAllergen !== '' ? 'fa-circle-exclamation' : 'fa-utensils' ?>" aria-hidden="true"></i>
            <p>
                <?php if ($selectedAllergen !== ''): ?>
                    No menu items are currently marked safe for this allergen. Please confirm directly with the restaurant.
                <?php else: ?>
                    Menu details for this venue have not been published yet.
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <?php foreach ($menuCategories as $category): ?>
            <section class="menu-category">
                <h3 class="menu-category__title"><?= e($category['name']) ?></h3>
                <div class="menu-items-grid">
                    <?php foreach ($category['items'] as $item): ?>
                        <?php
                        // ---- Build compact allergen summary for this item ----
                        $statuses   = $item['allergen_statuses'] ?? [];
                        $summaryBadges = []; // [name, status, cssClass]

                        // Separate "risk" statuses from safe "does_not_contain".
                        $riskBadges = [];
                        $selectedBadge = null;

                        foreach ($statuses as $aSlug => $status) {
                            $aName  = $allergenNameBySlug[$aSlug] ?? $aSlug;
                            $sLabel = $menuStatusLabel[$status] ?? ucfirst(str_replace('_', ' ', $status));
                            $sClass = $menuStatusClass[$status] ?? 'allergen-status-badge--unknown';

                            // When a filter is active, surface only that allergen.
                            if ($selectedAllergen !== '' && $aSlug === $selectedAllergen) {
                                $selectedBadge = ['name' => $aName, 'label' => $sLabel, 'class' => $sClass];
                            }

                            if (in_array($status, $riskStatuses, true)) {
                                $riskBadges[] = ['name' => $aName, 'label' => $sLabel, 'class' => $sClass];
                            }
                        }

                        // Decide what to show as the compact summary.
                        if ($selectedAllergen !== '' && $selectedBadge !== null) {
                            $summaryBadges[] = $selectedBadge;
                        } elseif ($selectedAllergen === '') {
                            // No filter: show only risk badges. If none, show a
                            // single positive "details available" chip.
                            if ($riskBadges !== []) {
                                $summaryBadges = $riskBadges;
                            } elseif ($statuses !== []) {
                                $summaryBadges[] = [
                                    'name'  => '',
                                    'label' => 'Allergen details available',
                                    'class' => 'allergen-status-badge--does-not-contain',
                                ];
                            }
                        }
                        ?>
                        <article class="menu-item-card">
                            <header class="menu-item-card__header">
                                <h4 class="menu-item-card__name"><?= e($item['name']) ?></h4>
                                <?php if ($item['price'] !== null): ?>
                                    <span class="menu-item-card__price">$<?= e(number_format((float) $item['price'], 2)) ?></span>
                                <?php endif; ?>
                            </header>

                            <?php if ($item['description'] !== null && $item['description'] !== ''): ?>
                                <p class="menu-item-card__description"><?= e($item['description']) ?></p>
                            <?php endif; ?>

                            <?php if ($summaryBadges !== [] || $statuses !== []): ?>
                                <div class="allergen-summary">
                                    <?php foreach ($summaryBadges as $b): ?>
                                        <span class="allergen-status-badge <?= e($b['class']) ?>">
                                            <?php if ($b['name'] !== ''): ?>
                                                <span class="allergen-status-badge__name"><?= e($b['name']) ?>:</span>
                                            <?php endif; ?>
                                            <span class="allergen-status-badge__status"><?= e($b['label']) ?></span>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($statuses !== []): ?>
                                <details class="allergen-details">
                                    <summary>View all allergen details</summary>
                                    <ul class="allergen-status-list">
                                        <?php foreach ($statuses as $aSlug => $status):
                                            $aName  = $allergenNameBySlug[$aSlug] ?? $aSlug;
                                            $sLabel = $menuStatusLabel[$status] ?? ucfirst(str_replace('_', ' ', $status));
                                            $sClass = $menuStatusClass[$status] ?? 'allergen-status-badge--unknown';
                                        ?>
                                            <li>
                                                <span class="allergen-status-badge <?= $sClass ?>">
                                                    <span class="allergen-status-badge__name"><?= e($aName) ?></span>
                                                    <span class="allergen-status-badge__status"><?= e($sLabel) ?></span>
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </details>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</section>