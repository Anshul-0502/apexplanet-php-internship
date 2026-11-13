<?php

declare(strict_types=1);

require_once "includes/bootstrap.php";

if (is_logged_in()) {
    redirect("index.php");
}

$error = "";
$username = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();

    $username = trim((string) ($_POST["username"] ?? ""));
    $password = (string) ($_POST["password"] ?? "");

    if ($username === "" || $password === "") {
        $error = "Username and password are required.";
    } else {
        $findUser = $pdo->prepare(
            "SELECT
                id,
                username,
                password,
                role
             FROM users
             WHERE username = :username
             LIMIT 1"
        );

        $findUser->execute([
            "username" => $username,
        ]);

        $user = $findUser->fetch();

        if (
            $user
            && password_verify(
                $password,
                $user["password"]
            )
        ) {
            session_regenerate_id(true);

            $_SESSION["user_id"] = (int) $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];

            redirect("index.php");
        }

        $error = "Invalid username or password.";
    }
}

$pageTitle = "Login • SecureBlog";
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
<body class="auth-body">

<div class="auth-shell">
    <section class="auth-visual">
        <div class="auth-copy">
            <span class="eyebrow">
                SECURE AUTHENTICATION
            </span>

            <h1>Welcome back.</h1>

            <p>
                Sign in to access the secured dashboard with
                role-based permissions.
            </p>

            <div class="pill-row">
                <span>Prepared Queries</span>
                <span>Secure Sessions</span>
                <span>Password Hashing</span>
            </div>
        </div>
    </section>

    <section class="auth-panel">
        <div class="auth-card">
            <a class="brand auth-brand" href="login.php">
                <span class="brand-mark">S</span>

                <span class="brand-copy">
                    <strong>SecureBlog</strong>
                    <small>Task 4</small>
                </span>
            </a>

            <h2>Sign in</h2>

            <p class="muted">
                Enter your account credentials.
            </p>

            <?php if (isset($_GET["registered"])): ?>
                <div class="alert alert-success">
                    Registration successful. Please sign in.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form
                method="POST"
                class="form-stack js-validate"
                novalidate
            >
                <?= csrf_field() ?>

                <div class="field">
                    <label for="username">Username</label>

                    <input
                        class="input"
                        id="username"
                        name="username"
                        type="text"
                        value="<?= e($username) ?>"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="field">
                    <label for="password">Password</label>

                    <input
                        class="input"
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button
                    class="btn btn-primary btn-block"
                    type="submit"
                >
                    Login
                </button>
            </form>

            <p class="auth-switch">
                Need an account?
                <a href="register.php">Register</a>
            </p>
        </div>
    </section>
</div>

</body>
</html>
