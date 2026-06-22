<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_once APP_ROOT . '/models/Venue.php';

$pageTitle = 'Detroit Brunch Directory';

// Full unfiltered list of published venues.
$allVenues = Venue::published();

$totalVenueCount = count($allVenues);

// --- Sanitize search query (q) ---------------------------------------------
$searchQuery = '';
if (isset($_GET['q']) && is_string($_GET['q'])) {
    $searchQuery = trim($_GET['q']);
    if ($searchQuery !== '') {
        $searchQuery = mb_substr($searchQuery, 0, 80);
    }
}

// --- Sanitize selected letter (letter) -------------------------------------
// Accept a single A-Z letter only; normalize to uppercase.
$selectedLetter = '';
if (isset($_GET['letter']) && is_string($_GET['letter'])) {
    $candidate = strtoupper(trim($_GET['letter']));
    if (preg_match('/^[A-Z]$/', $candidate)) {
        $selectedLetter = $candidate;
    }
}

// --- Sanitize advanced filters ---------------------------------------------
$styleFilter = '';
if (isset($_GET['style']) && is_string($_GET['style'])) {
    $styleFilter = mb_substr(trim($_GET['style']), 0, 80);
}

$favoriteFilter = '';
if (isset($_GET['favorite']) && is_string($_GET['favorite'])) {
    $favoriteFilter = mb_substr(trim($_GET['favorite']), 0, 80);
}

$whenFilter = '';
if (isset($_GET['when']) && is_string($_GET['when'])) {
    $whenFilter = mb_substr(trim($_GET['when']), 0, 80);
}

$featuredFilter = false;
if (isset($_GET['featured']) && is_string($_GET['featured'])) {
    $featuredFilter = trim($_GET['featured']) === '1';
}

// --- Available letters from full venue list --------------------------------
$availableLetters = [];
foreach ($allVenues as $v) {
    $name = isset($v['name']) && is_string($v['name']) ? ltrim($v['name']) : '';
    if ($name === '') {
        continue;
    }

    $first = mb_strtoupper(mb_substr($name, 0, 1));
    if (preg_match('/^[A-Z]$/', $first) && !in_array($first, $availableLetters, true)) {
        $availableLetters[] = $first;
    }
}
sort($availableLetters);

// --- Apply filters ----------------------------------------------------------
$venues = $allVenues;

if ($featuredFilter) {
    $venues = array_values(array_filter($venues, static function ($v): bool {
        return !empty($v['is_featured']);
    }));
}

if ($selectedLetter !== '') {
    $venues = array_values(array_filter($venues, static function ($v) use ($selectedLetter): bool {
        $name = isset($v['name']) && is_string($v['name']) ? ltrim($v['name']) : '';
        if ($name === '') {
            return false;
        }

        return mb_strtoupper(mb_substr($name, 0, 1)) === $selectedLetter;
    }));
}

if ($searchQuery !== '') {
    $needle = mb_strtolower($searchQuery);

    $venues = array_values(array_filter($venues, static function ($v) use ($needle): bool {
        $haystackParts = [
            $v['name'] ?? '',
            $v['description'] ?? '',
            $v['address_line1'] ?? '',
            $v['address_line2'] ?? '',
            $v['city'] ?? '',
            $v['state'] ?? '',
            $v['zip'] ?? '',
            $v['phone'] ?? '',
            $v['neighborhood_name'] ?? '',
            $v['price_range'] ?? '',
            $v['brunch_hours_note'] ?? '',
        ];

        $haystack = mb_strtolower(implode(' ', array_map('strval', $haystackParts)));

        return mb_strpos($haystack, $needle) !== false;
    }));
}

$advancedNeedles = array_values(array_filter([
    $styleFilter,
    $favoriteFilter,
    $whenFilter,
], static fn ($value): bool => trim((string) $value) !== ''));

if ($advancedNeedles !== []) {
    $venues = array_values(array_filter($venues, static function ($v) use ($advancedNeedles): bool {
        $haystackParts = [
            $v['name'] ?? '',
            $v['description'] ?? '',
            $v['address_line1'] ?? '',
            $v['address_line2'] ?? '',
            $v['city'] ?? '',
            $v['state'] ?? '',
            $v['zip'] ?? '',
            $v['neighborhood_name'] ?? '',
            $v['price_range'] ?? '',
            $v['brunch_hours_note'] ?? '',
        ];

        $haystack = mb_strtolower(implode(' ', array_map('strval', $haystackParts)));

        foreach ($advancedNeedles as $needle) {
            if (mb_strpos($haystack, mb_strtolower((string) $needle)) === false) {
                return false;
            }
        }

        return true;
    }));
}

// Full count after active filters.
$filteredVenueCount = count($venues);

// --- Server-side Load More pagination --------------------------------------
// Cumulative reveal: page 1 shows first 24, page 2 shows first 48, etc.
$perPage = 24;

$page = 1;
if (isset($_GET['page']) && is_string($_GET['page']) && ctype_digit($_GET['page'])) {
    $page = max(1, (int) $_GET['page']);
}

$visibleCount = $perPage * $page;
$visibleVenues = array_slice($venues, 0, $visibleCount);
$hasMore = $filteredVenueCount > $visibleCount;
$nextPage = $page + 1;

require APP_ROOT . '/views/directory.php';
