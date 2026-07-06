<?php
declare(strict_types=1);

/**
 * File: public/admin/rsvps.php
 * Purpose: Admin RSVP inbox - lists recent venue_rsvps rows (all venues) and
 *          allows updating a row's status (new/contacted/confirmed/cancelled).
 *          No search/filter in this batch; kept intentionally simple.
 * Batch: B2 RSVP modal + public wiring.
 */

require_once __DIR__ . '/../../app/bootstrap.php';
require_once APP_ROOT . '/models/Rsvp.php';

admin_require_login();

$activeNav = 'rsvps';
$pageTitle = page_title('RSVPs');
$debug = (bool) (app_config()['debug'] ?? false);

$flashSuccess = flash_get('success');
$flashError = flash_get('error');

// --- POST: status update only (no create/delete in this batch) -------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify((string) ($_POST['csrf_token'] ?? ''))) {
        flash_set('error', 'Security check failed. Please try again.');
        redirect(admin_url('rsvps.php'));
    }

    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'update_status') {
        $rsvpId = isset($_POST['rsvp_id']) && is_numeric($_POST['rsvp_id'])
            ? (int) $_POST['rsvp_id']
            : 0;
        $status = (string) ($_POST['status'] ?? '');

        if ($rsvpId <= 0) {
            flash_set('error', 'Invalid RSVP.');
            redirect(admin_url('rsvps.php'));
        }

        if (!in_array($status, Rsvp::STATUSES, true)) {
            flash_set('error', 'Invalid status.');
            redirect(admin_url('rsvps.php'));
        }

        try {
            Rsvp::updateStatus($rsvpId, $status);
            flash_set('success', 'RSVP status updated.');
        } catch (Throwable $ex) {
            flash_set('error', $debug ? $ex->getMessage() : 'Could not update that RSVP.');
        }

        redirect(admin_url('rsvps.php'));
    }

    flash_set('error', 'Unknown RSVP action.');
    redirect(admin_url('rsvps.php'));
}

// --- GET: list ---------------------------------------------------------------
try {
    $rsvps = Rsvp::recent(100);
} catch (Throwable $ex) {
    $rsvps = [];
    $flashError = $debug ? $ex->getMessage() : 'Could not load RSVPs.';
}

require APP_ROOT . '/views/partials/admin-header.php';
require APP_ROOT . '/views/partials/admin-sidebar.php';
require APP_ROOT . '/views/admin/rsvps.php';
require APP_ROOT . '/views/partials/admin-footer.php';
