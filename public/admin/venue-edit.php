<?php
declare(strict_types=1);

/**
 * Admin Venue Management — add/edit (Phase 5B).
 *
 * GET  ?id=N  → edit existing (not found → redirect to list with error)
 * GET        → blank add form
 * POST       → validate → create/update → redirect to list (PRG)
 *
 * Validation failures re-render the form with sticky values + alert--danger.
 */

require_once __DIR__ . '/../../app/bootstrap.php';
require_once APP_ROOT . '/models/Venue.php';

admin_require_login();

$activeNav  = 'venues';
$debug      = (bool) (app_config()['debug'] ?? false);

// Valid price_range ENUM values (mirrors schema).
$priceOptions = ['$', '$$', '$$$', '$$$$'];

// --- Determine edit id (GET only) --------------------------------------------
$editId = 0;
if (isset($_GET['id']) && is_numeric($_GET['id']) && (int) $_GET['id'] > 0) {
    $editId = (int) $_GET['id'];
}

// --- Default field values (add form) -----------------------------------------
$form = [
    'id'               => $editId,
    'name'             => '',
    'slug'             => '',
    'description'      => '',
    'hero_blurb'       => '',
    'neighborhood_id'  => '',
    'address_line1'    => '',
    'address_line2'    => '',
    'city'             => '',
    'state'            => '',
    'zip'              => '',
    'phone'            => '',
    'website_url'      => '',
    'instagram_url'    => '',
    'facebook_url'     => '',
    'main_image_path'  => '',
    'interior_image_paths' => array_fill(0, 4, ''),
    'price_range'      => '',
    'brunch_hours_note'=> '',
    'featured_sort'    => '0',
    'is_published'     => false,
    'is_featured'      => false,
];

// --- Load existing row for edit (GET) ----------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $editId > 0) {
    try {
        $existing = Venue::find($editId);
    } catch (Throwable $ex) {
        flash_set('error', $debug ? $ex->getMessage() : 'Could not load that venue.');
        redirect(admin_url('venues.php'));
    }

    if ($existing === null) {
        flash_set('error', 'That venue could not be found.');
        redirect(admin_url('venues.php'));
    }

    $form = [
        'id'               => (int) ($existing['id'] ?? 0),
        'name'             => (string) ($existing['name'] ?? ''),
        'slug'             => (string) ($existing['slug'] ?? ''),
        'description'      => (string) ($existing['description'] ?? ''),
        'hero_blurb'       => (string) ($existing['hero_blurb'] ?? ''),
        'neighborhood_id'  => ($existing['neighborhood_id'] ?? null) !== null ? (string) (int) $existing['neighborhood_id'] : '',
        'address_line1'    => (string) ($existing['address_line1'] ?? ''),
        'address_line2'    => (string) ($existing['address_line2'] ?? ''),
        'city'             => (string) ($existing['city'] ?? ''),
        'state'            => (string) ($existing['state'] ?? ''),
        'zip'              => (string) ($existing['zip'] ?? ''),
        'phone'            => (string) ($existing['phone'] ?? ''),
        'website_url'      => (string) ($existing['website_url'] ?? ''),
        'instagram_url'    => (string) ($existing['instagram_url'] ?? ''),
        'facebook_url'     => (string) ($existing['facebook_url'] ?? ''),
        'main_image_path'  => (string) ($existing['main_image_path'] ?? ''),
        'price_range'      => (string) ($existing['price_range'] ?? ''),
        'brunch_hours_note'=> (string) ($existing['brunch_hours_note'] ?? ''),
        'featured_sort'    => (string) (int) ($existing['featured_sort'] ?? 0),
        'is_published'     => !empty($existing['is_published']),
        'is_featured'      => !empty($existing['is_featured']),
        'interior_image_paths' => array_fill(0, 4, ''),
    ];

    $existingImages = Venue::imagesForVenue($editId, 4);
    foreach ($existingImages as $imageIndex => $imageRow) {
        if ($imageIndex >= 4) {
            break;
        }
        $form['interior_image_paths'][$imageIndex] = (string) ($imageRow['file_path'] ?? '');
    }
}

