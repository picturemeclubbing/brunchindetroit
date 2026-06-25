<?php

declare(strict_types=1);

/**
 * Shared site header. Supports optional SEO / Open Graph / Twitter / JSON-LD
 * metadata via the variables listed below. Every variable is optional.
 *
 * @var string              $pageTitle
 * @var string|null         $metaDescription
 * @var string|null         $canonicalUrl
 * @var string|null         $ogTitle
 * @var string|null         $ogDescription
 * @var string|null         $ogType
 * @var string|null         $ogUrl
 * @var string|null         $ogImage
 * @var string|null         $twitterCard
 * @var string|null         $twitterTitle
 * @var string|null         $twitterDescription
 * @var string|null         $twitterImage
 * @var string|null         $articlePublishedTime
 * @var string|null         $jsonLd
 */

$metaDescription = $metaDescription ?? 'Find your next brunch obsession in Detroit.';
$resolvedTitle   = isset($pageTitle) ? page_title($pageTitle) : page_title('Detroit Brunch');

$showAdminNavLink = false;
try {
    $showAdminNavLink = function_exists('admin_is_logged_in') && admin_is_logged_in();
} catch (Throwable $ex) {
    $showAdminNavLink = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($resolvedTitle) ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">

    <?php if (!empty($canonicalUrl)): ?>
        <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <?php endif; ?>

    <?php if (!empty($ogTitle) || !empty($ogDescription) || !empty($ogUrl)): ?>
        <!-- Open Graph -->
        <?php if (!empty($ogTitle)): ?>
            <meta property="og:title" content="<?= e($ogTitle) ?>">
        <?php endif; ?>
        <?php if (!empty($ogDescription)): ?>
            <meta property="og:description" content="<?= e($ogDescription) ?>">
        <?php endif; ?>
        <?php if (!empty($ogType)): ?>
            <meta property="og:type" content="<?= e($ogType) ?>">
        <?php endif; ?>
        <?php if (!empty($ogUrl)): ?>
            <meta property="og:url" content="<?= e($ogUrl) ?>">
        <?php endif; ?>
        <?php if (!empty($ogImage)): ?>
            <meta property="og:image" content="<?= e($ogImage) ?>">
        <?php endif; ?>
        <?php if (!empty($articlePublishedTime)): ?>
            <meta property="article:published_time" content="<?= e($articlePublishedTime) ?>">
        <?php endif; ?>
        <meta property="og:site_name" content="<?= e(site_domain()) ?>">
    <?php endif; ?>

    <?php if (!empty($twitterTitle) || !empty($twitterDescription)): ?>
        <!-- Twitter / X card -->
        <meta name="twitter:card" content="<?= e($twitterCard ?? 'summary') ?>">
        <?php if (!empty($twitterTitle)): ?>
            <meta name="twitter:title" content="<?= e($twitterTitle) ?>">
        <?php endif; ?>
        <?php if (!empty($twitterDescription)): ?>
            <meta name="twitter:description" content="<?= e($twitterDescription) ?>">
        <?php endif; ?>
        <?php if (!empty($twitterImage)): ?>
            <meta name="twitter:image" content="<?= e($twitterImage) ?>">
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($jsonLd)): ?>
        <!-- Structured data (JSON-LD) -->
        <script type="application/ld+json"><?= $jsonLd ?></script>
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/main.css')) ?>">
</head>
<body class="site-body">
<header class="site-header">
    <div class="container site-header__inner">
        <a href="<?= e(asset_url('index.php')) ?>" class="site-logo">Brunch<span class="site-logo__accent">InDetroit</span></a>

        <nav class="site-nav site-nav--desktop" aria-label="Main navigation">
            <a href="<?= e(asset_url('index.php')) ?>" class="site-nav__link site-nav__link--active">Home</a>
            <a href="<?= e(asset_url('directory.php')) ?>" class="site-nav__link">Directory</a>
            <a href="<?= e(asset_url('gallery.php')) ?>" class="site-nav__link">Galleries</a>
            <a href="<?= e(asset_url('blog.php')) ?>" class="site-nav__link">News</a>
            <a href="<?= e(asset_url('about.php')) ?>" class="site-nav__link">About</a>
        </nav>

        <div class="site-header__cta">
            <a href="<?= e(asset_url('contact.php')) ?>" class="btn btn--accent btn--sm site-header__list-spot">
                <i class="fas fa-plus" aria-hidden="true"></i>
                List your spot
            </a>

            <?php if ($showAdminNavLink): ?>
                <a href="<?= e(admin_url('dashboard.php')) ?>" class="site-nav__link site-nav__link--admin">Admin</a>
            <?php endif; ?>

            <button type="button" class="site-header__menu-btn" id="mobileMenuButton" aria-expanded="false" aria-controls="mobileMenu" aria-label="Open menu">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <div id="mobileMenu" class="site-nav-mobile" hidden>
        <nav class="site-nav-mobile__inner" aria-label="Mobile navigation">
            <a href="<?= e(asset_url('index.php')) ?>" class="site-nav-mobile__link">Home</a>
            <a href="<?= e(asset_url('directory.php')) ?>" class="site-nav-mobile__link">Directory</a>
            <a href="<?= e(asset_url('gallery.php')) ?>" class="site-nav-mobile__link">Galleries</a>
            <a href="<?= e(asset_url('blog.php')) ?>" class="site-nav-mobile__link">News</a>
            <a href="<?= e(asset_url('about.php')) ?>" class="site-nav-mobile__link">About</a>
            <a href="<?= e(asset_url('contact.php')) ?>" class="site-nav-mobile__link site-nav-mobile__link--list-spot">
                <i class="fas fa-plus" aria-hidden="true"></i>
                List your spot
            </a>

            <?php if ($showAdminNavLink): ?>
                <a href="<?= e(admin_url('dashboard.php')) ?>" class="site-nav-mobile__link site-nav-mobile__link--admin">Admin</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="site-main">
