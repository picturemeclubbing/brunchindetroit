<?php

declare(strict_types=1);

/**
 * Admin Blog Category Management.
 *
 * GET           - list categories and show add/edit form
 * GET ?edit=N   - edit existing category
 * POST save     - create/update category
 * POST delete   - delete category only when no posts use it
 */

require_once __DIR__ . '/../../app/bootstrap.php';
require_once APP_ROOT . '/models/Blog.php';

admin_require_login();

$pageTitle = 'Blog Categories | Admin';
$activeNav = 'blog';
$debug = defined('APP_DEBUG') && APP_DEBUG;

function admin_blog_category_slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');

    return $value;
}

$errors = [];
$form = [
    'id' => '',
    'name' => '',
    'slug' => '',
    'sort_order' => '0',
];

$editCategory = null;
$editId = 0;

if (isset($_GET['edit']) && is_numeric($_GET['edit']) && (int) $_GET['edit'] > 0) {
    $editId = (int) $_GET['edit'];

    try {
        $editCategory = Blog::categoryFind($editId);
    } catch (Throwable $ex) {
        flash_set('error', $debug ? $ex->getMessage() : 'Could not load that category.');
        redirect(admin_url('blog-categories.php'));
    }

    if ($editCategory === null) {
        flash_set('error', 'That category could not be found.');
        redirect(admin_url('blog-categories.php'));
    }

    $form = [
        'id' => (string) ($editCategory['id'] ?? ''),
        'name' => (string) ($editCategory['name'] ?? ''),
        'slug' => (string) ($editCategory['slug'] ?? ''),
        'sort_order' => (string) ($editCategory['sort_order'] ?? '0'),
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify((string) ($_POST['csrf_token'] ?? ''))) {
        flash_set('error', 'Security check failed. Please try again.');
        redirect(admin_url('blog-categories.php'));
    }

    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'delete') {
        $deleteId = (isset($_POST['id']) && is_numeric($_POST['id'])) ? (int) $_POST['id'] : 0;

        if ($deleteId <= 0) {
            flash_set('error', 'Invalid category.');
            redirect(admin_url('blog-categories.php'));
        }

        try {
            Blog::categoryDelete($deleteId);
            flash_set('success', 'Blog category deleted.');
        } catch (Throwable $ex) {
            flash_set('error', $debug ? $ex->getMessage() : 'That category cannot be deleted while posts use it.');
        }

        redirect(admin_url('blog-categories.php'));
    }

    if ($action === 'save') {
        $postId = (isset($_POST['id']) && is_numeric($_POST['id']) && (int) $_POST['id'] > 0)
            ? (int) $_POST['id']
            : null;

        $form = [
            'id' => $postId !== null ? (string) $postId : '',
            'name' => trim((string) ($_POST['name'] ?? '')),
            'slug' => trim((string) ($_POST['slug'] ?? '')),
            'sort_order' => trim((string) ($_POST['sort_order'] ?? '0')),
        ];

        if ($form['name'] === '') {
            $errors['name'] = 'Category name is required.';
        } elseif (mb_strlen($form['name']) > 120) {
            $errors['name'] = 'Category name must be 120 characters or fewer.';
        }

        $slug = $form['slug'] !== '' ? admin_blog_category_slugify($form['slug']) : admin_blog_category_slugify($form['name']);
        if ($slug === '') {
            $errors['slug'] = 'Slug is required.';
        } elseif (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            $errors['slug'] = 'Slug may only contain lowercase letters, numbers, and hyphens.';
        } elseif (mb_strlen($slug) > 120) {
            $errors['slug'] = 'Slug must be 120 characters or fewer.';
        } elseif (Blog::categorySlugExists($slug, $postId)) {
            $errors['slug'] = 'That slug is already in use.';
        }
        $form['slug'] = $slug;

        if ($form['sort_order'] === '') {
            $form['sort_order'] = '0';
        }

        if (!preg_match('/^-?\d+$/', $form['sort_order'])) {
            $errors['sort_order'] = 'Sort order must be a whole number.';
        }

        if (empty($errors)) {
            $data = [
                'name' => $form['name'],
                'slug' => $form['slug'],
                'sort_order' => (int) $form['sort_order'],
            ];

            try {
                if ($postId !== null) {
                    Blog::categoryUpdate($postId, $data);
                    flash_set('success', 'Blog category updated.');
                } else {
                    Blog::categoryCreate($data);
                    flash_set('success', 'Blog category added.');
                }

                redirect(admin_url('blog-categories.php'));
            } catch (Throwable $ex) {
                $errors['form'] = $debug ? $ex->getMessage() : 'Could not save that category.';
            }
        }

        if ($postId !== null) {
            $editId = $postId;
            $editCategory = [
                'id' => $postId,
                'name' => $form['name'],
                'slug' => $form['slug'],
                'sort_order' => $form['sort_order'],
            ];
        }
    }
}

try {
    $categories = Blog::categoriesWithCounts();
} catch (Throwable $ex) {
    $categories = [];
    flash_set('error', $debug ? $ex->getMessage() : 'Could not load blog categories.');
}

require APP_ROOT . '/views/partials/admin-header.php';
require APP_ROOT . '/views/partials/admin-sidebar.php';
require APP_ROOT . '/views/admin/blog-categories.php';
require APP_ROOT . '/views/partials/admin-footer.php';