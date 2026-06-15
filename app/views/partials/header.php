<?php

declare(strict_types=1);

/**
 * Shared site header. Supports optional SEO / Open Graph / Twitter / JSON-LD
 * metadata via the variables listed below. Every variable is optional —
 * existing pages (home, directory, venue detail, admin) keep working with
 * their current defaults when these are not set.
 *
 * @var string              $pageTitle         Document title segment before site domain
 * @var string|null         $metaDescription   <meta name="description">
 * @var string|null         $canonicalUrl      <link rel="canonical">
 * @var string|null         $ogTitle           og:title
 * @var string|null         $ogDescription     og:description
 * @var string|null         $ogType            og:type (e.g. website, article)
 * @var string|null         $ogUrl             og:url
 * @var string|null         $ogImage           og:image (absolute or relative)
 * @var string|null         $twitterCard       twitter:card (default summary)
 * @var string|null         $twitterTitle      twitter:title
 * @var string|null         $twitterDescription twitter:description
 * @var string|null         $twitterImage      twitter:image
 * @var string|null         $articlePublishedTime  ISO 8601 published time (article:published_time)
 * @var string|null         $jsonLd            Raw JSON-LD payload (already JSON-encoded)
 */

// Page title: allow a fully-formed title to pass through when the caller
// already appended the site domain (e.g. SEO pages that use the brand name).
$metaDescription = $metaDescription ?? 'Find your next brunch obsession in Detroit.';
$resolvedTitle   = isset($pageTitle) ? page_title($pageTitle) : page_title('Detroit Brunch');
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/main.css')) ?>">
</head>
<body class="site-body">
<header class="site-header">
    <div class="container site-header__inner">
        <a href="<?= e(asset_url('index.php')) ?>" class="site-logo"><?= e(site_name_display()) ?></a>

        <nav class="site-nav site-nav--desktop" aria-label="Main navigation">
            <a href="<?= e(asset_url('index.php')) ?>" class="site-nav__link site-nav__link--active">Home</a>
            <a href="<?= e(asset_url('blog.php')) ?>" class="site-nav__link">News &amp; Blogs</a>
            <a href="<?= e(asset_url('gallery.php')) ?>" class="site-nav__link">Gallery</a>
            <a href="<?= e(asset_url('directory.php')) ?>" class="site-nav__link">Directory</a>
        </nav>

        <button type="button" class="site-header__menu-btn" id="mobileMenuButton" aria-expanded="false" aria-controls="mobileMenu" aria-label="Open menu">
            <i class="fas fa-bars" aria-hidden="true"></i>
        </button>
    </div>

    <div id="mobileMenu" class="site-nav-mobile" hidden>
        <nav class="site-nav-mobile__inner" aria-label="Mobile navigation">
            <a href="<?= e(asset_url('index.php')) ?>" class="site-nav-mobile__link">Home</a>
            <a href="<?= e(asset_url('blog.php')) ?>" class="site-nav-mobile__link">News &amp; Blogs</a>
            <a href="<?= e(asset_url('gallery.php')) ?>" class="site-nav-mobile__link">Gallery</a>
            <a href="<?= e(asset_url('directory.php')) ?>" class="site-nav-mobile__link">Directory</a>
        </nav>
    </div>
</header>
<main class="site-main">
