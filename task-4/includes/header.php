<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? "SecureBlog";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta
        name="description"
        content="Secure PHP CRUD application with validation and role-based access control."
    >
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/app.js" defer></script>
</head>
<body>

<header class="site-header">
    <div class="container nav">
        <a class="brand" href="index.php">
            <span class="brand-mark">S</span>

            <span class="brand-copy">
                <strong>SecureBlog</strong>
                <small>Task 4</small>
            </span>
        </a>

        <nav class="nav-links">
            <?php if (is_logged_in()): ?>
                <a href="index.php">Posts</a>
                <a href="create.php">Create</a>

                <?php if (is_admin()): ?>
                    <a href="admin_users.php">Users</a>
                <?php endif; ?>

                <span
                    class="role-badge <?= is_admin() ? "role-admin" : "role-editor" ?>"
                >
                    <?= e(ucfirst(current_role())) ?>
                </span>

                <a class="btn btn-ghost btn-sm" href="logout.php">
                    Logout
                </a>
            <?php else: ?>
                <a href="login.php">Login</a>

                <a class="btn btn-primary btn-sm" href="register.php">
                    Register
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>
