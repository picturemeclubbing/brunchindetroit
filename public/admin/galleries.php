<?php
declare(strict_types=1);

/**
 * Admin Gallery Management — list page (Phase 5A).
 *
 * GET  renders all galleries (published + drafts) with publish/feature toggles.
 * POST handles publish/unpublish/feature/unfeature actions (CSRF + PRG).
 *
 * No create/edit here — that lives in gallery-edit.php. No delete in 5A.
 */

require_once __DIR__ . '/../../app/bootstrap.php';
require_once APP_ROOT . '/models/Gallery.php';

admin_require_login();

$activeNav  = 'galleries';
$pageTitle  = page_title('Gallery Management');
$debug      = (bool) (app_config()['debug'] ?? false);

// --- POST: toggle actions -----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check first — fail closed.
    if (!csrf_verify((string) ($_POST['csrf_token'] ?? ''))) {
        flash_set('error', 'Security check failed. Please try again.');
        redirect(admin_url('galleries.php'));
    }

    $action = (string) ($_POST['action'] ?? '');
    $idRaw  = $_POST['id'] ?? '';

    // Validate id as a positive integer.
    $id = (is_numeric($idRaw) && (int) $idRaw > 0) ? (int) $idRaw : 0;
    if ($id <= 0) {
        flash_set('error', 'Invalid gallery.');
        redirect(admin_url('galleries.php'));
    }

    try {
        switch ($action) {
            case 'publish':
                Gallery::setPublished($id, true);
                flash_set('success', 'Gallery published.');
                break;

            case 'unpublish':
                Gallery::setPublished($id, false);
                flash_set('success', 'Gallery unpublished.');
                break;

            case 'feature':
                Gallery::setFeatured($id, true);
                flash_set('success', 'Gallery marked as featured.');
                break;

            case 'unfeature':
                Gallery::setFeatured($id, false);
                flash_set('success', 'Gallery removed from featured.');
                break;

            default:
                flash_set('error', 'Unknown action.');
                break;
        }
    } catch (Throwable $ex) {
        // Never leak raw errors in production.
        flash_set('error', $debug ? $ex->getMessage() : 'Could not update that gallery.');
    }

    // PRG — always redirect after a POST.
    redirect(admin_url('galleries.php'));
}

// --- GET: render list ---------------------------------------------------------
$flashSuccess = flash_get('success');
$flashError   = flash_get('error');

try {
    $galleries = Gallery::all();
} catch (Throwable $ex) {
    $galleries   = [];
    $flashError  = $debug ? $ex->getMessage() : 'Could not load galleries.';
}

require APP_ROOT . '/views/partials/admin-header.php';
require APP_ROOT . '/views/partials/admin-sidebar.php';
require APP_ROOT . '/views/admin/galleries.php';
require APP_ROOT . '/views/partials/admin-footer.php';