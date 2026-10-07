<?php
session_start();
include "config/db.php";

$error = "";
$registered = isset($_GET["registered"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, username, password FROM users WHERE username = ?"
    );
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user["password"])) {
        session_regenerate_id(true);
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];

        header("Location: index.php");
        exit();
    }

    $error = "Invalid username or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login • BlogSpace Pro</title>
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
            <h1>Welcome back.</h1>
            <p>
                Sign in to create, edit, read and manage your blog posts from one
                professional dashboard.
            </p>
        </div>
    </section>

    <section class="auth-panel">
        <div class="auth-card">
            <h2>Sign in</h2>
            <p class="sub">Enter your credentials to continue to your dashboard.</p>

            <?php if ($registered): ?>
                <div class="alert alert-success">
                    Registration successful. You can now sign in.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
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
                           placeholder="Enter your password" required>
                </div>

                <button class="btn btn-primary" type="submit">Login to Dashboard</button>
            </form>

            <p class="auth-switch">
                New here?
                <a href="register.php">Create an account</a>
            </p>
        </div>
    </section>
</div>
</body>
</html>
