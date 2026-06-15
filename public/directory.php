<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_once APP_ROOT . '/models/Venue.php';

$pageTitle = 'Detroit Brunch Directory';
$venues = Venue::published();

require APP_ROOT . '/views/directory.php';