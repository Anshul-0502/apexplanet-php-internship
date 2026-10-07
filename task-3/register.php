<?php
include "config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $plainPassword = $_POST["password"] ?? "";

    if (strlen($username) < 3) {
        $message = "Username must be at least 3 characters.";
    } elseif (strlen($plainPassword) < 6) {
        $message = "Password must be at least 6 characters.";
    } else {
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO users (username, password) VALUES (?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ss", $username, $hashedPassword);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: login.php?registered=1");
            exit();
        } else {
            $message = "Username already exists. Please choose another one.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account • BlogSpace Pro</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="auth-page">
    <section class="auth-visual">
        <div class="auth-copy">
            <div class="brand" style="margin-bottom:26px;">
                <span class="brand-badge">B</span>
                <span>BlogSpace Pro</span>
            </div>
            <h1>Write. Share. Manage.</h1>
            <p>
                A clean PHP & MySQL blogging workspace with secure authentication
                and complete CRUD functionality.
            </p>
        </div>
    </section>

    <section class="auth-panel">
        <div class="auth-card">
            <h2>Create account</h2>
            <p class="sub">Join BlogSpace Pro and start managing your posts.</p>

            <?php if ($message): ?>
                <div class="alert alert-error"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input class="input" id="username" type="text" name="username"
                           placeholder="Enter your username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input class="input" id="password" type="password" name="password"
                           placeholder="Minimum 6 characters" required>
                </div>

                <button class="btn btn-primary" type="submit">Create Account</button>
            </form>

            <p class="auth-switch">
                Already have an account?
                <a href="login.php">Sign in</a>
            </p>
        </div>
    </section>
</div>
</body>
</html>
