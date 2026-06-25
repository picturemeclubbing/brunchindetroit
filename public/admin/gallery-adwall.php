<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';
require_once APP_ROOT . '/models/SiteSetting.php';

admin_require_login();

$activeNav = 'gallery-adwall';
$pageTitle = 'Gallery Ad Wall Settings';
$debug = (bool) (app_config()['debug'] ?? false);

$settingKeys = [
    'gallery_adwall_sponsor_label',
    'gallery_adwall_sponsor_name',
    'gallery_adwall_sponsor_headline',
    'gallery_adwall_sponsor_body',
    'gallery_adwall_background_image_url',
    'gallery_adwall_overlay_opacity',
    'gallery_adwall_logo_url',
    'gallery_adwall_logo_alt',
    'gallery_adwall_cta_label',
    'gallery_adwall_cta_url',
    'gallery_adwall_continue_label',
    'gallery_adwall_provider_note',
    'gallery_adwall_footer_background_image_url',
    'gallery_adwall_footer_overlay_color',
    'gallery_adwall_footer_overlay_opacity',
    'gallery_adwall_footer_position_x',
    'gallery_adwall_footer_position_y',
];

$defaults = [
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
function admin_gallery_adwall_is_upload_path(string $value): bool
{
    return preg_match('#^/uploads/gallery-adwall/[a-zA-Z0-9._-]+\.(jpe?g|png|webp)$#i', $value) === 1;
}

function admin_gallery_adwall_is_http_url(string $value): bool
{
    if (filter_var($value, FILTER_VALIDATE_URL) === false) {
        return false;
    }

    $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

    return in_array($scheme, ['http', 'https'], true);
}

function admin_gallery_adwall_handle_upload(string $fieldName, string $prefix, array &$errors): ?string
{
    if (empty($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        return null;
    }

    $file = $_FILES[$fieldName];
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($error !== UPLOAD_ERR_OK) {
        $errors[$fieldName] = 'Image upload failed. Please try again.';
        return null;
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    $size = (int) ($file['size'] ?? 0);

    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        $errors[$fieldName] = 'Uploaded image could not be verified.';
        return null;
    }

    $maxBytes = PHP_INT_MAX;

    if ($size <= 0 || $size > $maxBytes) {
        $errors[$fieldName] = 'Image upload exceeded the server upload limit.';
        return null;
    }

    $imageInfo = @getimagesize($tmpName);
    if ($imageInfo === false || empty($imageInfo['mime'])) {
        $errors[$fieldName] = 'Upload a valid image file.';
        return null;
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $mime = (string) $imageInfo['mime'];
    if (!isset($allowed[$mime])) {
        $errors[$fieldName] = 'Image must be JPG, PNG, or WEBP.';
        return null;
    }

    $publicRoot = realpath(__DIR__ . '/..');
    if ($publicRoot === false) {
        $errors[$fieldName] = 'Upload folder could not be resolved.';
        return null;
    }

    $uploadDir = $publicRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'gallery-adwall';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
        $errors[$fieldName] = 'Upload folder could not be created.';
        return null;
    }

    if (!is_writable($uploadDir)) {
        $errors[$fieldName] = 'Upload folder is not writable.';
        return null;
    }

    $extension = $allowed[$mime];
    $safePrefix = preg_replace('/[^a-z0-9-]+/i', '-', strtolower($prefix)) ?: 'adwall';
    $filename = $safePrefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
    $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmpName, $destination)) {
        $errors[$fieldName] = 'Uploaded image could not be saved.';
        return null;
    }

    return '/uploads/gallery-adwall/' . $filename;
}


function admin_gallery_adwall_delete_upload_file(string $value): void
{
    if (!admin_gallery_adwall_is_upload_path($value)) {
        return;
    }

    $publicRoot = realpath(__DIR__ . '/..');
    if ($publicRoot === false) {
        return;
    }

    $relative = ltrim($value, '/');
    $path = realpath($publicRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative));

    if ($path === false) {
        return;
    }

    $uploadRoot = realpath($publicRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'gallery-adwall');
    if ($uploadRoot === false) {
        return;
    }

    if (str_starts_with($path, $uploadRoot) && is_file($path)) {
        @unlink($path);
    }
}

function admin_gallery_adwall_json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

$errors = [];

try {
    $stored = SiteSetting::getMany($settingKeys);
} catch (Throwable $ex) {
    $stored = [];
    flash_set('error', $debug ? $ex->getMessage() : 'Could not load ad wall settings.');
}

