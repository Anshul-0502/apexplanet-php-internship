<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'BlogSpace Pro' ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <div class="container navbar">
        <a class="brand" href="index.php">
            <span class="brand-badge">B</span>
            <span>BlogSpace Pro</span>
        </a>

        <div class="nav-actions">
            <?php if (isset($_SESSION["user_id"])): ?>
                <span class="small">
                    Signed in as <strong><?= htmlspecialchars($_SESSION["username"]) ?></strong>
                </span>
                <a class="btn btn-light" href="create.php">+ New Post</a>
                <a class="btn btn-outline-danger" href="logout.php">Logout</a>
            <?php else: ?>
                <a class="btn btn-light" href="login.php">Login</a>
                <a class="btn btn-primary" href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
</header>
