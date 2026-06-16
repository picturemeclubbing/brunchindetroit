<?php
declare(strict_types=1);

/**
 * Admin Gallery Management — add/edit (Phase 5A).
 *
 * GET  ?id=N  → edit existing (404 → redirect to list with error)
 * GET        → blank add form
 * POST       → validate → create/update → redirect to list (PRG)
 *
 * Validation failures re-render the form with sticky values + alert--danger.
 */

require_once __DIR__ . '/../../app/bootstrap.php';
require_once APP_ROOT . '/models/Gallery.php';
require_once APP_ROOT . '/models/Venue.php';

admin_require_login();

$activeNav  = 'galleries';
$debug      = (bool) (app_config()['debug'] ?? false);

// --- Determine edit id (GET only) --------------------------------------------
$editId = 0;
if (isset($_GET['id']) && is_numeric($_GET['id']) && (int) $_GET['id'] > 0) {
    $editId = (int) $_GET['id'];
}

// --- Default field values (add form) -----------------------------------------
$form = [
    'id'               => $editId,
    'title'            => '',
    'slug'             => '',
    'venue_id'         => '',
    'event_date'       => '',
    'location_label'   => '',
    'description'      => '',
    'cover_image_path' => '',
    'gallery_url'      => '',
    'is_published'     => false,
    'is_featured'      => false,
];

// --- Load existing row for edit (GET) ----------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $editId > 0) {
    try {
        $existing = Gallery::find($editId);
    } catch (Throwable $ex) {
        flash_set('error', $debug ? $ex->getMessage() : 'Could not load that gallery.');
        redirect(admin_url('galleries.php'));
    }

    if ($existing === null) {
        flash_set('error', 'That gallery could not be found.');
        redirect(admin_url('galleries.php'));
    }

    $form = [
        'id'               => (int) ($existing['id'] ?? 0),
        'title'            => (string) ($existing['title'] ?? ''),
        'slug'             => (string) ($existing['slug'] ?? ''),
        'venue_id'         => ($existing['venue_id'] ?? null) !== null ? (string) (int) $existing['venue_id'] : '',
        'event_date'       => ($existing['event_date'] ?? null) !== null ? (string) $existing['event_date'] : '',
        'location_label'   => (string) ($existing['location_label'] ?? ''),
        'description'      => (string) ($existing['description'] ?? ''),
        'cover_image_path' => (string) ($existing['cover_image_path'] ?? ''),
        'gallery_url'      => (string) ($existing['gallery_url'] ?? ''),
        'is_published'     => !empty($existing['is_published']),
        'is_featured'      => !empty($existing['is_featured']),
    ];
}

$errors    = [];   // field => message

