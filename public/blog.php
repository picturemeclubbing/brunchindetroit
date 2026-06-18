<?php
declare(strict_types=1);

/**
 * Public News & Blogs list page (Phase 4A).
 *
 * Loads bootstrap + Blog model, reads an optional ?category=<slug> filter,
 * pulls the featured post + published posts, sets SEO metadata, and renders
 * the blog list view. Read-only â€” no form handling, no comments, no ratings.
 */

require_once __DIR__ . '/../app/bootstrap.php';
require_once APP_ROOT . '/models/Blog.php';

// --- Optional category filter -------------------------------------------------
$selectedCategory = '';
if (isset($_GET['category'])) {
    $candidate = strtolower(trim((string) $_GET['category']));
    if (preg_match('/^[a-z0-9-]+$/', $candidate)) {
        $selectedCategory = $candidate;
    }
}

// --- Data ---------------------------------------------------------------------
$categories = Blog::categories();
$featured   = Blog::featuredPost();

// When a category filter is active, don't double-show the featured post;
// the list below already reflects the filter.
$posts = Blog::publishedPosts($selectedCategory !== '' ? $selectedCategory : null);

if ($featured !== null && $selectedCategory === '') {
    $featuredId = (int) ($featured['id'] ?? 0);
    if ($featuredId > 0) {
        $posts = array_values(array_filter(
            $posts,
            static fn (array $post): bool => (int) ($post['id'] ?? 0) !== $featuredId
        ));
    }
}

// If a category slug was provided but doesn't match any category, reset it
// so the "All" pill shows as active instead of a phantom category.
$selectedCategoryName = null;
if ($selectedCategory !== '') {
    foreach ($categories as $c) {
        if ((string) $c['slug'] === $selectedCategory) {
            $selectedCategoryName = (string) $c['name'];
            break;
        }
    }
    if ($selectedCategoryName === null) {
        $selectedCategory = '';
    }
}

// On a filtered category view we hide the big featured banner to keep focus
// on the filtered list. It still shows on the unfiltered landing.
$showFeaturedBanner = ($selectedCategory === '') && ($featured !== null);

// --- SEO metadata -------------------------------------------------------------
$pageTitle       = 'News & Blogs | DetroitBrunch.com';
$metaDescription = 'Detroit brunch guides, restaurant stories, food culture, openings, and local dining news from DetroitBrunch.com.';
$canonicalUrl    = canonical_url(
    $selectedCategory !== '' ? 'blog.php?category=' . $selectedCategory : 'blog.php'
);

$ogTitle       = 'News & Blogs | DetroitBrunch.com';
$ogDescription = $metaDescription;
$ogType        = 'website';
$ogUrl         = $canonicalUrl;
if ($featured !== null && !empty($featured['featured_image_path'])) {
    $ogImage = $featured['featured_image_path'];
}

$twitterCard        = 'summary_large_image';
$twitterTitle       = $ogTitle;
$twitterDescription = $ogDescription;
if (isset($ogImage)) {
    $twitterImage = $ogImage;
}

require APP_ROOT . '/views/blog-list.php';