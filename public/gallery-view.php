<?php

declare(strict_types=1);

/**
 * Public gallery sponsor wall.
 *
 * Visitors see a sponsor-first landing page before continuing to the external
 * gallery provider URL. Images are still hosted externally.
 */

require_once __DIR__ . '/../app/bootstrap.php';
require_once APP_ROOT . '/models/Gallery.php';
require_once APP_ROOT . '/models/SiteSetting.php';

function gallery_adwall_css_url(string $url): string
{
    return str_replace(["\\", "'", "\r", "\n"], ["\\\\", "\\'", '', ''], $url);
}

$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';
$slug = strtolower($slug);

if ($slug === '' || !preg_match('/^[a-z0-9-]+$/', $slug)) {
    http_response_code(404);
    $pageTitle = 'Gallery Not Found';
    require APP_ROOT . '/views/partials/header.php';
    ?>
    <section class="section section--muted">
        <div class="container">
            <div class="empty-state">
                <h1>Gallery Not Found</h1>
                <p>We could not find that gallery.</p>
                <a class="btn btn--primary" href="<?= e(asset_url('gallery.php')) ?>">Browse Galleries</a>
            </div>
        </div>
    </section>
    <?php
    require APP_ROOT . '/views/partials/footer.php';
    exit;
}

$gallery = Gallery::findPublishedBySlug($slug);

if ($gallery === null) {
    http_response_code(404);
    $pageTitle = 'Gallery Not Found';
    require APP_ROOT . '/views/partials/header.php';
    ?>
    <section class="section section--muted">
        <div class="container">
            <div class="empty-state">
                <h1>Gallery Not Found</h1>
                <p>This gallery may be unpublished or unavailable.</p>
                <a class="btn btn--primary" href="<?= e(asset_url('gallery.php')) ?>">Browse Galleries</a>
            </div>
        </div>
    </section>
    <?php
    require APP_ROOT . '/views/partials/footer.php';
    exit;
}

$title = (string) ($gallery['title'] ?? 'Gallery');
$description = trim((string) ($gallery['description'] ?? ''));
$coverImage = trim((string) ($gallery['cover_image_path'] ?? ''));
$galleryUrl = trim((string) ($gallery['gallery_url'] ?? ''));
$eventDate = !empty($gallery['event_date']) ? date('F j, Y', strtotime((string) $gallery['event_date'])) : '';
$location = trim((string) ($gallery['location_label'] ?? ''));
$venueName = trim((string) ($gallery['venue_name'] ?? ''));
$neighborhoodName = trim((string) ($gallery['neighborhood_name'] ?? ''));

$locationLabel = $location !== '' ? $location : ($venueName !== '' ? $venueName : $neighborhoodName);

$adWallDefaults = [
    'gallery_adwall_sponsor_label' => 'Sponsored Gallery Access',
    'gallery_adwall_sponsor_name' => 'Featured Sponsor',
    'gallery_adwall_sponsor_headline' => 'Put your brand in front of every gallery visitor.',
    'gallery_adwall_sponsor_body' => 'You could be sponsoring this gallery. This full-page sponsor wall is designed for venues, brunch specials, event sponsors, and media campaigns before visitors continue to the photo gallery.',
    'gallery_adwall_background_image_url' => '',
    'gallery_adwall_overlay_opacity' => '0.88',
    'gallery_adwall_logo_url' => '',
    'gallery_adwall_logo_alt' => 'Featured sponsor',
    'gallery_adwall_cta_label' => '',
    'gallery_adwall_cta_url' => '',
    'gallery_adwall_continue_label' => 'Continue to Photos',
    'gallery_adwall_provider_note' => 'Photos open on the external gallery provider. SmugMug works now; Media Hub can be connected later.',
    'gallery_adwall_footer_background_image_url' => '',
    'gallery_adwall_footer_overlay_color' => '#111827',
    'gallery_adwall_footer_overlay_opacity' => '0.82',
    'gallery_adwall_footer_position_x' => 'center',
    'gallery_adwall_footer_position_y' => 'center',
];

$adWallStored = SiteSetting::getMany(array_keys($adWallDefaults));
$adWallSettings = array_merge($adWallDefaults, $adWallStored);

