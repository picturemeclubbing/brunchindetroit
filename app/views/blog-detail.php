<?php
declare(strict_types=1);

/**
 * Blog detail view (Phase 4A).
 *
 * Renders one published blog post with breadcrumb, contained article hero card,
 * body content, share buttons (Facebook, Twitter, Copy Link), and a
 * related-posts section with thumbnails.
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

$articleImage = $resolveBlogImage((string) ($post['featured_image_path'] ?? ''));

$publishedDate = $formatDate($post['published_at'] ?? null);
$readingMinutes = max(1, (int) round(str_word_count(strip_tags((string) ($post['body'] ?? ''))) / 200));
?>

<div class="blog-detail-page">
    <!-- Breadcrumb (above the article layout) -->
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= e(asset_url('blog.php')) ?>">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                News &amp; Blogs
            </a>

            <?php if (!empty($post['category_name']) && !empty($post['category_slug'])): ?>
                <span class="breadcrumb__separator" aria-hidden="true">/</span>
                <a href="<?= e(asset_url('blog.php?category=' . urlencode((string) $post['category_slug']))) ?>">
                    <?= e((string) $post['category_name']) ?>
                </a>
            <?php elseif (!empty($post['category_name'])): ?>
                <span class="breadcrumb__separator" aria-hidden="true">/</span>
                <span><?= e((string) $post['category_name']) ?></span>
            <?php endif; ?>
        </nav>
    </div>

    <div class="article-layout">
        <div class="article-main">
            <!-- Contained article hero card: title + meta + featured image together -->
            <section class="article-hero-card">
                <div class="article-hero-card__header">
                    <?php if (!empty($post['category_name'])): ?>
                        <span class="badge badge--accent"><?= e($post['category_name']) ?></span>
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
                <div class="article-hero-card__image">
                    <img
                        src="<?= e($articleImage) ?>"
                        alt="<?= e($post['title']) ?>"
                        loading="lazy"
                    >
                </div>
            </section>

            <!-- Share bar (top) -->
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

            <!-- Article body inside a polished white card -->
            <div class="article-card-shell">
                <div class="article-detail__body article-prose">
                    <?= $post['body'] /* trusted admin content, rendered raw */ ?>
                </div>
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

            <!-- Related articles (full cards in main column) -->
            <?php if (!empty($related)): ?>
                <section class="related-articles-section">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">Related Stories</h2>
                            <p class="section-subtitle">More from the Detroit brunch world.</p>
                        </div>
                        <a class="btn btn--outline" href="<?= e(asset_url('blog.php')) ?>">
                            All Stories
                        </a>
                    </div>

                    <div class="article-grid article-grid--2col">
                        <?php foreach ($related as $rp): ?>
                            <article class="article-card card card--hover">
                                <?php $relatedImage = $resolveBlogImage((string) ($rp['featured_image_path'] ?? '')); ?>
                                <a
                                    class="article-card__image"
                                    href="<?= e($articleUrl((string) $rp['slug'])) ?>"
                                    aria-label="<?= e('Read ' . $rp['title']) ?>"
                                    style="background-image:url('<?= e($relatedImage) ?>');"
                                ></a>

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
                </section>
            <?php endif; ?>
        </div><!-- /.article-main -->

        <aside class="article-sidebar" aria-label="Article sidebar">
            <!-- Advertisement -->
            <div class="ad-placeholder article-ad-placeholder">
                <span class="ad-placeholder__label">Advertisement</span>
                <span class="ad-placeholder__size">300 x 250</span>
            </div>

            <!-- Explore Directory -->
            <div class="article-sidebar-card">
                <h2 class="article-sidebar-card__title">Explore Directory</h2>
                <p class="article-sidebar-card__text">
                    Discover Detroit brunch spots, venue profiles, and allergy-aware menu details.
                </p>
                <a class="btn btn--primary btn--block" href="<?= e(asset_url('directory.php')) ?>">
                    <i class="fas fa-compass" aria-hidden="true"></i>
                    Browse Directory
                </a>
            </div>

            <!-- Related Stories (compact links with thumbnails) -->
            <?php if (!empty($related)): ?>
                <div class="article-sidebar-card">
                    <h2 class="article-sidebar-card__title">Related Stories</h2>
                    <ul class="related-story-list">
                        <?php foreach ($related as $rp): ?>
                            <li>
                                <a
                                    class="related-story-link"
                                    href="<?= e($articleUrl((string) $rp['slug'])) ?>"
                                >
                                    <?php $relatedThumb = $resolveBlogImage((string) ($rp['featured_image_path'] ?? '')); ?>
                                    <span
                                        class="related-story-link__thumb"
                                        style="background-image:url('<?= e($relatedThumb) ?>');"
                                    ></span>
                                    <span class="related-story-link__body">
                                        <?= e($rp['title']) ?>
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </aside>
    </div><!-- /.article-layout -->
</div>

<?php
require APP_ROOT . '/views/partials/footer.php';
