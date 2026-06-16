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
                Search by culture, dietary needs, vibe, or allergies
            </p>

        <div class="hero-search" role="search" aria-label="Brunch search (preview)">
            <div class="hero-search__grid">
                <label class="sr-only" for="hero-neighborhood">Neighborhood</label>
                <select id="hero-neighborhood" class="form-control" disabled>
                    <option>Neighborhood</option>
                    <option>Downtown</option>
                    <option>Midtown</option>
                    <option>Corktown</option>
                    <option>Eastern Market</option>
                </select>

                <label class="sr-only" for="hero-culture">Culture</label>
                <select id="hero-culture" class="form-control" disabled>
                    <option>Culture</option>
                    <option>Soul Food</option>
                    <option>Latin</option>
                    <option>Middle Eastern</option>
                    <option>Asian Fusion</option>
                </select>

                <label class="sr-only" for="hero-dietary">Dietary needs</label>
                <select id="hero-dietary" class="form-control" disabled>
                    <option>Dietary Needs</option>
                    <option>Vegan</option>
                    <option>Vegetarian</option>
                    <option>Gluten-Free</option>
                </select>

                <a href="<?= e(asset_url('directory.php')) ?>" class="btn btn--primary btn--block">
                    Search <i class="fas fa-search" aria-hidden="true"></i>
                </a>
            </div>
            <p class="hero-search__note">Full directory search coming soon. Browse the directory to explore spots.</p>
        </div>
        </div><!-- /.main-page-hero__content -->
    </div>
</section>

<section class="section section--white">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Featured Brunch Spots</h2>
            <div class="slider-controls">
                <button type="button" class="slider-controls__btn slider-prev" aria-label="Previous featured spot">
                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                </button>
                <button type="button" class="slider-controls__btn slider-next" aria-label="Next featured spot">
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="slider">
            <div class="slider__viewport">
                <div class="slider__track">
                    <article class="slider__slide">
                        <div class="card card--venue card--hover">
                            <div class="card__media" style="background-image: url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&amp;fit=crop&amp;w=1074&amp;q=80');"></div>
                            <div class="card__body">
                                <div class="tag-list">
                                    <span class="badge badge--accent">Rooftop</span>
                                    <span class="badge">Vegan Options</span>
                                    <span class="badge">DJ Brunch</span>
                                </div>
                                <h3 class="card__title">The Garden Rooftop</h3>
                                <p class="card__text">Elevated brunch experience with panoramic city views and botanical cocktails.</p>
                                <a href="<?= e(asset_url('directory.php')) ?>" class="card__link">View Details</a>
                            </div>
                        </div>
                    </article>
                    <article class="slider__slide">
                        <div class="card card--venue card--hover">
                            <div class="card__media" style="background-image: url('https://images.unsplash.com/photo-1559847844-5315695dadae?auto=format&amp;fit=crop&amp;w=1198&amp;q=80');"></div>
                            <div class="card__body">
                                <div class="tag-list">
                                    <span class="badge badge--accent">Soul Food</span>
                                    <span class="badge">Bottomless Mimosas</span>
                                </div>
                                <h3 class="card__title">Sweet Maple Cafe</h3>
                                <p class="card__text">Authentic Detroit soul food brunch with legendary chicken &amp; waffles.</p>
                                <a href="<?= e(asset_url('directory.php')) ?>" class="card__link">View Details</a>
                            </div>
                        </div>
                    </article>
                    <article class="slider__slide">
                        <div class="card card--venue card--hover">
                            <div class="card__media" style="background-image: url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&amp;fit=crop&amp;w=1170&amp;q=80');"></div>
                            <div class="card__body">
                                <div class="tag-list">
                                    <span class="badge badge--accent">Latin Fusion</span>
                                    <span class="badge">Gluten-Free</span>
                                    <span class="badge">Vegan</span>
                                </div>
                                <h3 class="card__title">Casa del Sol</h3>
                                <p class="card__text">Vibrant Latin-inspired brunch with bottomless sangria and live music.</p>
                                <a href="<?= e(asset_url('directory.php')) ?>" class="card__link">View Details</a>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
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
                        alt="Mother&apos;s Day brunch crowd at Garden Café, Corktown"
                        class="card--gallery__img"
                        width="1074"
                        height="720"
                        loading="lazy"
                    >
                </a>
                <div class="card__body">
                    <p class="card--gallery__venue">Garden Café</p>
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
        <h2 class="section-title section-title--spaced">Brunch Categories</h2>
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

<section class="cta-band">
    <div class="container cta-band__inner">
        <h2 class="cta-band__title">Ready to Brunch in Detroit?</h2>
        <p class="cta-band__text">Browse neighborhood spots, menus, and allergy-aware listings curated for <?= e(site_domain()) ?>.</p>
        <div class="cta-band__actions">
            <a href="<?= e(asset_url('directory.php')) ?>" class="btn btn--accent btn--lg">Browse Directory</a>
            <a href="<?= e(asset_url('contact.php')) ?>" class="btn btn--outline-light btn--lg">Contact Us</a>
        </div>
    </div>
</section>
