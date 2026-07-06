<?php
/**
 * File: app/views/admin/rsvps.php
 * Purpose: Admin RSVP inbox view. Renders the list built by
 *          public/admin/rsvps.php with an inline status-update control per
 *          row. No search/filter/pagination in this batch.
 * Batch: B2 RSVP modal + public wiring.
 *
 * @var array<int, array<string, mixed>> $rsvps
 * @var string|null $flashSuccess
 * @var string|null $flashError
 */

$statusLabels = [
    'new'       => 'New',
    'contacted' => 'Contacted',
    'confirmed' => 'Confirmed',
    'cancelled' => 'Cancelled',
];
?>
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">RSVPs</h1>
        <p class="admin-page-lead">
            Recent reservation requests submitted from venue pages and the directory.
        </p>
    </div>
</div>

<?php if ($flashSuccess !== null): ?>
    <div class="alert alert--success" role="alert">
        <i class="fa-solid fa-circle-check"></i>
        <?= e($flashSuccess) ?>
    </div>
<?php endif; ?>

<?php if ($flashError !== null): ?>
    <div class="alert alert--danger" role="alert">
        <i class="fa-solid fa-circle-exclamation"></i>
        <?= e($flashError) ?>
    </div>
<?php endif; ?>

<section class="admin-panel admin-rsvp-panel">
    <?php if ($rsvps === []): ?>
        <div class="empty-state">
            <h3>No RSVPs yet</h3>
            <p>Submitted reservation requests will show up here.</p>
        </div>
    <?php else: ?>
        <div class="admin-table__wrap">
            <table class="admin-table admin-rsvp-table">
                <thead>
                    <tr>
                        <th scope="col">Venue</th>
                        <th scope="col">Guest</th>
                        <th scope="col">Contact</th>
                        <th scope="col">Party</th>
                        <th scope="col">Requested</th>
                        <th scope="col">Notes</th>
                        <th scope="col">Status</th>
                        <th scope="col">Received</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rsvps as $rsvp): ?>
                        <?php
                        $rsvpId = (int) ($rsvp['id'] ?? 0);
                        $venueName = (string) ($rsvp['venue_name'] ?? 'Unknown venue');
                        $venueSlug = (string) ($rsvp['venue_slug'] ?? '');
                        $status = (string) ($rsvp['status'] ?? 'new');

                        $requestedParts = [];
                        if (!empty($rsvp['requested_date'])) {
                            $requestedParts[] = date('M j, Y', strtotime((string) $rsvp['requested_date']));
                        }
                        if (!empty($rsvp['requested_time'])) {
                            $requestedParts[] = date('g:i A', strtotime((string) $rsvp['requested_time']));
                        }
                        $requestedDisplay = $requestedParts !== [] ? implode(' at ', $requestedParts) : 'Not specified';

                        $notes = trim((string) ($rsvp['notes'] ?? ''));
                        $createdAt = !empty($rsvp['created_at'])
                            ? date('M j, Y g:i A', strtotime((string) $rsvp['created_at']))
                            : '';
                        ?>
                        <tr>
                            <th scope="row" class="admin-table__title-cell">
                                <?php if ($venueSlug !== ''): ?>
                                    <a href="<?= e(asset_url('venue.php?slug=' . urlencode($venueSlug))) ?>" target="_blank" rel="noopener noreferrer">
                                        <?= e($venueName) ?>
                                    </a>
                                <?php else: ?>
                                    <?= e($venueName) ?>
                                <?php endif; ?>
                            </th>
                            <td><?= e((string) ($rsvp['name'] ?? '')) ?></td>
                            <td class="admin-rsvp-contact-cell">
                                <?php if (!empty($rsvp['phone'])): ?>
                                    <span><?= e((string) $rsvp['phone']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($rsvp['email'])): ?>
                                    <span><?= e((string) $rsvp['email']) ?></span>
                                <?php endif; ?>
                                <?php if (empty($rsvp['phone']) && empty($rsvp['email'])): ?>
                                    <span class="admin-table__muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $rsvp['party_size'] !== null ? (int) $rsvp['party_size'] : '&mdash;' ?></td>
                            <td><?= e($requestedDisplay) ?></td>
                            <td class="admin-rsvp-notes-cell">
                                <?php if ($notes !== ''): ?>
                                    <span class="admin-table__muted"><?= e($notes) ?></span>
                                <?php else: ?>
                                    <span class="admin-table__muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td class="admin-rsvp-status-cell">
                                <form method="post" action="<?= e(admin_url('rsvps.php')) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="rsvp_id" value="<?= $rsvpId ?>">
                                    <select
                                        name="status"
                                        class="admin-rsvp-status-select admin-rsvp-status-select--<?= e($status) ?>"
                                        onchange="this.closest('form').submit()"
                                        aria-label="Update status for <?= e((string) ($rsvp['name'] ?? 'this RSVP')) ?>"
                                    >
                                        <?php foreach ($statusLabels as $value => $label): ?>
                                            <option value="<?= e($value) ?>" <?= $value === $status ? 'selected' : '' ?>>
                                                <?= e($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td class="admin-table__muted"><?= e($createdAt) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
