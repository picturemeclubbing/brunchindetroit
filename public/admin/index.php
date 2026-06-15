<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

if (admin_is_logged_in()) {
    redirect(admin_url('dashboard.php'));
}

redirect(admin_url('login.php'));
