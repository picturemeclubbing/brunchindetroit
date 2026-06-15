<?php
declare(strict_types=1);

/**
 * Public article detail page (Phase 4A).
 *
 * Loads a single published blog post by slug, builds dynamic SEO metadata
 * (canonical, Open Graph, Twitter, JSON-LD BlogPosting), loads related posts,
 * and renders the article detail view. Read-only — no comments, no ratings.
 */

require_once __DIR__ . '/../app/bootstrap.php';
require_once APP_ROOT . '/models/Blog.php';

// --- Validate slug ------------------------------------------------------------
$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';

/** Renders the simple 404 "article not found" page and returns. */
$renderArticleNotFound = static function (): void {
    http_response_code(404);
    $pageTitle = 'Article not found';
    require APP_ROOT . '/views/partials/header.php';
    ?>
    <main>
        <section class="section section--muted">
            <div class="container">
                <div class="article-not-found">
                    <p class="eyebrow eyebrow--dark">404</p>
                    <h1 class="article-not-found__title">Article not found</h1>
                    <p class="article-not-found__text">
                        This story may have been moved, unpublished, or never existed.
                        Browse the latest Detroit brunch stories instead.
                    </p>
                    <a class="btn btn--primary" href="<?= e(asset_url('blog.php')) ?>">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i>
                        Back to News & Blogs
                    </a>
                </div>
            </div>
        </section>
    </main>
    <?php
    require APP_ROOT . '/views/partials/footer.php';
};

if ($slug === '') {
    $renderArticleNotFound();
    return;
}

$post = Blog::findBySlug($slug);

if ($post === null) {
    $renderArticleNotFound();
    return;
}

// --- Related posts ------------------------------------------------------------
$related = Blog::relatedPosts(
    (int) $post['id'],
    isset($post['category_id']) ? (int) $post['category_id'] : null,
    3
);

// --- SEO metadata -------------------------------------------------------------
$canonicalUrl = canonical_url('article.php?slug=' . urlencode($slug));

$pageTitle       = $post['title'] . ' | DetroitBrunch.com';
$metaDescription = trim((string) ($post['excerpt'] ?? ''));
if ($metaDescription === '') {
    // Fallback: strip HTML from body and truncate for description.
    $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($post['body'] ?? ''))));
    $metaDescription = mb_strlen($plain) > 155
        ? trim(mb_substr($plain, 0, 152)) . '…'
        : $plain;
}

$ogTitle       = $post['title'];
$ogDescription = $metaDescription;
$ogType        = 'article';
$ogUrl         = $canonicalUrl;
$ogImage       = !empty($post['featured_image_path']) ? (string) $post['featured_image_path'] : null;

$twitterCard        = 'summary_large_image';
$twitterTitle       = $ogTitle;
$twitterDescription = $ogDescription;
$twitterImage       = $ogImage;

if (!empty($post['published_at'])) {
    $articlePublishedTime = date(DATE_ATOM, strtotime((string) $post['published_at']));
}

// --- JSON-LD (schema.org/BlogPosting) -----------------------------------------
$jsonLdPayload = [
    '@context'         => 'https://schema.org',
    '@type'            => 'BlogPosting',
    'headline'         => $post['title'],
    'description'      => $metaDescription !== '' ? $metaDescription : null,
    'image'            => $ogImage,
    'datePublished'    => isset($articlePublishedTime) ? $articlePublishedTime : null,
    'dateModified'     => !empty($post['updated_at'])
        ? date(DATE_ATOM, strtotime((string) $post['updated_at']))
        : (isset($articlePublishedTime) ? $articlePublishedTime : null),
    'author'           => !empty($post['author_name'])
        ? ['@type' => 'Person', 'name' => (string) $post['author_name']]
        : null,
    'publisher'        => [
        '@type' => 'Organization',
        'name'  => 'DetroitBrunch.com',
    ],
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id'   => $canonicalUrl,
    ],
];

// Remove null fields so we don't emit empty JSON-LD properties.
$jsonLdPayload = array_filter($jsonLdPayload, static fn ($v) => $v !== null);
$jsonLd = json_encode(
    $jsonLdPayload,
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
);

// --- Share URLs ---------------------------------------------------------------
$shareUrl   = $canonicalUrl;
$shareTitle = $post['title'];
$facebookShareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($shareUrl);
$twitterShareUrl  = 'https://twitter.com/intent/tweet?text=' . rawurlencode($shareTitle) . '&url=' . rawurlencode($shareUrl);

require APP_ROOT . '/views/blog-detail.php';