$errors    = [];   // field => message

/**
 * Validate an optional http(s) URL field.
 *
 * @param string $label Human label for the error message.
 * @return string|null The URL if valid, or null if empty. Sets $errors on failure.
 */
$validateUrl = static function (string $field, string $label) use (&$form, &$errors): ?string {
    if ($form[$field] === '') {
        return null;
    }
    if (mb_strlen($form[$field]) > 500) {
        $errors[$field] = $label . ' must be 500 characters or fewer.';
        return null;
    }
    if (filter_var($form[$field], FILTER_VALIDATE_URL) === false) {
        $errors[$field] = $label . ' must be a valid URL.';
        return null;
    }
    $scheme = strtolower((string) parse_url($form[$field], PHP_URL_SCHEME));
    if ($scheme !== 'http' && $scheme !== 'https') {
        $errors[$field] = $label . ' must start with http:// or https://.';
        return null;
    }
    return $form[$field];
};


/**
 * Handle one multi-file uploader for venue interior/profile photos.
 *
 * @return array<int, array{file_path:string, caption:string, sort_order:int}>|null
 */
/**
 * Handle the single profile/main image upload for a venue.
 */
function admin_venue_handle_main_image_upload(array &$errors): ?string
{
    if (empty($_FILES['main_image_upload']) || !is_array($_FILES['main_image_upload'])) {
        return null;
    }

    $file = $_FILES['main_image_upload'];
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($error !== UPLOAD_ERR_OK) {
        $errors['main_image_upload'] = 'Profile image upload failed. Please try again.';
        return null;
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    $size = (int) ($file['size'] ?? 0);

    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        $errors['main_image_upload'] = 'Uploaded profile image could not be verified.';
        return null;
    }

    $maxBytes = 5 * 1024 * 1024;
    if ($size <= 0 || $size > $maxBytes) {
        $errors['main_image_upload'] = 'Profile image must be 5MB or smaller.';
        return null;
    }

    $imageInfo = @getimagesize($tmpName);
    if ($imageInfo === false || empty($imageInfo['mime'])) {
        $errors['main_image_upload'] = 'Upload a valid profile image file.';
        return null;
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $mime = (string) $imageInfo['mime'];
    if (!isset($allowed[$mime])) {
        $errors['main_image_upload'] = 'Profile image must be JPG, PNG, or WEBP.';
        return null;
    }

    $publicRoot = realpath(__DIR__ . '/..');
    if ($publicRoot === false) {
        $errors['main_image_upload'] = 'Upload folder could not be resolved.';
        return null;
    }

    $uploadDir = $publicRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'venues';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
        $errors['main_image_upload'] = 'Upload folder could not be created.';
        return null;
    }

    if (!is_writable($uploadDir)) {
        $errors['main_image_upload'] = 'Upload folder is not writable.';
        return null;
    }

    $extension = $allowed[$mime];
    $filename = date('YmdHis') . '-venue-profile-' . bin2hex(random_bytes(8)) . '.' . $extension;
    $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmpName, $destination)) {
        $errors['main_image_upload'] = 'Uploaded profile image could not be saved.';
        return null;
    }

    return '/uploads/venues/' . $filename;
}

