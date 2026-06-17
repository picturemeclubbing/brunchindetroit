<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once APP_ROOT . '/models/Venue.php';
require_once APP_ROOT . '/models/Blog.php';
require_once APP_ROOT . '/models/Gallery.php';

// Phase 5D: Featured Spotlight slider data.
// Each source is loaded in its own try/catch (falling back to []) so a single
// broken table never white-screens the home page. Rows are normalized into a
// unified $spotlightItems array the view can render uniformly.

try {
    $featuredVenues = Venue::featuredForHome(3);
} catch (Throwable $e) {
    $featuredVenues = [];
}

try {
    $featuredPosts = Blog::featuredForHome(3);
} catch (Throwable $e) {
    $featuredPosts = [];
}

try {
    $featuredGalleries = Gallery::featuredForHome(2);
} catch (Throwable $e) {
    $featuredGalleries = [];
}

$spotlightItems = [];

// Venues
foreach ($featuredVenues as $row) {
    $desc = (string) ($row['description'] ?? '');
    if (mb_strlen($desc) > 160) {
        $desc = mb_substr($desc, 0, 157) . '…';
    }

    $metaParts = [];
    if (!empty($row['neighborhood_name'])) {
        $metaParts[] = $row['neighborhood_name'];
    }
    if (!empty($row['brunch_hours_note'])) {
        $metaParts[] = $row['brunch_hours_note'];
    }

    $spotlightItems[] = [
        'type'       => 'venue',
        'type_label' => 'Featured Brunch Spot',
        'icon'       => 'fa-utensils',
        'title'      => (string) ($row['name'] ?? ''),
        'description'=> $desc,
        'image'      => !empty($row['main_image_path']) ? $row['main_image_path'] : '',
        'url'        => asset_url('venue.php?slug=' . urlencode((string) ($row['slug'] ?? ''))),
        'cta_label'  => 'View Details',
        'meta'       => $metaParts,
        'meta_badge' => !empty($row['neighborhood_name']) ? $row['neighborhood_name'] : '',
    ];
}

// Blog posts
foreach ($featuredPosts as $row) {
    $desc = (string) ($row['excerpt'] ?? '');
    if (mb_strlen($desc) > 160) {
        $desc = mb_substr($desc, 0, 157) . '…';
    }

    $metaParts = [];
    if (!empty($row['category_name'])) {
        $metaParts[] = $row['category_name'];
    }
    if (!empty($row['published_at'])) {
        $metaParts[] = date('M j, Y', strtotime((string) $row['published_at']));
    }

    $spotlightItems[] = [
        'type'       => 'article',
        'type_label' => 'From the Blog',
        'icon'       => 'fa-newspaper',
        'title'      => (string) ($row['title'] ?? ''),
        'description'=> $desc,
        'image'      => !empty($row['featured_image_path']) ? $row['featured_image_path'] : '',
        'url'        => asset_url('article.php?slug=' . urlencode((string) ($row['slug'] ?? ''))),
        'cta_label'  => 'Read Article',
        'meta'       => $metaParts,
        'meta_badge' => !empty($row['category_name']) ? $row['category_name'] : '',
    ];
}

// Galleries
foreach ($featuredGalleries as $row) {
    $desc = (string) ($row['description'] ?? '');
    if (mb_strlen($desc) > 160) {
        $desc = mb_substr($desc, 0, 157) . '…';
    }

    $metaParts = [];
    if (!empty($row['venue_name'])) {
        $metaParts[] = $row['venue_name'];
    } elseif (!empty($row['location_label'])) {
        $metaParts[] = $row['location_label'];
    }
    if (!empty($row['event_date'])) {
        $metaParts[] = date('M j, Y', strtotime((string) $row['event_date']));
    }

    // Galleries link to external gallery_url when set; otherwise fall back to
    // the public gallery index.
    $galleryUrl = !empty($row['gallery_url']) ? $row['gallery_url'] : asset_url('gallery.php');

    $spotlightItems[] = [
        'type'       => 'gallery',
        'type_label' => 'Brunch Gallery',
        'icon'       => 'fa-camera',
        'title'      => (string) ($row['title'] ?? ''),
        'description'=> $desc,
        'image'      => !empty($row['cover_image_path']) ? $row['cover_image_path'] : '',
        'url'        => $galleryUrl,
        'cta_label'  => 'View Gallery',
        'meta'       => $metaParts,
        'meta_badge' => $metaParts[0] ?? '',
        'external'   => !empty($row['gallery_url']),
    ];
}

// Cap the total spotlight items at 6.
if (count($spotlightItems) > 6) {
    $spotlightItems = array_slice($spotlightItems, 0, 6);
}

$pageTitle = 'Find Your Next Brunch Obsession';

require APP_ROOT . '/views/partials/header.php';
require APP_ROOT . '/views/home.php';
require APP_ROOT . '/views/partials/footer.php';
