<?php
declare(strict_types=1);
$pageTitle = $pageTitle ?? "PinBoard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/app.js" defer></script>
</head>
<body>

<header class="site-header">
    <div class="shell topbar">
        <a class="brand" href="index.php">
            <span class="brand-dot">P</span>
            <strong>PinBoard</strong>
        </a>

        <?php if (is_logged_in()): ?>
            <form class="top-search" method="GET" action="index.php">
                <span>⌕</span>
                <input
                    type="search"
                    name="search"
                    value="<?= e((string) ($_GET["search"] ?? "")) ?>"
                    placeholder="Search ideas, titles or categories..."
                    maxlength="100"
                >
            </form>

            <nav class="top-actions">
                <a href="index.php">Explore</a>
                <a href="profile.php">Profile</a>

                <?php if (is_admin()): ?>
                    <a href="admin_users.php">Admin</a>
                <?php endif; ?>

                <a class="create-circle" href="create.php">+</a>
                <a class="avatar" href="profile.php">
                    <?= e(strtoupper(substr(current_username(), 0, 1))) ?>
                </a>
            </nav>
        <?php else: ?>
            <nav class="top-actions">
                <a href="login.php">Login</a>
                <a class="btn btn-primary" href="register.php">Join</a>
            </nav>
        <?php endif; ?>
    </div>
</header>
