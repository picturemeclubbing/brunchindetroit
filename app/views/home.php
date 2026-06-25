<?php

declare(strict_types=1);
?>
<section class="main-page-hero main-page-hero--home" style="--hero-bg-image:url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1600&q=80');">
    <div class="container main-page-hero__inner">
        <div class="main-page-hero__content">
            <span class="main-page-hero__badge">
                <i class="fas fa-utensils" aria-hidden="true"></i>
                Detroit Brunch Guide
            </span>
            <h1 class="main-page-hero__title">Find Your Next Brunch Obsession</h1>
            <p class="main-page-hero__subtitle">
                Start with where you want to brunch, then narrow it down by style, menu favorites, or time.
            </p>

        <div class="hero-search hero-search--simple" role="search" aria-label="Brunch search">
                <form class="hero-search__simple-form" method="get" action="<?= e(asset_url('directory.php')) ?>">
                    <label class="sr-only" for="hero-location-search">Search by location</label>

                    <div class="hero-search__simple-control">
                        <span class="hero-search__location-icon" aria-hidden="true">
                            <i class="fas fa-location-dot"></i>
                        </span>
                        <input
                            id="hero-location-search"
                            type="search"
                            name="q"
                            class="form-control hero-search__location-input"
                            placeholder="Brunch near me, city, address, zip, or venue..."
                            maxlength="80"
                            autocomplete="off"
                        >
                    </div>

                    <button type="submit" class="btn btn--primary hero-search__submit">
                        Search <i class="fas fa-search" aria-hidden="true"></i>
                    </button>

                    <button type="button" class="btn btn--outline hero-search__advanced-button" data-advanced-search-open>
                        Advanced
                        <i class="fas fa-sliders" aria-hidden="true"></i>
                    </button>
                </form>

                <p class="hero-search__note">Start with location. Use advanced search to narrow by featured spots, categories, menu favorites, and timing.</p>
            </div>

            <div class="advanced-search-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="advanced-search-title">
                <div class="advanced-search-modal__overlay" data-advanced-search-close></div>

                <section class="advanced-search-modal__panel" role="document">
                    <header class="advanced-search-modal__header">
                        <div>
                            <p class="advanced-search-modal__eyebrow">Advanced Search</p>
                            <h2 id="advanced-search-title" class="advanced-search-modal__title">Find the right brunch faster</h2>
                        </div>

                        <button type="button" class="advanced-search-modal__close" data-advanced-search-close aria-label="Close advanced search">
                            <i class="fas fa-xmark" aria-hidden="true"></i>
                        </button>
                    </header>

                    <div class="advanced-search-modal__body">
                        <section class="advanced-search-modal__section">
                            <div class="advanced-search-modal__section-header">
                                <h3>Featured Brunch Spots</h3>
                                <a href="<?= e(asset_url('directory.php?featured=1')) ?>">View featured</a>
                            </div>

                            <div class="advanced-featured-grid">
                                <?php
                                $advancedFeaturedItems = array_slice($spotlightItems ?? [], 0, 3);
                                ?>

                                <?php if (!empty($advancedFeaturedItems)): ?>
                                    <?php foreach ($advancedFeaturedItems as $item): ?>
                                        <?php
                                            $featuredTitle = (string) ($item['title'] ?? 'Featured Brunch Spot');
                                            $featuredUrl = (string) ($item['url'] ?? asset_url('directory.php'));
                                            $featuredImage = !empty($item['image'])
                                                ? (string) $item['image']
                                                : 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80';
                                        ?>
                                        <a class="advanced-featured-card" href="<?= e($featuredUrl) ?>">
                                            <span class="advanced-featured-card__image" style="background-image:url('<?= e($featuredImage) ?>');"></span>
                                            <span class="advanced-featured-card__content">
                                                <strong><?= e($featuredTitle) ?></strong>
                                                <small>View details</small>
                                            </span>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <a class="advanced-featured-card advanced-featured-card--empty" href="<?= e(asset_url('directory.php')) ?>">
                                        <span class="advanced-featured-card__content">
                                            <strong>Featured spots coming soon</strong>
                                            <small>Browse the full directory</small>
                                        </span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </section>

                        <section class="advanced-search-modal__section">
                            <div class="advanced-search-modal__section-header">
                                <h3>Quick Categories</h3>
                            </div>

                            <div class="advanced-category-grid">
                                <a href="<?= e(asset_url('directory.php?q=Vegan%20Brunch')) ?>"><i class="fas fa-leaf"></i> Vegan Brunch</a>
                                <a href="<?= e(asset_url('directory.php?q=Soul%20Food')) ?>"><i class="fas fa-music"></i> Soul Food</a>
                                <a href="<?= e(asset_url('directory.php?q=Rooftop')) ?>"><i class="fas fa-umbrella-beach"></i> Rooftop</a>
                                <a href="<?= e(asset_url('directory.php?q=Boozy%20Brunch')) ?>"><i class="fas fa-glass-cheers"></i> Boozy Brunch</a>
                                <a href="<?= e(asset_url('directory.php?q=Family%20Friendly')) ?>"><i class="fas fa-child"></i> Family Friendly</a>
                                <a href="<?= e(asset_url('directory.php?q=Bottomless')) ?>"><i class="fas fa-wine-glass-alt"></i> Bottomless</a>
                            </div>
                        </section>

                        <section class="advanced-search-modal__section">
                            <div class="advanced-search-modal__section-header">
                                <h3>More Filters</h3>
                            </div>

                            <form class="advanced-search-form" method="get" action="<?= e(asset_url('directory.php')) ?>">
                                <div class="advanced-search-form__grid">
                                    <label>
                                        <span>Brunch Style</span>
                                        <select name="style" class="form-control">
                                            <option value="">Any style</option>
                                            <option value="Classic Brunch">Classic Brunch</option>
                                            <option value="Soul Food">Soul Food</option>
                                            <option value="Rooftop">Rooftop / Patio</option>
                                            <option value="Upscale">Upscale</option>
                                            <option value="Casual">Casual</option>
                                            <option value="Day Party">Day Party</option>
                                            <option value="Date Spot">Date Spot</option>
                                            <option value="Family Friendly">Family Friendly</option>
                                            <option value="Sports Bar">Sports Bar</option>
                                            <option value="Cafe">Cafe</option>
                                        </select>
                                    </label>

                                    <label>
                                        <span>Menu Favorites</span>
                                        <select name="favorite" class="form-control">
                                            <option value="">Any menu favorite</option>
                                            <option value="Chicken and Waffles">Chicken &amp; Waffles</option>
                                            <option value="Pancakes">Pancakes</option>
                                            <option value="Omelets">Omelets</option>
                                            <option value="Seafood">Seafood</option>
                                            <option value="Vegan Options">Vegan Options</option>
                                            <option value="Mimosas">Mimosas</option>
                                            <option value="Cocktails">Cocktails</option>
                                            <option value="Coffee">Coffee</option>
                                        </select>
                                    </label>

                                    <label>
                                        <span>When</span>
                                        <select name="when" class="form-control">
                                            <option value="">Any time</option>
                                            <option value="Open Today">Open Today</option>
                                            <option value="Saturday">Saturday</option>
                                            <option value="Sunday">Sunday</option>
                                            <option value="Weekday">Weekday</option>
                                            <option value="Late Brunch">Late Brunch</option>
                                        </select>
                                    </label>
                                </div>

                                <div class="advanced-search-form__actions">
                                    <a class="btn btn--outline" href="<?= e(asset_url('directory.php')) ?>">View All</a>
                                    <button type="submit" class="btn btn--primary">
                                        Apply Filters <i class="fas fa-search" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </form>
                        </section>
                    </div>
                </section>
            </div>
        </div><!-- /.main-page-hero__content -->
    </div>
