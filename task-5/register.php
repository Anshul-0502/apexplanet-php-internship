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
        validate_username($username),
        validate_password($password)
    );

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (!$errors) {
        $check = $pdo->prepare(
            "SELECT id FROM users WHERE username = :username LIMIT 1"
        );

        $check->execute(["username" => $username]);

        if ($check->fetch()) {
            $errors[] = "That username is already registered.";
        } else {
            $insert = $pdo->prepare(
                "INSERT INTO users (username, password, role)
                 VALUES (:username, :password, 'editor')"
            );

            $insert->execute([
                "username" => $username,
                "password" => password_hash($password, PASSWORD_DEFAULT),
            ]);

            redirect("login.php?registered=1");
        }
    }
}

$pageTitle = "Create Account • PinBoard";
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

<main class="auth-layout">
    <section class="auth-showcase">
        <div>
            <span class="eyebrow">APEX PLANET • FINAL PROJECT</span>
            <h1>Discover ideas. Share your own.</h1>
            <p>
                A complete PHP and MySQL social-style platform with
                authentication, CRUD, search, pagination and security.
            </p>

            <div class="mini-pins">
                <span class="mini-pin tall"></span>
                <span class="mini-pin short"></span>
                <span class="mini-pin medium"></span>
                <span class="mini-pin short alt"></span>
            </div>
        </div>
    </section>

    <section class="auth-panel">
        <div class="auth-card">
            <a class="brand auth-brand" href="register.php">
                <span class="brand-dot">P</span>
                <strong>PinBoard</strong>
            </a>

            <h2>Create your account</h2>
            <p class="muted">Start posting, searching and exploring ideas.</p>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-stack js-validate" novalidate>
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
                        minlength="8"
                        required
                    >
                    <small>8+ characters with uppercase, lowercase and a number.</small>
                </div>

                <div class="field">
                    <label for="confirm_password">Confirm Password</label>
                    <input
                        class="input"
                        id="confirm_password"
                        name="confirm_password"
                        type="password"
                        minlength="8"
                        required
                    >
                </div>

                <button class="btn btn-primary btn-block" type="submit">
                    Create Account
                </button>
            </form>

            <p class="auth-switch">
                Already have an account?
                <a href="login.php">Sign in</a>
            </p>
        </div>
    </section>
</main>

</body>
</html>
