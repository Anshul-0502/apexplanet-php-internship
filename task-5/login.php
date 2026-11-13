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
        $stmt = $pdo->prepare(
            "SELECT id, username, password, role
             FROM users
             WHERE username = :username
             LIMIT 1"
        );

        $stmt->execute(["username" => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password"])) {
            session_regenerate_id(true);

            $_SESSION["user_id"] = (int) $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];

            redirect("index.php");
        }

        $error = "Invalid username or password.";
    }
}

$pageTitle = "Login • PinBoard";
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
            <span class="eyebrow">WELCOME BACK</span>
            <h1>Your next idea starts here.</h1>
            <p>
                Explore the visual feed, manage your posts and continue
                building your final internship project.
            </p>

            <div class="mini-pins">
                <span class="mini-pin medium"></span>
                <span class="mini-pin tall alt"></span>
                <span class="mini-pin short"></span>
                <span class="mini-pin medium"></span>
            </div>
        </div>
    </section>

    <section class="auth-panel">
        <div class="auth-card">
            <a class="brand auth-brand" href="login.php">
                <span class="brand-dot">P</span>
                <strong>PinBoard</strong>
            </a>

            <h2>Welcome back</h2>
            <p class="muted">Sign in to continue to your feed.</p>

            <?php if (isset($_GET["registered"])): ?>
                <div class="alert alert-success">
                    Registration successful. Please sign in.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
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
                        required
                    >
                </div>

                <button class="btn btn-primary btn-block" type="submit">
                    Sign In
                </button>
            </form>

            <p class="auth-switch">
                New here? <a href="register.php">Create account</a>
            </p>
        </div>
    </section>
</main>

</body>
</html>