</section>
<section class="section section--spotlight home-spotlight-section">
    <div class="container">
        <div class="home-spotlight-layout">
            <div class="home-spotlight__slider">
                <div class="slider__viewport">
                    <div class="slider__track">
                        <?php if (!empty($spotlightItems)):
                            foreach ($spotlightItems as $item):
                                $spotImg = !empty($item['image'])
                                    ? $item['image']
                                    : 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80';
                                $spotTitle = (string) ($item['title'] ?? '');
                                $spotDesc  = (string) ($item['description'] ?? '');
                                $spotMeta  = is_array($item['meta'] ?? null) ? $item['meta'] : [];
                                $isExternal = !empty($item['external']);
                        ?>
                            <div class="slider__slide">
                                <article class="home-feature-card home-feature-card--overlay" style="background-image:url('<?= e($spotImg) ?>');">
                                    <div class="home-feature-card__overlay" aria-hidden="true"></div>
                                    <div class="home-feature-card__body">
                                        <h3 class="home-feature-card__title"><?= e($spotTitle) ?></h3>
                                        <?php if ($spotDesc !== ''): ?>
                                            <p class="home-feature-card__text"><?= e($spotDesc) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($spotMeta)): ?>
                                            <p class="home-feature-card__meta">
                                                <?php foreach ($spotMeta as $i => $part): ?>
                                                    <?= $i > 0 ? ' <span class="home-feature-card__meta-sep" aria-hidden="true">&middot;</span> ' : '' ?>
                                                    <span class="home-feature-card__meta-item"><?= e((string) $part) ?></span>
                                                <?php endforeach; ?>
                                            </p>
                                        <?php endif; ?>
                                        <div class="home-feature-card__actions">
                                            <a href="<?= e($item['url']) ?>" class="btn btn--primary"<?php if (!empty($isExternal)): ?> target="_blank" rel="noopener"<?php endif; ?>>
                                                <?= e($item['cta_label'] ?? 'View Details') ?>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="slider__slide">
                                <article class="home-feature-card home-feature-card--overlay home-feature-card--fallback">
                                    <div class="home-feature-card__overlay" aria-hidden="true"></div>
                                    <div class="home-feature-card__body">
                                        <h3 class="home-feature-card__title">Featured stories coming soon</h3>
                                        <p class="home-feature-card__text">We're curating Detroit brunch spots, stories, and galleries for this space.</p>
                                        <div class="home-feature-card__actions">
                                            <a href="<?= e(asset_url('directory.php')) ?>" class="btn btn--primary">Browse the Directory</a>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (count($spotlightItems) > 1): ?>
                    <button type="button" class="home-spotlight__arrow home-spotlight__arrow--prev spotlight-prev" aria-label="Previous spotlight">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="home-spotlight__arrow home-spotlight__arrow--next spotlight-next" aria-label="Next spotlight">
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </button>
                <?php endif; ?>
            </div>

            <aside class="home-sponsor-card">
                <span class="home-sponsor-card__badge">Sponsor</span>
                <h3 class="home-sponsor-card__title">Put Your Brand in Front of Hungry Brunch Lovers</h3>
                <p class="home-sponsor-card__text">Promote your business to locals exploring Detroit's best brunch spots.</p>
                <a href="mailto:hello@brunchindetroit.com" class="btn home-sponsor-card__button">Advertise With Us</a>
            </aside>
        </div>
    </div>
