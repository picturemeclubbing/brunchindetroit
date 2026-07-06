<?php
declare(strict_types=1);

/**
 * Public RSVP submission endpoint (Batch B1: backend foundation only).
 *
 * POST-only, returns JSON. Not called by any UI yet — the modal/button
 * wiring is planned for Batch B2. Accepts either venue_slug or venue_id so
 * the future front-end can use whichever is more convenient to pass.
 */

require_once __DIR__ . '/../app/bootstrap.php';
require_once APP_ROOT . '/models/Venue.php';
require_once APP_ROOT . '/models/Rsvp.php';

header('Content-Type: application/json');

/**
 * Small helper to send a JSON response and stop execution.
 *
 * @param array<string, mixed> $payload
 */
$respond = static function (int $httpStatus, array $payload): void {
    http_response_code($httpStatus);
    echo json_encode($payload);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    $respond(405, [
        'success' => false,
        'message' => 'This endpoint only accepts POST requests.',
    ]);
}

// Honeypot: a real visitor never fills this field in (it's hidden from the
// live form via CSS in whatever markup posts here). If it's filled, quietly
// pretend to succeed without touching the database, so spam bots get no
// signal that they were caught.
$honeypot = trim((string) ($_POST['website'] ?? ''));
if ($honeypot !== '') {
    $respond(200, [
        'success' => true,
        'message' => 'Thanks! Your request has been received.',
    ]);
}

// --- Resolve the venue (published only) -----------------------------------

$venueSlug = trim((string) ($_POST['venue_slug'] ?? ''));
$venueIdInput = trim((string) ($_POST['venue_id'] ?? ''));

$venue = null;

if ($venueSlug !== '') {
    $venue = Venue::findBySlug($venueSlug);
} elseif ($venueIdInput !== '' && ctype_digit($venueIdInput)) {
    $candidate = Venue::find((int) $venueIdInput);
    if ($candidate !== null && !empty($candidate['is_published'])) {
        $venue = $candidate;
    }
}

if ($venue === null) {
    $respond(400, [
        'success' => false,
        'message' => 'We could not find that venue.',
    ]);
}

// --- Validate the submitted fields -----------------------------------------

$errors = [];

$name = trim((string) ($_POST['name'] ?? ''));
if ($name === '') {
    $errors['name'] = 'Name is required.';
} elseif (mb_strlen($name) > 150) {
    $errors['name'] = 'Name is too long.';
}

$phone = trim((string) ($_POST['phone'] ?? ''));
if ($phone !== '') {
    $phone = preg_replace('/[^0-9+().\-\s]/', '', $phone) ?? '';
    if ($phone === '' || mb_strlen($phone) > 30) {
        $errors['phone'] = 'Please enter a valid phone number.';
    }
}

$email = trim((string) ($_POST['email'] ?? ''));
if ($email !== '') {
    if (mb_strlen($email) > 190 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Please enter a valid email address.';
    }
}

if ($phone === '' && $email === '') {
    $errors['contact'] = 'Please provide a phone number or email address.';
}

$partySize = null;
$partySizeInput = trim((string) ($_POST['party_size'] ?? ''));
if ($partySizeInput === '') {
    $errors['party_size'] = 'Party size is required.';
} elseif (!ctype_digit($partySizeInput) || (int) $partySizeInput < 1 || (int) $partySizeInput > 50) {
    $errors['party_size'] = 'Party size should be a number between 1 and 50.';
} else {
    $partySize = (int) $partySizeInput;
}

$requestedDate = null;
$requestedDateInput = trim((string) ($_POST['requested_date'] ?? ''));
if ($requestedDateInput === '') {
    $errors['requested_date'] = 'Requested date is required.';
} else {
    $parsedDate = DateTime::createFromFormat('Y-m-d', $requestedDateInput);
    if ($parsedDate === false || $parsedDate->format('Y-m-d') !== $requestedDateInput) {
        $errors['requested_date'] = 'Please enter a valid date (YYYY-MM-DD).';
    } else {
        $requestedDate = $requestedDateInput;
    }
}

$requestedTime = null;
$requestedTimeInput = trim((string) ($_POST['requested_time'] ?? ''));
if ($requestedTimeInput === '') {
    $errors['requested_time'] = 'Requested time is required.';
} else {
    if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $requestedTimeInput) !== 1) {
        $errors['requested_time'] = 'Please enter a valid time (HH:MM).';
    } else {
        $requestedTime = $requestedTimeInput . ':00';
    }
}