$form = array_merge($defaults, $stored);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Instant clear image action.
    if ((string) ($_POST['action'] ?? '') === 'clear_image') {
        $settingKey = (string) ($_POST['setting_key'] ?? '');

        $clearableImageFields = [
            'gallery_adwall_background_image_url',
            'gallery_adwall_logo_url',
            'gallery_adwall_footer_background_image_url',
        ];

        if (!in_array($settingKey, $clearableImageFields, true)) {
            admin_gallery_adwall_json_response([
                'ok' => false,
                'message' => 'Invalid image field.',
            ], 400);
        }

        try {
            $current = SiteSetting::getMany([$settingKey]);
            $currentValue = (string) ($current[$settingKey] ?? '');

            admin_gallery_adwall_delete_upload_file($currentValue);

            SiteSetting::upsertMany([
                $settingKey => '',
            ]);

            admin_gallery_adwall_json_response([
                'ok' => true,
                'setting_key' => $settingKey,
                'message' => 'Image removed.',
            ]);
        } catch (Throwable $ex) {
            admin_gallery_adwall_json_response([
                'ok' => false,
                'message' => $debug ? $ex->getMessage() : 'Could not remove image.',
            ], 500);
        }
    }
    if (!csrf_verify((string) ($_POST['csrf_token'] ?? ''))) {
        flash_set('error', 'Security check failed. Please try again.');
        redirect(admin_url('gallery-adwall.php'));
    }

    foreach ($settingKeys as $key) {
        $form[$key] = trim((string) ($_POST[$key] ?? ($defaults[$key] ?? '')));
    }


    $clearImageFields = [
        'clear_gallery_adwall_background_image_url' => 'gallery_adwall_background_image_url',
        'clear_gallery_adwall_logo_url' => 'gallery_adwall_logo_url',
        'clear_gallery_adwall_footer_background_image_url' => 'gallery_adwall_footer_background_image_url',
    ];

    foreach ($clearImageFields as $clearField => $settingKey) {
        if (!empty($_POST[$clearField])) {
            $form[$settingKey] = '';
        }
    }
    $uploads = [
        'gallery_adwall_background_image_upload' => ['gallery_adwall_background_image_url', 'sponsor-bg'],
        'gallery_adwall_logo_upload' => ['gallery_adwall_logo_url', 'sponsor-logo'],
        'gallery_adwall_footer_background_image_upload' => ['gallery_adwall_footer_background_image_url', 'footer-bg'],
    ];

    foreach ($uploads as $uploadField => [$settingKey, $prefix]) {
        $uploadedPath = admin_gallery_adwall_handle_upload($uploadField, $prefix, $errors);
        if ($uploadedPath !== null) {
            $form[$settingKey] = $uploadedPath;
        }
    }

    $maxLengths = [
        'gallery_adwall_sponsor_label' => 80,
        'gallery_adwall_sponsor_name' => 120,
        'gallery_adwall_sponsor_headline' => 180,
        'gallery_adwall_sponsor_body' => 700,
        'gallery_adwall_background_image_url' => 500,
        'gallery_adwall_logo_url' => 500,
        'gallery_adwall_logo_alt' => 160,
        'gallery_adwall_cta_label' => 80,
        'gallery_adwall_cta_url' => 500,
        'gallery_adwall_continue_label' => 80,
        'gallery_adwall_provider_note' => 300,
        'gallery_adwall_footer_background_image_url' => 500,
        'gallery_adwall_footer_overlay_color' => 7,
        'gallery_adwall_footer_position_x' => 10,
        'gallery_adwall_footer_position_y' => 10,
    ];

    foreach ($maxLengths as $key => $max) {
        if (mb_strlen($form[$key]) > $max) {
            $errors[$key] = 'Must be ' . $max . ' characters or fewer.';
        }
    }

    $required = [
        'gallery_adwall_sponsor_label' => 'Sponsor label is required.',
        'gallery_adwall_sponsor_name' => 'Sponsor name is required.',
        'gallery_adwall_sponsor_headline' => 'Sponsor headline is required.',
        'gallery_adwall_sponsor_body' => 'Sponsor body text is required.',
        'gallery_adwall_continue_label' => 'Continue button label is required.',
        'gallery_adwall_provider_note' => 'Provider note is required.',
    ];

    foreach ($required as $key => $message) {
        if ($form[$key] === '') {
            $errors[$key] = $message;
        }
    }

    $imageUrlFields = [
        'gallery_adwall_background_image_url' => 'Background image URL',
        'gallery_adwall_logo_url' => 'Logo/image URL',
        'gallery_adwall_footer_background_image_url' => 'Footer background image URL',
    ];

    foreach ($imageUrlFields as $key => $label) {
        if ($form[$key] === '') {
            continue;
        }

        if (!admin_gallery_adwall_is_http_url($form[$key]) && !admin_gallery_adwall_is_upload_path($form[$key])) {
            $errors[$key] = $label . ' must be a valid http(s) URL or a saved gallery ad-wall upload path.';
        }
    }

    if ($form['gallery_adwall_cta_url'] !== '' && !admin_gallery_adwall_is_http_url($form['gallery_adwall_cta_url'])) {
        $errors['gallery_adwall_cta_url'] = 'CTA URL must be a valid http(s) URL.';
    }

    if (($form['gallery_adwall_cta_label'] === '') !== ($form['gallery_adwall_cta_url'] === '')) {
        $errors['gallery_adwall_cta_label'] = 'CTA label and CTA URL must both be filled out, or both left blank.';
        $errors['gallery_adwall_cta_url'] = 'CTA label and CTA URL must both be filled out, or both left blank.';
    }

    $overlayOpacity = $form['gallery_adwall_overlay_opacity'];
    if ($overlayOpacity === '') {
        $form['gallery_adwall_overlay_opacity'] = '0.88';
    } elseif (!is_numeric($overlayOpacity)) {
        $errors['gallery_adwall_overlay_opacity'] = 'Overlay strength must be a number between 0 and 1.';
    } else {
        $overlayFloat = (float) $overlayOpacity;
        if ($overlayFloat < 0 || $overlayFloat > 1) {
            $errors['gallery_adwall_overlay_opacity'] = 'Overlay strength must be between 0 and 1.';
        } else {
            $form['gallery_adwall_overlay_opacity'] = rtrim(rtrim(number_format($overlayFloat, 2, '.', ''), '0'), '.');
        }
    }

    $footerOverlayColor = $form['gallery_adwall_footer_overlay_color'] !== ''
        ? $form['gallery_adwall_footer_overlay_color']
        : '#111827';

    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $footerOverlayColor)) {
        $errors['gallery_adwall_footer_overlay_color'] = 'Footer overlay color must be a valid hex color.';
    } else {
        $form['gallery_adwall_footer_overlay_color'] = strtoupper($footerOverlayColor);
    }

    $footerOverlayOpacity = $form['gallery_adwall_footer_overlay_opacity'];
    if ($footerOverlayOpacity === '') {
        $form['gallery_adwall_footer_overlay_opacity'] = '0.82';
    } elseif (!is_numeric($footerOverlayOpacity)) {
        $errors['gallery_adwall_footer_overlay_opacity'] = 'Footer overlay strength must be a number between 0 and 1.';
    } else {
        $footerOverlayFloat = (float) $footerOverlayOpacity;
        if ($footerOverlayFloat < 0 || $footerOverlayFloat > 1) {
            $errors['gallery_adwall_footer_overlay_opacity'] = 'Footer overlay strength must be between 0 and 1.';
        } else {
            $form['gallery_adwall_footer_overlay_opacity'] = rtrim(rtrim(number_format($footerOverlayFloat, 2, '.', ''), '0'), '.');
        }
    }

    if (!in_array($form['gallery_adwall_footer_position_x'], ['left', 'center', 'right'], true)) {
        $form['gallery_adwall_footer_position_x'] = 'center';
    }

    if (!in_array($form['gallery_adwall_footer_position_y'], ['top', 'center', 'bottom'], true)) {
        $form['gallery_adwall_footer_position_y'] = 'center';
    }

    if ($errors === []) {
        try {
            SiteSetting::upsertMany($form);
            flash_set('success', 'Gallery ad wall settings saved.');
            redirect(admin_url('gallery-adwall.php'));
        } catch (Throwable $ex) {
            $errors['form'] = $debug ? $ex->getMessage() : 'Could not save ad wall settings.';
        }
    }
}

$flashSuccess = flash_get('success');
$flashError = flash_get('error');

require APP_ROOT . '/views/partials/admin-header.php';
require APP_ROOT . '/views/partials/admin-sidebar.php';
require APP_ROOT . '/views/admin/gallery-adwall.php';
require APP_ROOT . '/views/partials/admin-footer.php';
