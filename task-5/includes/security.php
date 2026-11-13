<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

function redirect(string $location): never
{
    header("Location: " . $location);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $submitted = (string) ($_POST["csrf_token"] ?? "");
    $stored = (string) ($_SESSION["csrf_token"] ?? "");

    if ($submitted === "" || $stored === "" || !hash_equals($stored, $submitted)) {
        http_response_code(403);
        exit("Invalid security token.");
    }
}

function is_logged_in(): bool
{
    return isset($_SESSION["user_id"]);
}

function current_user_id(): int
{
    return (int) ($_SESSION["user_id"] ?? 0);
}

function current_username(): string
{
    return (string) ($_SESSION["username"] ?? "");
}

function current_role(): string
{
    return (string) ($_SESSION["role"] ?? "editor");
}

function is_admin(): bool
{
    return current_role() === "admin";
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect("login.php");
    }
}

function require_admin(): void
{
    require_login();

    if (!is_admin()) {
        http_response_code(403);
        exit("Administrator permission required.");
    }
}

function can_manage_post(array $post): bool
{
    return is_admin() || (int) $post["user_id"] === current_user_id();
}

function validate_username(string $username): array
{
    $errors = [];

    if ($username === "") {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3 || strlen($username) > 30) {
        $errors[] = "Username must be between 3 and 30 characters.";
    } elseif (!preg_match('/^[A-Za-z0-9_]+$/', $username)) {
        $errors[] = "Username can contain only letters, numbers and underscores.";
    }

    return $errors;
}

function validate_password(string $password): array
{
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password needs an uppercase letter.";
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password needs a lowercase letter.";
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password needs a number.";
    }

    return $errors;
}

function validate_post(string $title, string $content, string $category): array
{
    $errors = [];

    if ($title === "" || strlen($title) < 3 || strlen($title) > 150) {
        $errors[] = "Title must be between 3 and 150 characters.";
    }

    if ($content === "" || strlen($content) < 10 || strlen($content) > 10000) {
        $errors[] = "Content must be between 10 and 10,000 characters.";
    }

    if ($category === "" || strlen($category) > 50) {
        $errors[] = "Category is required and must be 50 characters or fewer.";
    }

    return $errors;
}

function validate_image(array $file): ?string
{
    if (($file["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file["error"] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return "Image upload failed.";
    }

    if (($file["size"] ?? 0) > 5 * 1024 * 1024) {
        return "Image must be 5 MB or smaller.";
    }

    $allowed = ["image/jpeg", "image/png", "image/webp"];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file["tmp_name"]);

    return in_array($mime, $allowed, true)
        ? null
        : "Only JPG, PNG and WEBP images are allowed.";
}

function store_image(array $file): ?string
{
    if (($file["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $map = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp",
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file["tmp_name"]);
    $extension = $map[$mime] ?? null;

    if ($extension === null) {
        return null;
    }

    $filename = bin2hex(random_bytes(16)) . "." . $extension;
    $destination = dirname(__DIR__) . "/uploads/" . $filename;

    return move_uploaded_file($file["tmp_name"], $destination)
        ? $filename
        : null;
}

function delete_image(?string $filename): void
{
    if (!$filename) {
        return;
    }

    $path = dirname(__DIR__) . "/uploads/" . basename($filename);

    if (is_file($path)) {
        unlink($path);
    }
}
