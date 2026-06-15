<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

admin_logout();
redirect(admin_url('login.php'));
