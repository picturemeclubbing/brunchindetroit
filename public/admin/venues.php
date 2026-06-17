<?php
declare(strict_types=1);

/**
 * Admin Venue Management — list page (Phase 5B).
 *
 * GET  renders all venues (published + drafts) with publish/feature toggles.
 * POST handles publish/unpublish/feature/unfeature actions (CSRF + PRG).
 *
 * No create/edit here — that lives in venue-edit.php. No delete in 5B.
 */

require_once __DIR__ . '/../../app/bootstrap.php';
require_once APP_ROOT . '/models/Venue.php';

admin_require_login();

$activeNav  = 'venues';
$pageTitle  = page_title('Venue Management');
$debug      = (bool) (app_config()['debug'] ?? false);

// --- POST: toggle actions -----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check first — fail closed.
    if (!csrf_verify((string) ($_POST['csrf_token'] ?? ''))) {
        flash_set('error', 'Security check failed. Please try again.');
        redirect(admin_url('venues.php'));
    }

    $action = (string) ($_POST['action'] ?? '');
    $idRaw  = $_POST['id'] ?? '';

    // Validate id as a positive integer.
    $id = (is_numeric($idRaw) && (int) $idRaw > 0) ? (int) $idRaw : 0;
    if ($id <= 0) {
        flash_set('error', 'Invalid venue.');
        redirect(admin_url('venues.php'));
    }

    try {
        switch ($action) {
            case 'publish':
                Venue::setPublished($id, true);
                flash_set('success', 'Venue published.');
                break;

            case 'unpublish':
                Venue::setPublished($id, false);
                flash_set('success', 'Venue unpublished.');
                break;

            case 'feature':
                Venue::setFeatured($id, true);
                flash_set('success', 'Venue marked as featured.');
                break;

            case 'unfeature':
                Venue::setFeatured($id, false);
                flash_set('success', 'Venue removed from featured.');
                break;

            default:
                flash_set('error', 'Unknown action.');
                break;
        }
    } catch (Throwable $ex) {
        // Never leak raw errors in production.
        flash_set('error', $debug ? $ex->getMessage() : 'Could not update that venue.');
    }

    // PRG — always redirect after a POST.
    redirect(admin_url('venues.php'));
}

// --- GET: render list ---------------------------------------------------------
$flashSuccess = flash_get('success');
$flashError   = flash_get('error');

try {
    $venues = Venue::all();
} catch (Throwable $ex) {
    $venues      = [];
    $flashError  = $debug ? $ex->getMessage() : 'Could not load venues.';
}

require APP_ROOT . '/views/partials/admin-header.php';
require APP_ROOT . '/views/partials/admin-sidebar.php';
require APP_ROOT . '/views/admin/venues.php';
require APP_ROOT . '/views/partials/admin-footer.php';