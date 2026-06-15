<?php

declare(strict_types=1);

/** @var string|null $error */
/** @var string $returnUrl */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(page_title('Admin Login')) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/main.css')) ?>">
</head>
<body class="admin-body admin-body--login">
    <div class="admin-login">
        <div class="admin-login__card">
            <p class="admin-login__brand"><?= e(site_name_display()) ?></p>
            <h1 class="admin-login__title">Admin sign in</h1>
            <p class="admin-login__hint">Private area for <?= e(site_domain()) ?> content managers.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert--danger" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= e(admin_url('login.php')) ?>" class="admin-login__form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="return" value="<?= e($returnUrl) ?>">

                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" required autocomplete="username" value="<?= e($email ?? '') ?>">

                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">

                <button type="submit" class="btn btn--primary btn--block">Sign in</button>
            </form>

            <p class="admin-login__footer">
                <a href="<?= e(asset_url('index.php')) ?>">&larr; Back to public site</a>
            </p>
        </div>
    </div>
</body>
</html>