</section>

<section class="section home-neighborhoods-section" aria-labelledby="home-neighborhoods-heading">
    <div class="container">
        <div class="section-header home-neighborhoods-section__header">
            <div>
                <span class="home-section-eyebrow">Start local</span>
                <h2 id="home-neighborhoods-heading" class="section-title">Brunch by neighborhood</h2>
            </div>
            <a href="<?= e(asset_url('directory.php')) ?>" class="section-header__link">
                All neighborhoods <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </a>
        </div>

        <div class="home-neighborhood-strip" aria-label="Popular brunch neighborhoods">
            <a class="home-neighborhood-card home-neighborhood-card--corktown" href="<?= e(asset_url('directory.php?q=Corktown')) ?>">
                <span class="home-neighborhood-card__name">Corktown</span>
                <span class="home-neighborhood-card__note">Warm, casual, local</span>
            </a>
            <a class="home-neighborhood-card home-neighborhood-card--downtown" href="<?= e(asset_url('directory.php?q=Downtown')) ?>">
                <span class="home-neighborhood-card__name">Downtown</span>
                <span class="home-neighborhood-card__note">Classic brunch + cocktails</span>
            </a>
            <a class="home-neighborhood-card home-neighborhood-card--midtown" href="<?= e(asset_url('directory.php?q=Midtown')) ?>">
                <span class="home-neighborhood-card__name">Midtown</span>
                <span class="home-neighborhood-card__note">Coffee, culture, patios</span>
            </a>
            <a class="home-neighborhood-card home-neighborhood-card--eastern" href="<?= e(asset_url('directory.php?q=Eastern%20Market')) ?>">
                <span class="home-neighborhood-card__name">Eastern Market</span>
                <span class="home-neighborhood-card__note">Weekend food energy</span>
            </a>
            <a class="home-neighborhood-card home-neighborhood-card--newcenter" href="<?= e(asset_url('directory.php?q=New%20Center')) ?>">
                <span class="home-neighborhood-card__name">New Center</span>
                <span class="home-neighborhood-card__note">Hidden gems nearby</span>
            </a>
            <a class="home-neighborhood-card home-neighborhood-card--ferndale" href="<?= e(asset_url('directory.php?q=Ferndale')) ?>">
                <span class="home-neighborhood-card__name">Ferndale</span>
                <span class="home-neighborhood-card__note">Nearby brunch favorites</span>
            </a>
        </div>
    </div>
