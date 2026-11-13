<?php

declare(strict_types=1);

require_once "includes/bootstrap.php";

if (is_logged_in()) {
    redirect("index.php");
}

$errors = [];
$username = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();

    $username = trim((string) ($_POST["username"] ?? ""));
    $password = (string) ($_POST["password"] ?? "");
    $confirmPassword = (string) ($_POST["confirm_password"] ?? "");

    $errors = array_merge(
        $errors,
        validate_username($username),
        validate_password($password)
    );

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (!$errors) {
        $checkUser = $pdo->prepare(
            "SELECT id
             FROM users
             WHERE username = :username
             LIMIT 1"
        );

        $checkUser->execute([
            "username" => $username,
        ]);

        if ($checkUser->fetch()) {
            $errors[] = "That username is already registered.";
        } else {
            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $insertUser = $pdo->prepare(
                "INSERT INTO users (
                    username,
                    password,
                    role
                ) VALUES (
                    :username,
                    :password,
                    'editor'
                )"
            );

            $insertUser->execute([
                "username" => $username,
                "password" => $passwordHash,
            ]);

            redirect("login.php?registered=1");
        }
    }
}

$pageTitle = "Register • SecureBlog";
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
            <span class="eyebrow">APEX PLANET • TASK 4</span>

            <h1>Security-first PHP development.</h1>

            <p>
                Prepared statements, strong validation, protected sessions,
                CSRF protection, and role-based access control.
            </p>

            <div class="pill-row">
                <span>PDO</span>
                <span>RBAC</span>
                <span>Validation</span>
                <span>CSRF</span>
            </div>
        </div>
    </section>

    <section class="auth-panel">
        <div class="auth-card">
            <a class="brand auth-brand" href="register.php">
                <span class="brand-mark">S</span>

                <span class="brand-copy">
                    <strong>SecureBlog</strong>
                    <small>Task 4</small>
                </span>
            </a>

            <h2>Create account</h2>

            <p class="muted">
                New accounts are created with the Editor role.
            </p>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
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
                        minlength="3"
                        maxlength="30"
                        pattern="[A-Za-z0-9_]+"
                        autocomplete="username"
                        required
                    >

                    <small>
                        Letters, numbers, and underscores only.
                    </small>
                </div>

                <div class="field">
                    <label for="password">Password</label>

                    <input
                        class="input"
                        id="password"
                        name="password"
                        type="password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                    <small>
                        Minimum 8 characters with uppercase, lowercase,
                        and a number.
                    </small>
                </div>

                <div class="field">
                    <label for="confirm_password">
                        Confirm password
                    </label>

                    <input
                        class="input"
                        id="confirm_password"
                        name="confirm_password"
                        type="password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button
                    class="btn btn-primary btn-block"
                    type="submit"
                >
                    Create Account
                </button>
            </form>

            <p class="auth-switch">
                Already registered?
                <a href="login.php">Sign in</a>
            </p>
        </div>
    </section>
</div>

</body>
</html>