function admin_venue_handle_interior_image_uploads(array &$errors): ?array
{
    if (empty($_FILES['interior_image_uploads']) || !is_array($_FILES['interior_image_uploads'])) {
        return null;
    }

    $files = $_FILES['interior_image_uploads'];
    $names = $files['name'] ?? [];

    if (!is_array($names)) {
        return null;
    }

    $uploaded = [];
    $maxBytes = 5 * 1024 * 1024;
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $publicRoot = realpath(__DIR__ . '/..');
    if ($publicRoot === false) {
        $errors['interior_image_uploads'] = 'Upload folder could not be resolved.';
        return null;
    }

    $uploadDir = $publicRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'venues';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
        $errors['interior_image_uploads'] = 'Upload folder could not be created.';
        return null;
    }

    if (!is_writable($uploadDir)) {
        $errors['interior_image_uploads'] = 'Upload folder is not writable.';
        return null;
    }

    foreach ($names as $index => $name) {
        $error = (int) ($files['error'][$index] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if (count($uploaded) >= 4) {
            $errors['interior_image_uploads'] = 'Upload up to 4 interior photos.';
            return null;
        }

        if ($error !== UPLOAD_ERR_OK) {
            $errors['interior_image_uploads'] = 'One interior photo failed to upload. Please try again.';
            return null;
        }

        $tmpName = (string) ($files['tmp_name'][$index] ?? '');
        $size = (int) ($files['size'][$index] ?? 0);

        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            $errors['interior_image_uploads'] = 'Uploaded interior photo could not be verified.';
            return null;
        }

        if ($size <= 0 || $size > $maxBytes) {
            $errors['interior_image_uploads'] = 'Each interior photo must be 5MB or smaller.';
            return null;
        }

        $imageInfo = @getimagesize($tmpName);
        if ($imageInfo === false || empty($imageInfo['mime'])) {
            $errors['interior_image_uploads'] = 'Upload valid JPG, PNG, or WEBP images.';
            return null;
        }

        $mime = (string) $imageInfo['mime'];
        if (!isset($allowed[$mime])) {
            $errors['interior_image_uploads'] = 'Interior photos must be JPG, PNG, or WEBP.';
            return null;
        }

        $extension = $allowed[$mime];
        $filename = date('YmdHis') . '-venue-' . bin2hex(random_bytes(8)) . '.' . $extension;
        $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($tmpName, $destination)) {
            $errors['interior_image_uploads'] = 'Uploaded interior photo could not be saved.';
            return null;
        }

        $uploaded[] = [
            'file_path' => '/uploads/venues/' . $filename,
            'caption' => '',
            'sort_order' => count($uploaded),
        ];
    }

    return $uploaded !== [] ? $uploaded : null;
}