</section>
<section class="section section--white home-featured-venues-section" aria-labelledby="home-featured-venues-heading">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="home-section-eyebrow">Hand-picked</span>
                <h2 id="home-featured-venues-heading" class="section-title">Featured brunch spots</h2>
            </div>
            <a href="<?= e(asset_url('directory.php?featured=1')) ?>" class="section-header__link">
                View Directory <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </a>
        </div>

        <div class="home-featured-venue-grid">
            <?php
            $homeFeaturedVenues = array_slice($featuredVenues ?? [], 0, 3);
            ?>

            <?php if (!empty($homeFeaturedVenues)): ?>
                <?php foreach ($homeFeaturedVenues as $venue): ?>
                    <?php
                    $venueName = (string) ($venue['name'] ?? 'Featured Brunch Spot');
                    $venueSlug = (string) ($venue['slug'] ?? '');
                    $venueUrl = $venueSlug !== ''
                        ? asset_url('venue.php?slug=' . urlencode($venueSlug))
                        : asset_url('directory.php?featured=1');

                    $venueImage = !empty($venue['main_image_path'])
                        ? (string) $venue['main_image_path']
                        : 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=900&q=80';

                    $venueNeighborhood = (string) ($venue['neighborhood_name'] ?? 'Detroit');
                    $venueHours = (string) ($venue['brunch_hours_note'] ?? 'Brunch details available');
                    $venueDescription = (string) ($venue['description'] ?? '');
                    if (mb_strlen($venueDescription) > 120) {
                        $venueDescription = mb_substr($venueDescription, 0, 117) . '...';
                    }
                    ?>
                    <article class="home-featured-venue-card">
                        <a class="home-featured-venue-card__media" href="<?= e($venueUrl) ?>">
                            <img src="<?= e($venueImage) ?>" alt="<?= e($venueName) ?>" loading="lazy">
                            <span class="home-featured-venue-card__badge">Featured</span>
                        </a>

                        <div class="home-featured-venue-card__body">
                            <p class="home-featured-venue-card__area"><?= e($venueNeighborhood) ?></p>
                            <h3 class="home-featured-venue-card__title">
                                <a href="<?= e($venueUrl) ?>"><?= e($venueName) ?></a>
                            </h3>

                            <?php if ($venueDescription !== ''): ?>
                                <p class="home-featured-venue-card__text"><?= e($venueDescription) ?></p>
                            <?php endif; ?>

                            <p class="home-featured-venue-card__meta">
                                <i class="far fa-clock" aria-hidden="true"></i>
                                <?= e($venueHours) ?>
                            </p>

                            <a class="btn btn--primary btn--sm" href="<?= e($venueUrl) ?>">View Details</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <article class="home-featured-venue-card home-featured-venue-card--empty">
                    <div class="home-featured-venue-card__body">
                        <p class="home-featured-venue-card__area">Coming Soon</p>
                        <h3 class="home-featured-venue-card__title">Featured brunch spots are being curated.</h3>
                        <p class="home-featured-venue-card__text">Browse the full directory while we highlight more Detroit brunch locations.</p>
                        <a class="btn btn--primary btn--sm" href="<?= e(asset_url('directory.php')) ?>">Browse Directory</a>
                    </div>
                </article>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section home-brunch-battle-section" aria-labelledby="home-brunch-battle-heading">
    <div class="container">
        <article class="home-brunch-battle-card">
            <div class="home-brunch-battle-card__media">
                <img
                    src="https://images.unsplash.com/photo-1504754524776-8f4f37790ca0?auto=format&fit=crop&w=1000&q=80"
                    alt="Brunch table with plates for a future Brunch Battle"
                    loading="lazy"
                >
                <span class="home-brunch-battle-card__vs">VS</span>
            </div>

            <div class="home-brunch-battle-card__body">
                <span class="home-section-eyebrow">This week's Brunch Battle</span>
                <h2 id="home-brunch-battle-heading">Chicken &amp; waffles showdown</h2>
                <p>
                    Placeholder for the future Brunch Battle feature. Once the admin workflow is ready,
                    this area can showcase two competing brunch plates, voting, judges, or video.
                </p>
                <a href="<?= e(asset_url('contact.php')) ?>" class="btn btn--accent">
                    <i class="fas fa-trophy" aria-hidden="true"></i>
                    Coming Soon
                </a>
            </div>
        </article>
    </div>
