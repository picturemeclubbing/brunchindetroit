<?php
declare(strict_types=1);

/**
 * Blog list view (Phase 4A).
 *
 * @var array  $categories
 * @var array|null $featured
 * @var array  $posts
 * @var string $selectedCategory
 * @var string|null $selectedCategoryName
 * @var bool   $showFeaturedBanner
 */

require APP_ROOT . '/views/partials/header.php';

/** Small helper: absolute article URL (used many times below). */
$articleUrl = static function (string $slug): string {
    return asset_url('article.php?slug=' . urlencode($slug));
};

/** Small helper: human-readable published date. */
$formatDate = static function (?string $raw): string {
    if ($raw === null || $raw === '') {
        return '';
    }
    $ts = strtotime($raw);
    return $ts !== false ? date('F j, Y', $ts) : '';
};

/**
 * Blog hero background image.
 * Keep this page hero static so the News & Blogs landing has consistent branding.
 * Featured article images are used in the featured slider/cards instead.
 */
$blogHeroImage = 'https://images.unsplash.com/photo-1535400845297-314126c8e0f4?auto=format&fit=crop&w=1600&q=80';

$blogCardFallbackImage = asset_url('assets/images/blog-card-fallback.png');

$resolveBlogImage = static function (?string $imagePath) use ($blogCardFallbackImage): string {
    $imagePath = trim((string) $imagePath);

    if ($imagePath === '') {
        return $blogCardFallbackImage;
    }

    if (str_starts_with($imagePath, '/')) {
        $publicFile = dirname(APP_ROOT) . '/public' . str_replace('/', DIRECTORY_SEPARATOR, $imagePath);

        if (!is_file($publicFile)) {
            return $blogCardFallbackImage;
        }
    }

    return $imagePath;
};
?>