// --- POST: validate + save ---------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_verify((string) ($_POST['csrf_token'] ?? ''))) {
        flash_set('error', 'Security check failed. Please try again.');
        redirect(admin_url('venues.php'));
    }

    // Read raw POST into sticky form values.
    $form['name']              = trim((string) ($_POST['name'] ?? ''));
    $form['slug']              = trim((string) ($_POST['slug'] ?? ''));
    $form['description']       = trim((string) ($_POST['description'] ?? ''));
    $form['hero_blurb']        = trim((string) ($_POST['hero_blurb'] ?? ''));
    $form['neighborhood_id']   = trim((string) ($_POST['neighborhood_id'] ?? ''));
    $form['address_line1']     = trim((string) ($_POST['address_line1'] ?? ''));
    $form['address_line2']     = trim((string) ($_POST['address_line2'] ?? ''));
    $form['city']              = trim((string) ($_POST['city'] ?? ''));
    $form['state']             = trim((string) ($_POST['state'] ?? ''));
    $form['zip']               = trim((string) ($_POST['zip'] ?? ''));
    $form['phone']             = trim((string) ($_POST['phone'] ?? ''));
    $form['website_url']       = trim((string) ($_POST['website_url'] ?? ''));
    $form['instagram_url']     = trim((string) ($_POST['instagram_url'] ?? ''));
    $form['facebook_url']      = trim((string) ($_POST['facebook_url'] ?? ''));
    $form['main_image_path']   = trim((string) ($_POST['main_image_path'] ?? ''));
    $uploadedMainImagePath = admin_venue_handle_main_image_upload($errors);
    if ($uploadedMainImagePath !== null) {
        $form['main_image_path'] = $uploadedMainImagePath;
    }
    $form['price_range']       = trim((string) ($_POST['price_range'] ?? ''));
    $form['brunch_hours_note'] = trim((string) ($_POST['brunch_hours_note'] ?? ''));
    $form['featured_sort']     = trim((string) ($_POST['featured_sort'] ?? '0'));
    $form['is_published']      = isset($_POST['is_published']);
    $form['is_featured']       = isset($_POST['is_featured']);

    if (mb_strlen($form['hero_blurb']) > 300) {
        $errors['hero_blurb'] = 'Hero blurb must be 300 characters or fewer.';
    }

    // On edit, keep the id from the hidden field for the update + slug check.
    $postEditId = (isset($_POST['id']) && is_numeric($_POST['id']) && (int) $_POST['id'] > 0)
        ? (int) $_POST['id']
        : 0;
    $form['id'] = $postEditId;
    $isEdit     = ($postEditId > 0);

    // --- Validate name -------------------------------------------------------
    if ($form['name'] === '') {
        $errors['name'] = 'Name is required.';
    } elseif (mb_strlen($form['name']) > 200) {
        $errors['name'] = 'Name must be 200 characters or fewer.';
    }

    // --- Validate / build slug ----------------------------------------------
    if ($form['slug'] === '') {
        // Auto-generate from name: lowercase, ascii-ish, hyphen-joined.
        $slug = strtolower($form['name']);
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
            if (Venue::slugExists($form['slug'], $isEdit ? $postEditId : null)) {
                $errors['slug'] = 'That slug is already in use. Choose another.';
            }
        } catch (Throwable $ex) {
            $errors['slug'] = $debug ? $ex->getMessage() : 'Could not verify slug uniqueness.';
        }
    }

    // --- Validate neighborhood_id (optional, positive int or empty) ---------
    $neighborhoodIdOut = null;
    if ($form['neighborhood_id'] !== '') {
        if (!is_numeric($form['neighborhood_id']) || (int) $form['neighborhood_id'] <= 0) {
            $errors['neighborhood_id'] = 'Neighborhood must be a valid choice or "No neighborhood".';
        } else {
            $neighborhoodIdOut = (int) $form['neighborhood_id'];
        }
    }

    // --- Validate address lines ---------------------------------------------
    if (mb_strlen($form['address_line1']) > 200) {
        $errors['address_line1'] = 'Address line 1 must be 200 characters or fewer.';
    }
    if (mb_strlen($form['address_line2']) > 200) {
        $errors['address_line2'] = 'Address line 2 must be 200 characters or fewer.';
    }

    // --- Validate city/state (optional; fall back to defaults when blank) ---
    // Note: these are NOT NULL columns with defaults 'Detroit'/'MI'.
    $cityOut = $form['city'] !== '' ? $form['city'] : 'Detroit';
    $stateOut = $form['state'] !== '' ? $form['state'] : 'MI';
    if (mb_strlen($cityOut) > 100) {
        $errors['city'] = 'City must be 100 characters or fewer.';
    }
    if (mb_strlen($stateOut) > 50) {
        $errors['state'] = 'State must be 50 characters or fewer.';
    }

    // --- Validate zip, phone -------------------------------------------------
    if (mb_strlen($form['zip']) > 20) {
        $errors['zip'] = 'ZIP must be 20 characters or fewer.';
    }
    if (mb_strlen($form['phone']) > 40) {
        $errors['phone'] = 'Phone must be 40 characters or fewer.';
    }

    // --- Validate URLs -------------------------------------------------------
    $websiteUrlOut     = $validateUrl('website_url',     'Website URL');
    $instagramUrlOut   = $validateUrl('instagram_url',   'Instagram URL');
    $facebookUrlOut    = $validateUrl('facebook_url',    'Facebook URL');
    $mainImagePathOut = null;
    if ($form['main_image_path'] !== '') {
        if (mb_strlen($form['main_image_path']) > 500) {
            $errors['main_image_path'] = 'Main image URL must be 500 characters or fewer.';
        } elseif (str_starts_with($form['main_image_path'], '/uploads/venues/')) {
            $mainImagePathOut = $form['main_image_path'];
        } else {
            $mainImagePathOut = $validateUrl('main_image_path', 'Main image URL');
        }
    }

    $interiorImagesOut = admin_venue_handle_interior_image_uploads($errors);

    // --- Validate price_range (ENUM or empty) -------------------------------
    $priceRangeOut = null;
    if ($form['price_range'] !== '') {
        if (!in_array($form['price_range'], $priceOptions, true)) {
            $errors['price_range'] = 'Price range must be one of $, $$, $$$, $$$$.';
        } else {
            $priceRangeOut = $form['price_range'];
        }
    }

    // --- Validate featured_sort (optional non-negative int) -----------------
    $featuredSortOut = 0;
    if ($form['featured_sort'] !== '') {
        if (!is_numeric($form['featured_sort']) || (int) $form['featured_sort'] < 0) {
            $errors['featured_sort'] = 'Featured sort must be a whole number of 0 or higher.';
        } else {
            $featuredSortOut = (int) $form['featured_sort'];
        }
    }
    // Keep the normalized value sticky in case of other errors.
    $form['featured_sort'] = (string) $featuredSortOut;

    // --- Persist -------------------------------------------------------------
    if (empty($errors)) {
        $data = [
            'name'              => $form['name'],
            'slug'              => $form['slug'],
            'description'       => $form['description'] !== '' ? $form['description'] : null,
            'hero_blurb'        => $form['hero_blurb'] !== '' ? $form['hero_blurb'] : null,
            'neighborhood_id'   => $neighborhoodIdOut,            // null or int
            'address_line1'     => $form['address_line1'] !== '' ? $form['address_line1'] : null,
            'address_line2'     => $form['address_line2'] !== '' ? $form['address_line2'] : null,
            'city'              => $cityOut,                      // never null (NOT NULL column)
            'state'             => $stateOut,                     // never null (NOT NULL column)
            'zip'               => $form['zip'] !== '' ? $form['zip'] : null,
            'phone'             => $form['phone'] !== '' ? $form['phone'] : null,
            'website_url'       => $websiteUrlOut,                // null or http(s) URL
            'instagram_url'     => $instagramUrlOut,              // null or http(s) URL
            'facebook_url'      => $facebookUrlOut,               // null or http(s) URL
            'main_image_path'   => $mainImagePathOut,             // null or http(s) URL
            'price_range'       => $priceRangeOut,                // null or ENUM value
            'brunch_hours_note' => $form['brunch_hours_note'] !== '' ? $form['brunch_hours_note'] : null,
            'is_published'      => $form['is_published'] ? 1 : 0,
            'is_featured'       => $form['is_featured'] ? 1 : 0,
            'featured_sort'     => $featuredSortOut,
        ];

        try {
            if ($isEdit) {
                Venue::update($postEditId, $data);
                $savedVenueId = $postEditId;
                flash_set('success', 'Venue updated.');
            } else {
                $savedVenueId = Venue::create($data);
                flash_set('success', 'Venue added.');
            }

            if ($interiorImagesOut !== null) {
                Venue::replaceImages((int) $savedVenueId, $interiorImagesOut);
            }

            redirect(admin_url('venues.php'));
        } catch (Throwable $ex) {
            $errors['form'] = $debug ? $ex->getMessage() : 'Could not save the venue. Please try again.';
        }
    }

    // Fall through: re-render form with sticky $form values + $errors.
}

// --- Prepare view variables ---------------------------------------------------
$pageTitle    = page_title(($form['id'] > 0 ? 'Edit Venue' : 'Add Venue'));
$neighborhoods = [];
try {
    $neighborhoods = Venue::neighborhoodsForSelect();
} catch (Throwable $ex) {
    // Non-fatal: the dropdown will just be empty.
}

require APP_ROOT . '/views/partials/admin-header.php';
require APP_ROOT . '/views/partials/admin-sidebar.php';
require APP_ROOT . '/views/admin/venue-edit.php';
require APP_ROOT . '/views/partials/admin-footer.php';