</section>
<section class="section section--muted">
    <div class="container">
        <div class="section-header"><div><span class="home-section-eyebrow">Browse by mood</span><h2 class="section-title">Brunch Categories</h2></div></div>
        <div class="grid grid--categories">
            <a href="<?= e(asset_url('directory.php')) ?>" class="category-tile">
                <i class="fas fa-leaf category-tile__icon" aria-hidden="true"></i>
                <span class="category-tile__label">Vegan Brunch</span>
            </a>
            <a href="<?= e(asset_url('directory.php')) ?>" class="category-tile">
                <i class="fas fa-music category-tile__icon" aria-hidden="true"></i>
                <span class="category-tile__label">Soul Food</span>
            </a>
            <a href="<?= e(asset_url('directory.php')) ?>" class="category-tile">
                <i class="fas fa-umbrella-beach category-tile__icon" aria-hidden="true"></i>
                <span class="category-tile__label">Rooftop</span>
            </a>
            <a href="<?= e(asset_url('directory.php')) ?>" class="category-tile">
                <i class="fas fa-glass-cheers category-tile__icon" aria-hidden="true"></i>
                <span class="category-tile__label">Boozy Brunch</span>
            </a>
            <a href="<?= e(asset_url('directory.php')) ?>" class="category-tile">
                <i class="fas fa-child category-tile__icon" aria-hidden="true"></i>
                <span class="category-tile__label">Family Friendly</span>
            </a>
            <a href="<?= e(asset_url('directory.php')) ?>" class="category-tile">
                <i class="fas fa-pepper-hot category-tile__icon" aria-hidden="true"></i>
                <span class="category-tile__label">Latin Flavors</span>
            </a>
            <a href="<?= e(asset_url('directory.php')) ?>" class="category-tile">
                <i class="fas fa-bread-slice category-tile__icon" aria-hidden="true"></i>
                <span class="category-tile__label">Gluten-Free</span>
            </a>
            <a href="<?= e(asset_url('directory.php')) ?>" class="category-tile">
                <i class="fas fa-wine-glass-alt category-tile__icon" aria-hidden="true"></i>
                <span class="category-tile__label">Bottomless</span>
            </a>
        </div>
    </div>
</section>

