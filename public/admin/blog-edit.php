<?php

declare(strict_types=1);

/**
 * Admin Blog Management — add/edit (Phase 5E).
 *
 * GET  ?id=N  → edit existing
 * GET        → blank add form
 * POST       → validate → create/update → redirect to list (PRG)
 */

require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/models/Blog.php';

admin_require_login();

$pageTitle = 'Edit Blog Post | Admin';
$activeNav = 'blog';
$debug = defined('APP_DEBUG') && APP_DEBUG;

function admin_blog_slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');
    $value = preg_replace('/-+/', '-', $value) ?? '';

    return $value;
}

function admin_blog_datetime_to_mysql(string $value): ?string
{
    $value = trim($value);
    if ($value === '') {
        return null;
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return null;
    }

    return date('Y-m-d H:i:s', $timestamp);
}

function admin_blog_datetime_for_input(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return '';
    }

    return date('Y-m-d\TH:i', $timestamp);
}

$categories = Blog::categories();

$categoryIds = [];
foreach ($categories as $category) {
    $categoryIds[(int) $category['id']] = true;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;
$post = null;
$errors = [];

if ($isEdit) {
    try {
        $post = Blog::find($id);
    } catch (Throwable $ex) {
        flash_set('error', $debug ? $ex->getMessage() : 'Could not load that blog post.');
        redirect(admin_url('blog.php'));
    }

    if ($post === null) {
        flash_set('error', 'That blog post could not be found.');
        redirect(admin_url('blog.php'));
    }

    $pageTitle = 'Edit Blog Post | Admin';
} else {
    $pageTitle = 'Add Blog Post | Admin';
}

$form = [
    'title' => '',
    'slug' => '',
    'category_id' => '',
    'excerpt' => '',
    'body' => '',
    'featured_image_path' => '',
    'published_at' => '',
    'is_published' => '0',
    'is_featured' => '0',
];

if ($post !== null) {
    $form = [
        'title' => (string) ($post['title'] ?? ''),
        'slug' => (string) ($post['slug'] ?? ''),
        'category_id' => (string) ($post['category_id'] ?? ''),
        'excerpt' => (string) ($post['excerpt'] ?? ''),
        'body' => (string) ($post['body'] ?? ''),
        'featured_image_path' => (string) ($post['featured_image_path'] ?? ''),
        'published_at' => admin_blog_datetime_for_input($post['published_at'] ?? null),
        'is_published' => (int) ($post['is_published'] ?? 0) === 1 ? '1' : '0',
        'is_featured' => (int) ($post['is_featured'] ?? 0) === 1 ? '1' : '0',
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify((string) ($_POST['csrf_token'] ?? ''))) {
        flash_set('error', 'Security check failed. Please try again.');
        redirect(admin_url('blog.php'));
    }

    $postEditId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $isEdit = $postEditId > 0;

    if ($isEdit) {
        try {
            $post = Blog::find($postEditId);
        } catch (Throwable $ex) {
            flash_set('error', $debug ? $ex->getMessage() : 'Could not load that blog post.');
            redirect(admin_url('blog.php'));
        }

        if ($post === null) {
            flash_set('error', 'That blog post could not be found.');
            redirect(admin_url('blog.php'));
        }
    }

    $form = [
        'title' => trim((string) ($_POST['title'] ?? '')),
        'slug' => trim((string) ($_POST['slug'] ?? '')),
        'category_id' => trim((string) ($_POST['category_id'] ?? '')),
        'excerpt' => trim((string) ($_POST['excerpt'] ?? '')),
        'body' => trim((string) ($_POST['body'] ?? '')),
        'featured_image_path' => trim((string) ($_POST['featured_image_path'] ?? '')),
        'published_at' => trim((string) ($_POST['published_at'] ?? '')),
        'is_published' => isset($_POST['is_published']) ? '1' : '0',
        'is_featured' => isset($_POST['is_featured']) ? '1' : '0',
    ];

    if ($form['title'] === '') {
        $errors['title'] = 'Title is required.';
    } elseif (mb_strlen($form['title']) > 255) {
        $errors['title'] = 'Title must be 255 characters or fewer.';
    }

    $slug = $form['slug'] !== '' ? admin_blog_slugify($form['slug']) : admin_blog_slugify($form['title']);
    if ($slug === '') {
        $errors['slug'] = 'Slug is required.';
    } elseif (mb_strlen($slug) > 160) {
        $errors['slug'] = 'Slug must be 160 characters or fewer.';
    } elseif (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
        $errors['slug'] = 'Slug may only contain lowercase letters, numbers, and hyphens.';
    } elseif (Blog::slugExists($slug, $isEdit ? $postEditId : null)) {
        $errors['slug'] = 'That slug is already in use.';
    }
    $form['slug'] = $slug;

    $categoryId = null;
    if ($form['category_id'] !== '') {
        $categoryId = (int) $form['category_id'];
        if ($categoryId <= 0 || !isset($categoryIds[$categoryId])) {
            $errors['category_id'] = 'Choose a valid category or leave it uncategorized.';
        }
    }

    if (mb_strlen($form['excerpt']) > 2000) {
        $errors['excerpt'] = 'Excerpt must be 2,000 characters or fewer.';
    }

    if (mb_strlen($form['featured_image_path']) > 500) {
        $errors['featured_image_path'] = 'Featured image path must be 500 characters or fewer.';
    } elseif ($form['featured_image_path'] !== '') {
        $isHttp = filter_var($form['featured_image_path'], FILTER_VALIDATE_URL) !== false
            && preg_match('/^https?:\/\//i', $form['featured_image_path']);
        $isRootRelative = str_starts_with($form['featured_image_path'], '/');

        if (!$isHttp && !$isRootRelative) {
            $errors['featured_image_path'] = 'Use an http(s) URL or a root-relative path starting with /.';
        }
    }

    $publishedAt = admin_blog_datetime_to_mysql($form['published_at']);
    if ($form['published_at'] !== '' && $publishedAt === null) {
        $errors['published_at'] = 'Use a valid published date/time.';
    }

    if ($form['is_published'] === '1' && $publishedAt === null) {
        $publishedAt = date('Y-m-d H:i:s');
        $form['published_at'] = admin_blog_datetime_for_input($publishedAt);
    }

    $currentAdmin = admin_user();
    $authorAdminId = null;

    if ($isEdit && $post !== null && !empty($post['author_admin_id'])) {
        $authorAdminId = (int) $post['author_admin_id'];
    } elseif ($currentAdmin !== null && !empty($currentAdmin['id'])) {
        $authorAdminId = (int) $currentAdmin['id'];
    }

    if (empty($errors)) {
        $data = [
            'title' => $form['title'],
            'slug' => $form['slug'],
            'category_id' => $categoryId,
            'excerpt' => $form['excerpt'],
            'body' => $form['body'],
            'featured_image_path' => $form['featured_image_path'],
            'published_at' => $publishedAt,
            'is_published' => $form['is_published'] === '1' ? 1 : 0,
            'is_featured' => $form['is_featured'] === '1' ? 1 : 0,
            'author_admin_id' => $authorAdminId,
        ];

        try {
            if ($isEdit) {
                Blog::update($postEditId, $data);
                flash_set('success', 'Blog post updated.');
            } else {
                Blog::create($data);
                flash_set('success', 'Blog post added.');
            }

            redirect(admin_url('blog.php'));
        } catch (Throwable $ex) {
            $errors['form'] = $debug ? $ex->getMessage() : 'Unable to save blog post. Please try again.';
        }
    }

    $id = $postEditId;
}

require APP_ROOT . '/views/partials/admin-header.php';
require APP_ROOT . '/views/partials/admin-sidebar.php';
require APP_ROOT . '/views/admin/blog-edit.php';
require APP_ROOT . '/views/partials/admin-footer.php';
