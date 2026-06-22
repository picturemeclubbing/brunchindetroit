<?php

declare(strict_types=1);

/**
 * Admin neighborhood management.
 *
 * Lets admins create/edit/deactivate/delete neighborhoods used by venue records.
 */

require dirname(__DIR__, 2) . '/app/bootstrap.php';
require APP_ROOT . '/models/Venue.php';

admin_require_login();

$pageTitle = 'Neighborhoods | Admin';
$activeNav = 'neighborhoods';
$debug = defined('APP_DEBUG') && APP_DEBUG;

$errors = [];
$form = [
    'id' => 0,
    'name' => '',
    'slug' => '',
    'sort_order' => '0',
    'is_active' => '1',
];

$editNeighborhood = null;

$slugify = static function (string $value): string {
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');

    return $value;
};

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
if ($editId > 0) {
    try {
        $editNeighborhood = Venue::neighborhoodFind($editId);
    } catch (Throwable $ex) {
        flash_set('error', $debug ? $ex->getMessage() : 'Could not load that neighborhood.');
        redirect(admin_url('neighborhoods.php'));
    }

    if ($editNeighborhood === null) {
        flash_set('error', 'That neighborhood could not be found.');
        redirect(admin_url('neighborhoods.php'));
    }

    $form = [
        'id' => (int) ($editNeighborhood['id'] ?? 0),
        'name' => (string) ($editNeighborhood['name'] ?? ''),
        'slug' => (string) ($editNeighborhood['slug'] ?? ''),
        'sort_order' => (string) ($editNeighborhood['sort_order'] ?? '0'),
        'is_active' => !empty($editNeighborhood['is_active']) ? '1' : '0',
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify((string) ($_POST['csrf_token'] ?? ''))) {
        flash_set('error', 'Security check failed. Please try again.');
        redirect(admin_url('neighborhoods.php'));
    }

    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'delete') {
        $deleteId = (int) ($_POST['id'] ?? 0);

        if ($deleteId <= 0) {
            flash_set('error', 'Invalid neighborhood.');
            redirect(admin_url('neighborhoods.php'));
        }

        try {
            Venue::neighborhoodDelete($deleteId);
            flash_set('success', 'Neighborhood deleted.');
        } catch (Throwable $ex) {
            flash_set('error', $debug ? $ex->getMessage() : 'That neighborhood cannot be deleted while venues use it.');
        }

        redirect(admin_url('neighborhoods.php'));
    }

    if ($action === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $form = [
            'id' => $id,
            'name' => trim((string) ($_POST['name'] ?? '')),
            'slug' => trim((string) ($_POST['slug'] ?? '')),
            'sort_order' => trim((string) ($_POST['sort_order'] ?? '0')),
            'is_active' => isset($_POST['is_active']) ? '1' : '0',
        ];

        if ($form['name'] === '') {
            $errors['name'] = 'Neighborhood name is required.';
        } elseif (mb_strlen($form['name']) > 120) {
            $errors['name'] = 'Neighborhood name must be 120 characters or fewer.';
        }

        if ($form['slug'] === '') {
            $form['slug'] = $slugify($form['name']);
        } else {
            $form['slug'] = $slugify($form['slug']);
        }

        if ($form['slug'] === '') {
            $errors['slug'] = 'Slug is required.';
        } elseif (mb_strlen($form['slug']) > 120) {
            $errors['slug'] = 'Slug must be 120 characters or fewer.';
        } elseif (Venue::neighborhoodSlugExists($form['slug'], $id > 0 ? $id : null)) {
            $errors['slug'] = 'That slug is already used by another neighborhood.';
        }

        if ($form['sort_order'] === '' || !preg_match('/^-?\d+$/', $form['sort_order'])) {
            $errors['sort_order'] = 'Sort order must be a whole number.';
        }

        if ($errors === []) {
            $data = [
                'name' => $form['name'],
                'slug' => $form['slug'],
                'sort_order' => (int) $form['sort_order'],
                'is_active' => $form['is_active'] === '1' ? 1 : 0,
            ];

            try {
                if ($id > 0) {
                    Venue::neighborhoodUpdate($id, $data);
                    flash_set('success', 'Neighborhood updated.');
                } else {
                    Venue::neighborhoodCreate($data);
                    flash_set('success', 'Neighborhood added.');
                }

                redirect(admin_url('neighborhoods.php'));
            } catch (Throwable $ex) {
                $errors['form'] = $debug ? $ex->getMessage() : 'Could not save that neighborhood.';
            }
        }
    }
}

try {
    $neighborhoods = Venue::neighborhoodsWithCounts();
} catch (Throwable $ex) {
    $neighborhoods = [];
    flash_set('error', $debug ? $ex->getMessage() : 'Could not load neighborhoods.');
}

require APP_ROOT . '/views/partials/admin-header.php';
require APP_ROOT . '/views/partials/admin-sidebar.php';
require APP_ROOT . '/views/admin/neighborhoods.php';
require APP_ROOT . '/views/partials/admin-footer.php';