// --- POST: validate + save ---------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_verify((string) ($_POST['csrf_token'] ?? ''))) {
        flash_set('error', 'Security check failed. Please try again.');
        redirect(admin_url('galleries.php'));
    }

    // Read raw POST into sticky form values.
    $form['title']            = trim((string) ($_POST['title'] ?? ''));
    $form['slug']             = trim((string) ($_POST['slug'] ?? ''));
    $form['venue_id']         = trim((string) ($_POST['venue_id'] ?? ''));
    $form['event_date']       = trim((string) ($_POST['event_date'] ?? ''));
    $form['location_label']   = trim((string) ($_POST['location_label'] ?? ''));
    $form['description']      = trim((string) ($_POST['description'] ?? ''));
    $form['cover_image_path'] = trim((string) ($_POST['cover_image_path'] ?? ''));
    $form['gallery_url']      = trim((string) ($_POST['gallery_url'] ?? ''));
    $form['is_published']     = isset($_POST['is_published']);
    $form['is_featured']      = isset($_POST['is_featured']);

    // On edit, keep the id from the hidden field for the update + slug check.
    $postEditId = (isset($_POST['id']) && is_numeric($_POST['id']) && (int) $_POST['id'] > 0)
        ? (int) $_POST['id']
        : 0;
    $form['id'] = $postEditId;
    $isEdit     = ($postEditId > 0);

    // --- Validate title ------------------------------------------------------
    if ($form['title'] === '') {
        $errors['title'] = 'Title is required.';
    } elseif (mb_strlen($form['title']) > 255) {
        $errors['title'] = 'Title must be 255 characters or fewer.';
    }

    // --- Validate / build slug ----------------------------------------------
    if ($form['slug'] === '') {
        // Auto-generate from title: lowercase, ascii-ish, hyphen-joined.
        $slug = strtolower($form['title']);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug) ?? '';
        $slug = preg_replace('/[\s-]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');
        $form['slug'] = $slug;
    } else {
        // Normalize a user-supplied slug.
        $slug = strtolower($form['slug']);
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug) ?? '';
        $slug = preg_replace('/-+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');
        $form['slug'] = $slug;
    }

    if ($form['slug'] === '') {
        $errors['slug'] = 'Slug could not be generated. Please enter one manually.';
    } elseif (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $form['slug'])) {
        $errors['slug'] = 'Slug may only contain lowercase letters, numbers, and single hyphens.';
    } elseif (mb_strlen($form['slug']) > 160) {
        $errors['slug'] = 'Slug must be 160 characters or fewer.';
    } else {
        // Uniqueness check (ignore this record's own id when editing).
        try {
            if (Gallery::slugExists($form['slug'], $isEdit ? $postEditId : null)) {
                $errors['slug'] = 'That slug is already in use. Choose another.';
            }
        } catch (Throwable $ex) {
            $errors['slug'] = $debug ? $ex->getMessage() : 'Could not verify slug uniqueness.';
        }
    }

    // --- Validate venue_id (optional, positive int or empty) ----------------
    $venueIdOut = null; // final value for storage
    if ($form['venue_id'] !== '') {
        if (!is_numeric($form['venue_id']) || (int) $form['venue_id'] <= 0) {
            $errors['venue_id'] = 'Venue must be a valid choice or "No linked venue".';
        } else {
            $venueIdOut = (int) $form['venue_id'];
        }
    }

    // --- Validate event_date (optional, YYYY-MM-DD) -------------------------
    $eventDateOut = null;
    if ($form['event_date'] !== '') {
        $dt = DateTime::createFromFormat('Y-m-d', $form['event_date']);
        $valid = ($dt !== false)
            && $dt->format('Y-m-d') === $form['event_date']
            && (DateTime::getLastErrors()['warning_count'] ?? 0) === 0;
        if (!$valid) {
            $errors['event_date'] = 'Event date must be a valid date (YYYY-MM-DD).';
        } else {
            $eventDateOut = $form['event_date'];
        }
    }

    // --- Validate text length fields ----------------------------------------
    if (mb_strlen($form['location_label']) > 200) {
        $errors['location_label'] = 'Location label must be 200 characters or fewer.';
    }
    if (mb_strlen($form['cover_image_path']) > 500) {
        $errors['cover_image_path'] = 'Cover image URL must be 500 characters or fewer.';
    }

    // --- Validate gallery_url (optional, http(s) URL) -----------------------
    $galleryUrlOut = null;
    if ($form['gallery_url'] !== '') {
        if (mb_strlen($form['gallery_url']) > 500) {
            $errors['gallery_url'] = 'Gallery URL must be 500 characters or fewer.';
        } elseif (filter_var($form['gallery_url'], FILTER_VALIDATE_URL) === false) {
            $errors['gallery_url'] = 'Gallery URL must be a valid URL.';
        } else {
            $scheme = strtolower((string) parse_url($form['gallery_url'], PHP_URL_SCHEME));
            if ($scheme !== 'http' && $scheme !== 'https') {
                $errors['gallery_url'] = 'Gallery URL must start with http:// or https://.';
            } else {
                $galleryUrlOut = $form['gallery_url'];
            }
        }
    }

    // --- Persist -------------------------------------------------------------
    if (empty($errors)) {
        $data = [
            'title'            => $form['title'],
            'slug'             => $form['slug'],
            'venue_id'         => $venueIdOut,          // null or int
            'event_date'       => $eventDateOut,        // null or 'Y-m-d'
            'location_label'   => $form['location_label'] !== '' ? $form['location_label'] : null,
            'description'      => $form['description'] !== '' ? $form['description'] : null,
            'cover_image_path' => $form['cover_image_path'] !== '' ? $form['cover_image_path'] : null,
            'gallery_url'      => $galleryUrlOut,       // null or http(s) URL
            'is_published'     => $form['is_published'] ? 1 : 0,
            'is_featured'      => $form['is_featured'] ? 1 : 0,
        ];

        try {
            if ($isEdit) {
                Gallery::update($postEditId, $data);
                flash_set('success', 'Gallery updated.');
            } else {
                Gallery::create($data);
                flash_set('success', 'Gallery added.');
            }
            redirect(admin_url('galleries.php'));
        } catch (Throwable $ex) {
            $errors['form'] = $debug ? $ex->getMessage() : 'Could not save the gallery. Please try again.';
        }
    }

    // Fall through: re-render form with sticky $form values + $errors.
}

// --- Prepare view variables ---------------------------------------------------
$pageTitle  = page_title(($form['id'] > 0 ? 'Edit Gallery' : 'Add Gallery'));
$venues     = [];
try {
    $venues = Venue::allForSelect();
} catch (Throwable $ex) {
    // Non-fatal: the dropdown will just be empty.
}

require APP_ROOT . '/views/partials/admin-header.php';
require APP_ROOT . '/views/partials/admin-sidebar.php';
require APP_ROOT . '/views/admin/gallery-edit.php';
require APP_ROOT . '/views/partials/admin-footer.php';