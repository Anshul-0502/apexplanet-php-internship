<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

$id = (int)($_POST["id"] ?? 0);

if ($id > 0) {
    $find = mysqli_prepare($conn, "SELECT image FROM posts WHERE id = ?");
    mysqli_stmt_bind_param($find, "i", $id);
    mysqli_stmt_execute($find);
    $findResult = mysqli_stmt_get_result($find);
    $post = mysqli_fetch_assoc($findResult);

    if ($post) {
        $stmt = mysqli_prepare($conn, "DELETE FROM posts WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        if (!empty($post["image"])) {
            $imagePath = __DIR__ . "/uploads/" . $post["image"];
            if (is_file($imagePath)) {
                unlink($imagePath);
            }
        }
    }
}

header("Location: index.php");
exit();
?>
