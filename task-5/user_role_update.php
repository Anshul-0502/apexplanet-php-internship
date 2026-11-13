<?php
declare(strict_types=1);

require_once "includes/bootstrap.php";
require_admin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("admin_users.php");
}

verify_csrf();

$userId = (int) ($_POST["user_id"] ?? 0);
$newRole = (string) ($_POST["role"] ?? "");

if (!in_array($newRole, ["admin", "editor"], true)) {
    redirect("admin_users.php?error=" . urlencode("Invalid role selected."));
}

$stmt = $pdo->prepare(
    "SELECT id, role
     FROM users
     WHERE id = :id
     LIMIT 1"
);

$stmt->execute(["id" => $userId]);
$user = $stmt->fetch();

if (!$user) {
    redirect("admin_users.php?error=" . urlencode("User not found."));
}

if ($user["role"] === "admin" && $newRole === "editor") {
    $countAdmins = $pdo->prepare(
        "SELECT COUNT(*)
         FROM users
         WHERE role = 'admin'"
    );

    $countAdmins->execute();

    if ((int) $countAdmins->fetchColumn() <= 1) {
        redirect(
            "admin_users.php?error="
            . urlencode("The last administrator cannot be demoted.")
        );
    }
}

$update = $pdo->prepare(
    "UPDATE users
     SET role = :role
     WHERE id = :id"
);

$update->execute([
    "role" => $newRole,
    "id" => $userId,
]);

if ($userId === current_user_id()) {
    $_SESSION["role"] = $newRole;
}

redirect("admin_users.php?updated=1");
