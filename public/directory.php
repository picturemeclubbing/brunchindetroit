<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_once APP_ROOT . '/models/Venue.php';

$pageTitle = 'Detroit Brunch Directory';

// Full unfiltered list of published venues (used for available letters + total count).
$allVenues = Venue::published();

$totalVenueCount = count($allVenues);

// --- Sanitize search query (q) ---------------------------------------------
$searchQuery = '';
if (isset($_GET['q']) && is_string($_GET['q'])) {
    $searchQuery = trim($_GET['q']);
    if ($searchQuery !== '') {
        // Limit length; empty after trim means no query.
        $searchQuery = mb_substr($searchQuery, 0, 80);
    }
}

// --- Sanitize selected letter (letter) -------------------------------------
// Accept a single A–Z letter only; normalize to uppercase.
$selectedLetter = '';
if (isset($_GET['letter']) && is_string($_GET['letter'])) {
    $candidate = strtoupper(trim($_GET['letter']));
    if (preg_match('/^[A-Z]$/', $candidate)) {
        $selectedLetter = $candidate;
    }
}

// --- Available letters (from full unfiltered venue list) --------------------
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
        $name = isset($v['name']) && is_string($v['name']) ? $v['name'] : '';
        return $name !== '' && mb_strpos(mb_strtolower($name), $needle) !== false;
    }));
}

$filteredVenueCount = count($venues);

require APP_ROOT . '/views/directory.php';