<?php

declare(strict_types=1);

/** @var string $pageTitle Document title segment before site domain */
/** @var string|null $metaDescription */
$metaDescription = $metaDescription ?? 'Find your next brunch obsession in Detroit.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(page_title($pageTitle)) ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
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
