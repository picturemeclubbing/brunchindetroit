<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/models/Blog.php';

admin_require_login();

$pageTitle = 'Blog Management | Admin';
$activeNav = 'blog';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!csrf_verify((string) ($_POST['csrf_token'] ?? ''))) {
            flash_set('error', 'Security check failed. Please try again.');
            redirect(admin_url('blog.php'));
        }

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $action = (string) ($_POST['action'] ?? '');

        if ($id <= 0) {
            flash_set('error', 'Invalid blog post selected.');
            redirect(admin_url('blog.php'));
        }

        if ($action === 'publish') {
            Blog::setPublished($id, true);
            flash_set('success', 'Blog post published.');
        } elseif ($action === 'unpublish') {
            Blog::setPublished($id, false);
            flash_set('success', 'Blog post unpublished.');
        } elseif ($action === 'feature') {
            Blog::setFeatured($id, true);
            flash_set('success', 'Blog post featured.');
        } elseif ($action === 'unfeature') {
            Blog::setFeatured($id, false);
            flash_set('success', 'Blog post unfeatured.');
        } else {
            flash_set('error', 'Unknown blog action.');
        }
    } catch (Throwable $e) {
        if (defined('APP_DEBUG') && APP_DEBUG) {
            flash_set('error', $e->getMessage());
        } else {
            flash_set('error', 'Unable to update blog post. Please try again.');
        }
    }

    redirect(admin_url('blog.php'));
}

try {
    $posts = Blog::all();
} catch (Throwable $e) {
    $posts = [];

    if (defined('APP_DEBUG') && APP_DEBUG) {
        flash_set('error', $e->getMessage());
    } else {
        flash_set('error', 'Unable to load blog posts.');
    }
}

require __DIR__ . '/../../app/views/partials/admin-header.php';
require __DIR__ . '/../../app/views/partials/admin-sidebar.php';
require __DIR__ . '/../../app/views/admin/blog.php';
require __DIR__ . '/../../app/views/partials/admin-footer.php';
