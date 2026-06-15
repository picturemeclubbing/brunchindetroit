<?php
declare(strict_types=1);

/**
 * Blog detail view (Phase 4A).
 *
 * Renders one published blog post with breadcrumb, hero image, body content,
 * share buttons (Facebook, Twitter, Copy Link), and a related-posts section.
 *
 * @var array<string, mixed>  $post
 * @var array<int, array<string, mixed>> $related
 * @var string $canonicalUrl
 * @var string $facebookShareUrl
 * @var string $twitterShareUrl
 * @var string $shareUrl
 * @var string $jsonLd
 */

require APP_ROOT . '/views/partials/header.php';

/** Human-readable published date. */
$formatDate = static function (?string $raw): string {
    if ($raw === null || $raw === '') {
        return '';
    }
    $ts = strtotime($raw);
    return $ts !== false ? date('F j, Y', $ts) : '';
};

/** Absolute article URL helper (used by related post cards). */
$articleUrl = static function (string $slug): string {
    return asset_url('article.php?slug=' . urlencode($slug));
};

$publishedDate = $formatDate($post['published_at'] ?? null);
$readingMinutes = max(1, (int) round(str_word_count(strip_tags((string) ($post['body'] ?? ''))) / 200));
?>

<main>
    <!-- Breadcrumb -->
    <section class="section section--tight section--muted">
        <div class="container">
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <a class="breadcrumb__link" href="<?= e(asset_url('blog.php')) ?>">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i>
                    News & Blogs
                </a>
                <?php if (!empty($post['category_name'])): ?>
                    <span class="breadcrumb__sep" aria-hidden="true">›</span>
                    <a class="breadcrumb__link" href="<?= e(asset_url('blog.php?category=' . urlencode((string) $post['category_slug']))) ?>">
                        <?= e($post['category_name']) ?>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </section>

    <!-- Article hero -->
    <article class="article-detail">
        <header class="article-detail__header">
            <div class="container">
                <?php if (!empty($post['category_name'])): ?>
                    <p class="eyebrow eyebrow--accent"><?= e($post['category_name']) ?></p>
                <?php endif; ?>

                <h1 class="article-detail__title"><?= e($post['title']) ?></h1>

                <p class="article-meta article-meta--large">
                    <?php if (!empty($post['author_name'])): ?>
                        <span class="article-meta__item">
                            <i class="fas fa-user-pen" aria-hidden="true"></i>
                            By <?= e($post['author_name']) ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($publishedDate !== ''): ?>
                        <span class="article-meta__item">
                            <i class="fas fa-calendar-day" aria-hidden="true"></i>
                            <?= e($publishedDate) ?>
                        </span>
                    <?php endif; ?>
                    <span class="article-meta__item">
                        <i class="fas fa-clock" aria-hidden="true"></i>
                        <?= $readingMinutes ?> min read
                    </span>
                </p>
            </div>
        </header>

        <?php if (!empty($post['featured_image_path'])): ?>
            <div class="article-detail__hero-wrap">
                <div class="container">
                    <div
                        class="article-detail__hero"
                        style="background-image:url('<?= e($post['featured_image_path']) ?>');"
                        role="img"
                        aria-label="<?= e($post['title']) ?>"
                    ></div>
                </div>
            </div>
        <?php endif; ?>

        <div class="container container--narrow">
            <!-- Share bar -->
            <div class="article-share" aria-label="Share this article">
                <span class="article-share__label">Share</span>
                <a
                    class="article-share__btn"
                    href="<?= e($facebookShareUrl) ?>"
                    target="_blank" rel="noopener noreferrer"
                    aria-label="Share on Facebook"
                >
                    <i class="fab fa-facebook-f" aria-hidden="true"></i>
                </a>
                <a
                    class="article-share__btn article-share__btn--twitter"
                    href="<?= e($twitterShareUrl) ?>"
                    target="_blank" rel="noopener noreferrer"
                    aria-label="Share on Twitter / X"
                >
                    <i class="fab fa-x-twitter" aria-hidden="true"></i>
                </a>
                <button
                    type="button"
                    class="article-share__btn article-share__btn--copy"
                    data-copy-link="<?= e($shareUrl) ?>"
                    aria-label="Copy link to clipboard"
                >
                    <i class="fas fa-link" aria-hidden="true"></i>
                    <span>Copy Link</span>
                </button>
            </div>

            <?php if (!empty($post['excerpt'])): ?>
                <p class="article-detail__lede"><?= e($post['excerpt']) ?></p>
            <?php endif; ?>

            <div class="article-detail__body">
                <?= $post['body'] /* trusted admin content, rendered raw */ ?>
            </div>

            <!-- Footer share (repeated for long articles) -->
            <div class="article-share article-share--bottom" aria-label="Share this article">
                <span class="article-share__label">Enjoyed this story?</span>
                <a
                    class="article-share__btn"
                    href="<?= e($facebookShareUrl) ?>"
                    target="_blank" rel="noopener noreferrer"
                    aria-label="Share on Facebook"
                >
                    <i class="fab fa-facebook-f" aria-hidden="true"></i>
                </a>
                <a
                    class="article-share__btn article-share__btn--twitter"
                    href="<?= e($twitterShareUrl) ?>"
                    target="_blank" rel="noopener noreferrer"
                    aria-label="Share on Twitter / X"
                >
                    <i class="fab fa-x-twitter" aria-hidden="true"></i>
                </a>
                <button
                    type="button"
                    class="article-share__btn article-share__btn--copy"
                    data-copy-link="<?= e($shareUrl) ?>"
                    aria-label="Copy link to clipboard"
                >
                    <i class="fas fa-link" aria-hidden="true"></i>
                    <span>Copy Link</span>
                </button>
            </div>
        </div>
    </article>

    <!-- Related articles -->
    <?php if (!empty($related)): ?>
        <section class="section section--muted">
            <div class="container">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">Related Stories</h2>
                        <p class="section-subtitle">More from the Detroit brunch world.</p>
                    </div>
                    <a class="btn btn--outline" href="<?= e(asset_url('blog.php')) ?>">
                        All Stories
                    </a>
                </div>

                <div class="article-grid">
                    <?php foreach ($related as $rp): ?>
                        <article class="article-card card card--hover">
                            <?php if (!empty($rp['featured_image_path'])): ?>
                                <a
                                    class="article-card__image"
                                    href="<?= e($articleUrl((string) $rp['slug'])) ?>"
                                    aria-label="<?= e('Read ' . $rp['title']) ?>"
                                    style="background-image:url('<?= e($rp['featured_image_path']) ?>');"
                                ></a>
                            <?php endif; ?>

                            <div class="article-card__body">
                                <?php if (!empty($rp['category_name'])): ?>
                                    <span class="badge badge--accent article-card__category">
                                        <?= e($rp['category_name']) ?>
                                    </span>
                                <?php endif; ?>

                                <h3 class="article-card__title">
                                    <a href="<?= e($articleUrl((string) $rp['slug'])) ?>">
                                        <?= e($rp['title']) ?>
                                    </a>
                                </h3>

                                <?php if (!empty($rp['excerpt'])): ?>
                                    <p class="article-card__excerpt"><?= e($rp['excerpt']) ?></p>
                                <?php endif; ?>

                                <p class="article-meta">
                                    <?php if (!empty($rp['author_name'])): ?>
                                        <span class="article-meta__item">
                                            <i class="fas fa-user-pen" aria-hidden="true"></i>
                                            <?= e($rp['author_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php $rpd = $formatDate($rp['published_at'] ?? null); ?>
                                    <?php if ($rpd !== ''): ?>
                                        <span class="article-meta__item">
                                            <i class="fas fa-calendar-day" aria-hidden="true"></i>
                                            <?= e($rpd) ?>
                                        </span>
                                    <?php endif; ?>
                                </p>

                                <a
                                    class="btn btn--outline article-card__read-more"
                                    href="<?= e($articleUrl((string) $rp['slug'])) ?>"
                                >
                                    Read More
                                    <i class="fas fa-arrow-right-long" aria-hidden="true"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php
require APP_ROOT . '/views/partials/footer.php';