$sponsorLabel = trim((string) $adWallSettings['gallery_adwall_sponsor_label']);
$sponsorName = trim((string) $adWallSettings['gallery_adwall_sponsor_name']);
$sponsorHeadline = trim((string) $adWallSettings['gallery_adwall_sponsor_headline']);
$sponsorBody = trim((string) $adWallSettings['gallery_adwall_sponsor_body']);
$sponsorBackgroundImageUrl = trim((string) $adWallSettings['gallery_adwall_background_image_url']);
$sponsorOverlayOpacity = is_numeric($adWallSettings['gallery_adwall_overlay_opacity'] ?? null) ? (float) $adWallSettings['gallery_adwall_overlay_opacity'] : 0.88;
$sponsorOverlayOpacity = max(0, min(1, $sponsorOverlayOpacity));
$sponsorLogoUrl = trim((string) $adWallSettings['gallery_adwall_logo_url']);
$sponsorLogoAlt = trim((string) $adWallSettings['gallery_adwall_logo_alt']);
$sponsorCtaLabel = trim((string) $adWallSettings['gallery_adwall_cta_label']);
$sponsorCtaUrl = trim((string) $adWallSettings['gallery_adwall_cta_url']);
$continueLabel = trim((string) $adWallSettings['gallery_adwall_continue_label']);
$providerNote = trim((string) $adWallSettings['gallery_adwall_provider_note']);

$galleryFooterBackgroundImageUrl = trim((string) $adWallSettings['gallery_adwall_footer_background_image_url']);
$galleryFooterOverlayColor = trim((string) $adWallSettings['gallery_adwall_footer_overlay_color']);
$galleryFooterOverlayOpacity = is_numeric($adWallSettings['gallery_adwall_footer_overlay_opacity'] ?? null) ? (float) $adWallSettings['gallery_adwall_footer_overlay_opacity'] : 0.82;
$galleryFooterOverlayOpacity = max(0, min(1, $galleryFooterOverlayOpacity));
$galleryFooterPositionX = trim((string) ($adWallSettings['gallery_adwall_footer_position_x'] ?? 'center'));
$galleryFooterPositionY = trim((string) ($adWallSettings['gallery_adwall_footer_position_y'] ?? 'center'));

if ($sponsorLabel === '') {
    $sponsorLabel = $adWallDefaults['gallery_adwall_sponsor_label'];
}
if ($sponsorName === '') {
    $sponsorName = $adWallDefaults['gallery_adwall_sponsor_name'];
}
if ($sponsorHeadline === '') {
    $sponsorHeadline = $adWallDefaults['gallery_adwall_sponsor_headline'];
}
if ($sponsorBody === '') {
    $sponsorBody = $adWallDefaults['gallery_adwall_sponsor_body'];
}
if ($sponsorLogoAlt === '') {
    $sponsorLogoAlt = $adWallDefaults['gallery_adwall_logo_alt'];
}
if ($continueLabel === '') {
    $continueLabel = $adWallDefaults['gallery_adwall_continue_label'];
}
if ($providerNote === '') {
    $providerNote = $adWallDefaults['gallery_adwall_provider_note'];
}
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $galleryFooterOverlayColor)) {
    $galleryFooterOverlayColor = $adWallDefaults['gallery_adwall_footer_overlay_color'];
}
if (!in_array($galleryFooterPositionX, ['left', 'center', 'right'], true)) {
    $galleryFooterPositionX = 'center';
}
if (!in_array($galleryFooterPositionY, ['top', 'center', 'bottom'], true)) {
    $galleryFooterPositionY = 'center';
}

$sponsorStyle = '--sponsor-overlay-opacity: ' . rtrim(rtrim(number_format($sponsorOverlayOpacity, 2, '.', ''), '0'), '.') . ';';
if ($sponsorBackgroundImageUrl !== '') {
    $sponsorStyle .= " --sponsor-bg-image: url('" . gallery_adwall_css_url($sponsorBackgroundImageUrl) . "');";
}

$footerClass = 'site-footer--gallery-adwall';
$footerStyle = '--gallery-footer-overlay-color: ' . $galleryFooterOverlayColor . '; ';
$footerStyle .= '--gallery-footer-overlay-opacity: ' . rtrim(rtrim(number_format($galleryFooterOverlayOpacity, 2, '.', ''), '0'), '.') . '; ';
$footerStyle .= '--gallery-footer-bg-position: ' . $galleryFooterPositionX . ' ' . $galleryFooterPositionY . ';';
if ($galleryFooterBackgroundImageUrl !== '') {
    $footerStyle .= " --gallery-footer-bg-image: url('" . gallery_adwall_css_url($galleryFooterBackgroundImageUrl) . "');";
}