<section class="section section--white" aria-labelledby="home-gallery-heading">
    <div class="container">
        <div class="section-header">
            <h2 id="home-gallery-heading" class="section-title">Recent Brunch Galleries</h2>
            <a href="<?= e(asset_url('gallery.php')) ?>" class="section-header__link">
                Browse All Galleries <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </a>
        </div>

        <p class="section-intro">
            We visit brunch spots around Detroit to capture the food and the people enjoying the moment.
            Find your photos in galleries organized by <strong>location</strong> and <strong>date</strong>.
        </p>

        <div class="grid grid--galleries">
            <article class="card card--gallery card--hover">
                <a href="<?= e(asset_url('gallery.php')) ?>" class="card--gallery__media-link">
                    <img
                        src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&amp;fit=crop&amp;w=1074&amp;q=80"
                        alt="Guests at Sunday Jazz Brunch, Downtown Detroit"
                        class="card--gallery__img"
                        width="1074"
                        height="720"
                        loading="lazy"
                    >
                </a>
                <div class="card__body">
                    <p class="card--gallery__venue">The Grand Brunch House</p>
                    <h3 class="card--gallery__event">Sunday Jazz Brunch</h3>
                    <div class="tag-list">
                        <span class="badge badge--location">Downtown</span>
                    </div>
                    <p class="card--gallery__date">
                        <i class="far fa-calendar-alt" aria-hidden="true"></i>
                        <time datetime="2023-06-18">June 18, 2023</time>
                    </p>
                    <a href="<?= e(asset_url('gallery.php')) ?>" class="btn btn--primary btn--block">View Gallery</a>
                </div>
            </article>

            <article class="card card--gallery card--hover">
                <a href="<?= e(asset_url('gallery.php')) ?>" class="card--gallery__media-link">
                    <img
                        src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&amp;fit=crop&amp;w=1170&amp;q=80"
                        alt="Rooftop brunch guests and mimosas in Midtown"
                        class="card--gallery__img"
                        width="1170"
                        height="780"
                        loading="lazy"
                    >
                </a>
                <div class="card__body">
                    <p class="card--gallery__venue">Skyline Bistro</p>
                    <h3 class="card--gallery__event">Rooftop Mimosa Party</h3>
                    <div class="tag-list">
                        <span class="badge badge--location">Midtown</span>
                    </div>
                    <p class="card--gallery__date">
                        <i class="far fa-calendar-alt" aria-hidden="true"></i>
                        <time datetime="2023-05-21">May 21, 2023</time>
                    </p>
                    <a href="<?= e(asset_url('gallery.php')) ?>" class="btn btn--primary btn--block">View Gallery</a>
                </div>
            </article>

            <article class="card card--gallery card--hover">
                <a href="<?= e(asset_url('gallery.php')) ?>" class="card--gallery__media-link">
                    <img
                        src="https://images.unsplash.com/photo-1551218808-94e220e084d2?auto=format&amp;fit=crop&amp;w=1074&amp;q=80"
                        alt="Mother&apos;s Day brunch crowd at Garden Cafe, Corktown"
                        class="card--gallery__img"
                        width="1074"
                        height="720"
                        loading="lazy"
                    >
                </a>
                <div class="card__body">
                    <p class="card--gallery__venue">Garden Caf&eacute;</p>
                    <h3 class="card--gallery__event">Mother&apos;s Day Brunch</h3>
                    <div class="tag-list">
                        <span class="badge badge--location">Corktown</span>
                    </div>
                    <p class="card--gallery__date">
                        <i class="far fa-calendar-alt" aria-hidden="true"></i>
                        <time datetime="2023-05-14">May 14, 2023</time>
                    </p>
                    <a href="<?= e(asset_url('gallery.php')) ?>" class="btn btn--primary btn--block">View Gallery</a>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="section section--muted">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Latest Brunch News</h2>
            <a href="<?= e(asset_url('blog.php')) ?>" class="section-header__link">
                View All Articles <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </a>
        </div>

        <div class="grid grid--articles">
            <article class="card card--article card--hover">
                <div class="card__media" style="background-image: url('https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&amp;fit=crop&amp;w=1153&amp;q=80');"></div>
                <div class="card__body">
                    <span class="badge">Trending</span>
                    <h3 class="card__title">Detroit&apos;s 10 Most Instagrammable Brunch Spots</h3>
                    <p class="card__text">From floral walls to neon signs, these spots offer the perfect backdrop for your brunch photos.</p>
                    <div class="card__meta">
                        <span>June 15, 2023</span>
                        <a href="<?= e(asset_url('blog.php')) ?>" class="card__link">Read More</a>
                    </div>
                </div>
            </article>
            <article class="card card--article card--hover">
                <div class="card__media" style="background-image: url('https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&amp;fit=crop&amp;w=1170&amp;q=80');"></div>
                <div class="card__body">
                    <span class="badge">Recipes</span>
                    <h3 class="card__title">How to Make Detroit-Style Cinnamon Rolls</h3>
                    <p class="card__text">Our resident pastry chef shares her secret recipe for the perfect brunch treat.</p>
                    <div class="card__meta">
                        <span>June 8, 2023</span>
                        <a href="<?= e(asset_url('blog.php')) ?>" class="card__link">Read More</a>
                    </div>
                </div>
            </article>
            <article class="card card--article card--hover">
                <div class="card__media" style="background-image: url('https://images.unsplash.com/photo-1559847844-5315695dadae?auto=format&amp;fit=crop&amp;w=1198&amp;q=80');"></div>
                <div class="card__body">
                    <span class="badge">Reviews</span>
                    <h3 class="card__title">We Tried Every Mimosa Flight in Detroit</h3>
                    <p class="card__text">Our comprehensive guide to the best mimosa flights across the city.</p>
                    <div class="card__meta">
                        <span>June 1, 2023</span>
                        <a href="<?= e(asset_url('blog.php')) ?>" class="card__link">Read More</a>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="section home-sponsor-band-section" aria-labelledby="home-sponsor-band-heading">
    <div class="container">
        <div class="home-sponsor-band">
            <span class="home-section-eyebrow">Sponsored</span>
            <h2 id="home-sponsor-band-heading">Put your brand in front of hungry brunch lovers</h2>
            <p>Reach locals actively choosing where to eat this weekend. Featured placement, galleries, and sponsored content options are available.</p>
            <a href="mailto:hello@brunchindetroit.com" class="btn btn--light">Advertise with us</a>
        </div>
    </div>
