<?php
declare(strict_types=1);

/**
 * Public Gallery index page (Phase 4B).
 *
 * Loads bootstrap + Gallery model, reads optional ?q, ?location, ?year, ?month
 * filters, pulls published galleries + filter option lists, sets SEO metadata,
 * and renders the gallery list view. Read-only — no form handling, no detail
 * route, no local image hosting, no SmugMug API. Cards link directly to
 * external gallery_url (SmugMug, etc.) in a new tab.
 */

require_once __DIR__ . '/../app/bootstrap.php';
require_once APP_ROOT . '/models/Gallery.php';

// --- Filters (GET, server-rendered, shareable) --------------------------------
$q = '';
if (isset($_GET['q'])) {
    $candidate = trim((string) $_GET['q']);
    if ($candidate !== '' && mb_strlen($candidate) <= 80) {
        $q = $candidate;
    }
}

$location = '';
if (isset($_GET['location'])) {
    $candidate = trim((string) $_GET['location']);
    if ($candidate !== '' && mb_strlen($candidate) <= 80) {
        $location = $candidate;
    }
}

$year = null;
if (isset($_GET['year'])) {
    $candidate = (string) $_GET['year'];
    if (preg_match('/^\d{4}$/', $candidate)) {
        $y = (int) $candidate;
        if ($y > 1900 && $y < 2100) {
            $year = $y;
        }
    }
}

$month = null;
if (isset($_GET['month'])) {
    $candidate = (string) $_GET['month'];
    if (preg_match('/^\d{1,2}$/', $candidate)) {
        $m = (int) $candidate;
        if ($m >= 1 && $m <= 12) {
            $month = $m;
        }
    }
}

// Whether ANY filter is active (drives "View All" link + status text).
$hasActiveFilters = ($q !== '') || ($location !== '') || ($year !== null) || ($month !== null);

// --- Data ---------------------------------------------------------------------
$galleries  = Gallery::publishedGalleries(
    $q !== '' ? $q : null,
    $location !== '' ? $location : null,
    $year,
    $month
);
$locations  = Gallery::availableLocations();
$years      = Gallery::availableYears();

// Month names for the dropdown + status line.
$monthNames = [
    1  => 'January',
    2  => 'February',
    3  => 'March',
    4  => 'April',
    5  => 'May',
    6  => 'June',
    7  => 'July',
    8  => 'August',
    9  => 'September',
    10 => 'October',
    11 => 'November',
    12 => 'December',
];

// --- SEO metadata -------------------------------------------------------------
$pageTitle       = 'Event Gallery Archives | DetroitBrunch.com';
$metaDescription = 'Browse Detroit brunch event galleries, venue highlights, and photo collections. Search by event, venue, neighborhood, or date.';

// Build the canonical query string from the active filters only.
$canonicalParts = [];
if ($q !== '') {
    $canonicalParts['q'] = $q;
}
if ($location !== '') {
    $canonicalParts['location'] = $location;
}
if ($year !== null) {
    $canonicalParts['year'] = (string) $year;
}
if ($month !== null) {
    $canonicalParts['month'] = (string) $month;
}
$canonicalQuery = $canonicalParts !== [] ? '?' . http_build_query($canonicalParts) : '';
$canonicalUrl   = canonical_url('gallery.php' . $canonicalQuery);

$ogTitle       = 'Event Gallery Archives | DetroitBrunch.com';
$ogDescription = $metaDescription;
$ogType        = 'website';
$ogUrl         = $canonicalUrl;

// Use the first gallery cover as the OG image when available.
if (!empty($galleries) && !empty($galleries[0]['cover_image_path'])) {
    $ogImage = $galleries[0]['cover_image_path'];
}

$twitterCard        = 'summary_large_image';
$twitterTitle       = $ogTitle;
$twitterDescription = $ogDescription;
if (isset($ogImage)) {
    $twitterImage = $ogImage;
}

require APP_ROOT . '/views/gallery-list.php';