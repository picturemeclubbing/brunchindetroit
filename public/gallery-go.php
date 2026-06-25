<?php

declare(strict_types=1);

/**
 * Public gallery outbound redirect.
 *
 * Future click tracking belongs here. For now this validates the gallery and
 * redirects to its external gallery_url.
 */

require_once __DIR__ . '/../app/bootstrap.php';
require_once APP_ROOT . '/models/Gallery.php';

$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';
$slug = strtolower($slug);

if ($slug === '' || !preg_match('/^[a-z0-9-]+$/', $slug)) {
    redirect(asset_url('gallery.php'));
}

$gallery = Gallery::findPublishedBySlug($slug);
if ($gallery === null || empty($gallery['gallery_url'])) {
    redirect(asset_url('gallery-view.php?slug=' . urlencode($slug)));
}

$url = trim((string) $gallery['gallery_url']);
$scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

if (!in_array($scheme, ['http', 'https'], true)) {
    redirect(asset_url('gallery-view.php?slug=' . urlencode($slug)));
}

header('Location: ' . $url, true, 302);
exit;