</section>

<section class="section section--white home-owner-cta-section" aria-labelledby="home-owner-cta-heading">
    <div class="container">
        <div class="home-owner-cta">
            <div class="home-owner-cta__media">
                <img
                    src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=900&q=80"
                    alt="Restaurant table setting for brunch venue owners"
                    loading="lazy"
                >
            </div>

            <div class="home-owner-cta__body">
                <span class="home-section-eyebrow">For venues</span>
                <h2 id="home-owner-cta-heading">Own a brunch spot?</h2>
                <p>Get listed, keep your brunch details updated, and reach Detroiters planning their next weekend move.</p>
                <div class="home-owner-cta__actions">
                    <a href="<?= e(asset_url('contact.php')) ?>" class="btn btn--primary">Get listed free</a>
                    <a href="<?= e(asset_url('directory.php')) ?>" class="btn btn--outline">View directory</a>
                </div>
            </div>
        </div>
    </div>
</section>










<script>
/* Phase 5R.2: Advanced Search Modal JS */
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.querySelector('.advanced-search-modal');
    const openButton = document.querySelector('[data-advanced-search-open]');

    if (!modal || !openButton) {
        return;
    }

    const openModal = function () {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('advanced-search-open');

        const firstFocusable = modal.querySelector('button, a, select, input');
        if (firstFocusable) {
            firstFocusable.focus();
        }
    };

    const closeModal = function () {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('advanced-search-open');
        openButton.focus();
    };

    openButton.addEventListener('click', function (event) {
        event.preventDefault();
        openModal();
    });

    modal.addEventListener('click', function (event) {
        if (event.target.closest('[data-advanced-search-close]')) {
            event.preventDefault();
            closeModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
});
</script>
