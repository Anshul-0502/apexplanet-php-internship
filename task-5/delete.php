<?php
declare(strict_types=1);

require_once "includes/bootstrap.php";
require_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("index.php");
}

verify_csrf();

$postId = (int) ($_POST["id"] ?? 0);

$findPost = $pdo->prepare(
    "SELECT id, user_id, image
     FROM posts
     WHERE id = :id
     LIMIT 1"
);

$findPost->execute(["id" => $postId]);
$post = $findPost->fetch();

if (!$post) {
    redirect("index.php");
}

if (!can_manage_post($post)) {
    http_response_code(403);
    exit("You do not have permission to delete this post.");
}

$delete = $pdo->prepare(
    "DELETE FROM posts WHERE id = :id"
);

$delete->execute(["id" => $postId]);

delete_image($post["image"] ?? null);

redirect("index.php");