<main>
    <!-- Page hero: real background image (featured post image when available)
         with a teal/yellow gradient overlay. Image visible, text readable. -->
    <section class="main-page-hero main-page-hero--blog" style="--hero-bg-image:url('<?= e($blogHeroImage) ?>');">
        <div class="container main-page-hero__inner">
            <div class="main-page-hero__content">
                <span class="main-page-hero__badge">
                    <i class="fas fa-newspaper" aria-hidden="true"></i>
                    Detroit Brunch Stories
                </span>
                <h1 class="main-page-hero__title">News & Blogs</h1>
                <p class="main-page-hero__subtitle">
                    Detroit brunch guides, food stories, openings, and local dining culture
                    &mdash; curated by the DetroitBrunch.com team.
                </p>
            </div>
        </div>
    </section>

    <section class="section section--muted">
        <div class="container">
            <div class="blog-layout">
                <div class="blog-main">
                    <!-- Category filter pills -->
            <nav class="blog-categories" aria-label="Blog categories">
                <a
                    class="blog-category-link<?= $selectedCategory === '' ? ' is-active' : '' ?>"
                    href="<?= e(asset_url('blog.php')) ?>"
                >
                    All
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a
                        class="blog-category-link<?= $selectedCategory === $cat['slug'] ? ' is-active' : '' ?>"
                        href="<?= e(asset_url('blog.php?category=' . urlencode((string) $cat['slug']))) ?>"
                    >
                        <?= e($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <!-- Featured articles slider/card (only on unfiltered landing) -->
            <?php if ($showFeaturedBanner && !empty($featuredPosts)): ?>
                <section class="blog-featured-slider" aria-label="Featured stories">
                    <div class="slider__viewport blog-featured-slider__viewport">
                        <div class="slider__track blog-featured-slider__track">
                            <?php foreach ($featuredPosts as $featuredItem): ?>
                                <div class="slider__slide blog-featured-slider__slide">
                                    <article class="featured-article card card--hover">
                                        <?php $featuredImage = $resolveBlogImage((string) ($featuredItem['featured_image_path'] ?? '')); ?>
                                        <a
                                            class="featured-article__image"
                                            href="<?= e($articleUrl((string) $featuredItem['slug'])) ?>"
                                            aria-label="<?= e('Read ' . $featuredItem['title']) ?>"
                                            style="background-image:url('<?= e($featuredImage) ?>');"
                                        ></a>

                                        <div class="featured-article__content">
                                            <?php if (!empty($featuredItem['category_name'])): ?>
                                                <span class="badge badge--accent"><?= e($featuredItem['category_name']) ?></span>
                                            <?php endif; ?>

                                            <h2 class="featured-article__title">
                                                <a href="<?= e($articleUrl((string) $featuredItem['slug'])) ?>">
                                                    <?= e($featuredItem['title']) ?>
                                                </a>
                                            </h2>

                                            <?php if (!empty($featuredItem['excerpt'])): ?>
                                                <p class="featured-article__excerpt"><?= e($featuredItem['excerpt']) ?></p>
                                            <?php endif; ?>

                                            <p class="article-meta">
                                                <?php if (!empty($featuredItem['author_name'])): ?>
                                                    <span class="article-meta__item">
                                                        <i class="fas fa-user-pen" aria-hidden="true"></i>
                                                        <?= e($featuredItem['author_name']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php $fd = $formatDate($featuredItem['published_at'] ?? null); ?>
                                                <?php if ($fd !== ''): ?>
                                                    <span class="article-meta__item">
                                                        <i class="fas fa-calendar-day" aria-hidden="true"></i>
                                                        <?= e($fd) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </p>

                                            <a
                                                class="btn btn--primary"
                                                href="<?= e($articleUrl((string) $featuredItem['slug'])) ?>"
                                            >
                                                <i class="fas fa-arrow-right-long" aria-hidden="true"></i>
                                                Read Featured Story
                                            </a>
                                        </div>
                                    </article>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if (count($featuredPosts) > 1): ?>
                        <button type="button" class="slider-prev blog-featured-slider__arrow blog-featured-slider__arrow--prev" aria-label="Previous featured story">
                            <i class="fas fa-chevron-left" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="slider-next blog-featured-slider__arrow blog-featured-slider__arrow--next" aria-label="Next featured story">
                            <i class="fas fa-chevron-right" aria-hidden="true"></i>
                        </button>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <!-- Posts grid -->
            <?php if (empty($posts)): ?>
                <div class="blog-empty-state empty-state">
                    <h3>No stories in this category yet</h3>
                    <p>
                        We haven't published any posts in
                        <?= $selectedCategoryName !== null ? ('<strong>' . e($selectedCategoryName) . '</strong>') : 'this category' ?>
                        yet. Check back soon for fresh Detroit brunch stories.
                    </p>
                    <a class="btn btn--outline" href="<?= e(asset_url('blog.php')) ?>">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i>
                        View All Posts
                    </a>
                </div>
            <?php else: ?>
                <div class="section-header">
                    <div>
                        <h2 class="section-title">
                            <?= $selectedCategoryName !== null ? e($selectedCategoryName) : 'Latest Stories' ?>
                        </h2>
                        <p class="section-subtitle">
                            <?= count($posts) ?> post<?= count($posts) === 1 ? '' : 's' ?>
                            <?= $selectedCategoryName !== null ? 'in this category' : 'from the DetroitBrunch.com team' ?>.
                        </p>
                    </div>
                </div>

                <div class="article-grid">
                    <?php foreach ($posts as $post): ?>
                        <article class="article-card card card--hover">
                            <?php $postImage = $resolveBlogImage((string) ($post['featured_image_path'] ?? '')); ?>
                            <a
                                class="article-card__image"
                                href="<?= e($articleUrl((string) $post['slug'])) ?>"
                                aria-label="<?= e('Read ' . $post['title']) ?>"
                                style="background-image:url('<?= e($postImage) ?>');"
                            ></a>

                            <div class="article-card__body">
                                <?php if (!empty($post['category_name'])): ?>
                                    <span class="badge badge--accent article-card__category">
                                        <?= e($post['category_name']) ?>
                                    </span>
                                <?php endif; ?>

                                <h3 class="article-card__title">
                                    <a href="<?= e($articleUrl((string) $post['slug'])) ?>">
                                        <?= e($post['title']) ?>
                                    </a>
                                </h3>

                                <?php if (!empty($post['excerpt'])): ?>
                                    <p class="article-card__excerpt"><?= e($post['excerpt']) ?></p>
                                <?php endif; ?>

                                <p class="article-meta">
                                    <?php if (!empty($post['author_name'])): ?>
                                        <span class="article-meta__item">
                                            <i class="fas fa-user-pen" aria-hidden="true"></i>
                                            <?= e($post['author_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php $pd = $formatDate($post['published_at'] ?? null); ?>
                                    <?php if ($pd !== ''): ?>
                                        <span class="article-meta__item">
                                            <i class="fas fa-calendar-day" aria-hidden="true"></i>
                                            <?= e($pd) ?>
                                        </span>
                                    <?php endif; ?>
                                </p>

                                <a
                                    class="btn btn--outline article-card__read-more"
                                    href="<?= e($articleUrl((string) $post['slug'])) ?>"
                                >
                                    Read More
                                    <i class="fas fa-arrow-right-long" aria-hidden="true"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
                </div><!-- /.blog-main -->

                <aside class="blog-sidebar" aria-label="Blog sidebar">
                    <!-- Advertisement -->
                    <div class="ad-placeholder blog-ad-placeholder">
                        <span class="ad-placeholder__label">Advertisement</span>
                        <span class="ad-placeholder__size">300 x 250</span>
                    </div>

                    <!-- Explore Detroit Brunch -->
                    <div class="blog-sidebar-card">
                        <h2 class="blog-sidebar-card__title">Explore Detroit Brunch</h2>
                        <p class="blog-sidebar-card__text">
                            Find brunch spots, menus, and allergy-aware options across Detroit.
                        </p>
                        <a class="btn btn--primary btn--block" href="<?= e(asset_url('directory.php')) ?>">
                            <i class="fas fa-compass" aria-hidden="true"></i>
                            Browse Directory
                        </a>
                    </div>

                    <!-- Categories -->
                    <div class="blog-sidebar-card">
                        <h2 class="blog-sidebar-card__title">Categories</h2>
                        <ul class="blog-sidebar-links">
                            <li>
                                <a
                                    class="blog-sidebar-links__link<?= $selectedCategory === '' ? ' is-active' : '' ?>"
                                    href="<?= e(asset_url('blog.php')) ?>"
                                >
                                    All Stories
                                </a>
                            </li>
                            <?php foreach ($categories as $cat): ?>
                                <li>
                                    <a
                                        class="blog-sidebar-links__link<?= $selectedCategory === $cat['slug'] ? ' is-active' : '' ?>"
                                        href="<?= e(asset_url('blog.php?category=' . urlencode((string) $cat['slug']))) ?>"
                                    >
                                        <?= e($cat['name']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </aside>
            </div><!-- /.blog-layout -->
        </div>
    </section>
</main>

<?php
require APP_ROOT . '/views/partials/footer.php';