$pageTitle = $title . ' | Gallery';
$metaDescription = $description !== ''
    ? mb_substr($description, 0, 155)
    : 'View this Detroit brunch gallery after a sponsor message.';
$canonicalUrl = canonical_url('gallery-view.php?slug=' . urlencode($slug));
$ogTitle = $pageTitle;
$ogDescription = $metaDescription;
$ogType = 'article';
$ogUrl = $canonicalUrl;
if ($coverImage !== '') {
    $ogImage = $coverImage;
}

require APP_ROOT . '/views/partials/header.php';
?>

<div class="gallery-sponsor-wall" style="<?= e($sponsorStyle) ?>">
    <div class="container gallery-sponsor-wall__inner">
        <section class="gallery-sponsor-wall__sponsor" aria-label="Sponsor message">
            <div class="gallery-sponsor-wall__sponsor-content">
                <span class="gallery-sponsor-wall__label">
                    <i class="fas fa-bullhorn" aria-hidden="true"></i>
                    <?= e($sponsorLabel) ?>
                </span>

                <?php if ($sponsorLogoUrl !== ''): ?>
                    <img class="gallery-sponsor-wall__logo" src="<?= e($sponsorLogoUrl) ?>" alt="<?= e($sponsorLogoAlt) ?>" loading="lazy">
                <?php endif; ?>

                <p class="gallery-sponsor-wall__name"><?= e($sponsorName) ?></p>
                <h1><?= e($sponsorHeadline) ?></h1>
                <p class="gallery-sponsor-wall__body"><?= e($sponsorBody) ?></p>

                <?php if ($sponsorCtaLabel !== '' && $sponsorCtaUrl !== ''): ?>
                    <a class="btn btn--light gallery-sponsor-wall__cta" href="<?= e($sponsorCtaUrl) ?>" target="_blank" rel="noopener noreferrer">
                        <?= e($sponsorCtaLabel) ?>
                        <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <span class="gallery-sponsor-wall__setup-note">
                        Want this sponsor space? Your brand could be featured here.
                    </span>
                <?php endif; ?>
            </div>
        </section>

        <aside class="gallery-sponsor-wall__exit" aria-label="Continue to gallery">
            <div class="gallery-sponsor-wall__exit-card">
                <span class="gallery-sponsor-wall__exit-eyebrow">Gallery Access</span>

                <h2><?= e($title) ?></h2>

                <div class="gallery-sponsor-wall__gallery-meta">
                    <?php if ($locationLabel !== ''): ?>
                        <span>
                            <i class="fas fa-location-dot" aria-hidden="true"></i>
                            <?= e($locationLabel) ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($eventDate !== ''): ?>
                        <span>
                            <i class="fas fa-calendar-day" aria-hidden="true"></i>
                            <?= e($eventDate) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($description !== ''): ?>
                    <p class="gallery-sponsor-wall__gallery-description"><?= e($description) ?></p>
                <?php endif; ?>

                <?php if ($coverImage !== ''): ?>
                    <img class="gallery-sponsor-wall__thumb" src="<?= e($coverImage) ?>" alt="<?= e($title) ?>" loading="lazy">
                <?php endif; ?>

                <p class="gallery-sponsor-wall__provider-note"><?= e($providerNote) ?></p>

                <?php if ($galleryUrl !== ''): ?>
                    <a class="btn btn--primary btn--large btn--block" href="<?= e(asset_url('gallery-go.php?slug=' . urlencode($slug))) ?>">
                        <i class="fas fa-images" aria-hidden="true"></i>
                        <?= e($continueLabel) ?>
                    </a>
                <?php else: ?>
                    <span class="btn btn--primary btn--large btn--block is-disabled" aria-disabled="true">
                        <i class="fas fa-clock" aria-hidden="true"></i>
                        Gallery Coming Soon
                    </span>
                <?php endif; ?>

                <a class="gallery-sponsor-wall__back" href="<?= e(asset_url('gallery.php')) ?>">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i>
                    Back to galleries
                </a>
            </div>
        </aside>
    </div>
</div>

<?php
require APP_ROOT . '/views/partials/footer.php';
