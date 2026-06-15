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
?>

<main>
    <!-- Page hero -->
    <section class="page-hero blog-hero">
        <div class="container">
            <div class="page-hero__content">
                <p class="eyebrow">Detroit Brunch Stories</p>
                <h1>News & Blogs</h1>
                <p>
                    Detroit brunch guides, food stories, openings, and local dining culture
                    — curated by the DetroitBrunch.com team.
                </p>
            </div>
        </div>
    </section>

    <section class="section section--muted">
        <div class="container">
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

            <!-- Featured article (only on unfiltered landing) -->
            <?php if ($showFeaturedBanner && $featured !== null): ?>
                <article class="featured-article card card--hover">
                    <?php if (!empty($featured['featured_image_path'])): ?>
                        <a
                            class="featured-article__image"
                            href="<?= e($articleUrl((string) $featured['slug'])) ?>"
                            aria-label="<?= e('Read ' . $featured['title']) ?>"
                            style="background-image:url('<?= e($featured['featured_image_path']) ?>');"
                        ></a>
                    <?php endif; ?>

                    <div class="featured-article__content">
                        <?php if (!empty($featured['category_name'])): ?>
                            <span class="badge badge--accent"><?= e($featured['category_name']) ?></span>
                        <?php endif; ?>

                        <h2 class="featured-article__title">
                            <a href="<?= e($articleUrl((string) $featured['slug'])) ?>">
                                <?= e($featured['title']) ?>
                            </a>
                        </h2>

                        <?php if (!empty($featured['excerpt'])): ?>
                            <p class="featured-article__excerpt"><?= e($featured['excerpt']) ?></p>
                        <?php endif; ?>

                        <p class="article-meta">
                            <?php if (!empty($featured['author_name'])): ?>
                                <span class="article-meta__item">
                                    <i class="fas fa-user-pen" aria-hidden="true"></i>
                                    <?= e($featured['author_name']) ?>
                                </span>
                            <?php endif; ?>
                            <?php $fd = $formatDate($featured['published_at'] ?? null); ?>
                            <?php if ($fd !== ''): ?>
                                <span class="article-meta__item">
                                    <i class="fas fa-calendar-day" aria-hidden="true"></i>
                                    <?= e($fd) ?>
                                </span>
                            <?php endif; ?>
                        </p>

                        <a
                            class="btn btn--primary"
                            href="<?= e($articleUrl((string) $featured['slug'])) ?>"
                        >
                            <i class="fas fa-arrow-right-long" aria-hidden="true"></i>
                            Read Featured Story
                        </a>
                    </div>
                </article>
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
                            <?php if (!empty($post['featured_image_path'])): ?>
                                <a
                                    class="article-card__image"
                                    href="<?= e($articleUrl((string) $post['slug'])) ?>"
                                    aria-label="<?= e('Read ' . $post['title']) ?>"
                                    style="background-image:url('<?= e($post['featured_image_path']) ?>');"
                                ></a>
                            <?php endif; ?>

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
        </div>
    </section>
</main>

<?php
require APP_ROOT . '/views/partials/footer.php';