// --- Brunch-hours availability check ---------------------------------------
//
// venue_hours (hour_type = 'brunch') is the structured, per-day-of-week
// source of truth for when a venue serves brunch. It exists in the schema
// but nothing in the admin currently writes to it, so most/all venues have
// zero rows today. We treat that as "not yet configured" and do NOT block
// the request in that case — blocking every RSVP because of missing admin
// data would be worse than not enforcing yet. Once a venue has real rows
// here (via a future admin screen), this check activates automatically for
// that venue with no further code changes needed.
if ($requestedDate !== null && $requestedTime !== null && $errors === []) {
    $brunchHours = Venue::brunchHoursForVenue((int) $venue['id']);

    if ($brunchHours !== []) {
        $requestedDayOfWeek = (int) (new DateTime($requestedDate))->format('w'); // 0=Sun..6=Sat
        $dayRows = array_values(array_filter($brunchHours, static function (array $row) use ($requestedDayOfWeek): bool {
            return (int) $row['day_of_week'] === $requestedDayOfWeek;
        }));

        if ($dayRows === []) {
            // Venue has structured hours for other days but none for this
            // one — treat as closed that day.
            $errors['requested_date'] = 'This venue does not offer brunch on that day.';
        } else {
            $isOpenAtRequestedTime = false;
            $allClosedThatDay = true;

            foreach ($dayRows as $dayRow) {
                if (!empty($dayRow['is_closed'])) {
                    continue;
                }
                $allClosedThatDay = false;

                $openTime = (string) ($dayRow['open_time'] ?? '');
                $closeTime = (string) ($dayRow['close_time'] ?? '');
                if ($openTime === '' || $closeTime === '') {
                    continue;
                }

                if ($requestedTime >= $openTime && $requestedTime <= $closeTime) {
                    $isOpenAtRequestedTime = true;
                    break;
                }
            }

            if ($allClosedThatDay) {
                $errors['requested_date'] = 'This venue does not offer brunch on that day.';
            } elseif (!$isOpenAtRequestedTime) {
                $errors['requested_time'] = 'Please choose a time within this venue\'s brunch hours for that day.';
            }
        }
    }
    // If $brunchHours === [], no structured data exists yet for this venue —
    // intentionally not enforced. See comment above.
}

$notes = trim((string) ($_POST['notes'] ?? ''));
if ($notes !== '' && mb_strlen($notes) > 1000) {
    $errors['notes'] = 'Notes are too long (1000 characters max).';
}

$sourceContext = trim((string) ($_POST['source_context'] ?? ''));
if ($sourceContext !== '' && mb_strlen($sourceContext) > 100) {
    $sourceContext = mb_substr($sourceContext, 0, 100);
}

if ($errors !== []) {
    $respond(422, [
        'success' => false,
        'message' => 'Please fix the highlighted fields.',
        'errors'  => $errors,
    ]);
}

// --- Persist -----------------------------------------------------------

$ipAddress = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
if ($ipAddress !== '') {
    $ipAddress = mb_substr($ipAddress, 0, 45);
}

$userAgent = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
if ($userAgent !== '') {
    $userAgent = mb_substr($userAgent, 0, 255);
}

$rsvpId = null;

try {
    $rsvpId = Rsvp::create([
        'venue_id'        => (int) $venue['id'],
        'name'            => $name,
        'phone'           => $phone !== '' ? $phone : null,
        'email'           => $email !== '' ? $email : null,
        'party_size'      => $partySize,
        'requested_date'  => $requestedDate,
        'requested_time'  => $requestedTime,
        'notes'           => $notes !== '' ? $notes : null,
        'source_context'  => $sourceContext !== '' ? $sourceContext : null,
        'ip_address'      => $ipAddress !== '' ? $ipAddress : null,
        'user_agent'      => $userAgent !== '' ? $userAgent : null,
    ]);
} catch (Throwable $exception) {
    $respond(500, [
        'success' => false,
        'message' => 'Something went wrong saving your request. Please try again.',
    ]);
}

$respond(201, [
    'success' => true,
    'id'      => $rsvpId,
    'message' => 'Thanks! Your request has been received.',
]);
