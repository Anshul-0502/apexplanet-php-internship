<?php
declare(strict_types=1);

require_once "includes/bootstrap.php";
require_login();

$profileUserId = isset($_GET["user"])
    ? max(1, (int) $_GET["user"])
    : current_user_id();

$userStmt = $pdo->prepare(
    "SELECT id, username, role, created_at
     FROM users
     WHERE id = :id
     LIMIT 1"
);

$userStmt->execute(["id" => $profileUserId]);
$profileUser = $userStmt->fetch();

if (!$profileUser) {
    http_response_code(404);
    exit("User not found.");
}

$countStmt = $pdo->prepare(
    "SELECT COUNT(*)
     FROM posts
     WHERE user_id = :user_id"
);

$countStmt->execute(["user_id" => $profileUserId]);
$postCount = (int) $countStmt->fetchColumn();

$postStmt = $pdo->prepare(
    "SELECT id, user_id, title, category, image, created_at
     FROM posts
     WHERE user_id = :user_id
     ORDER BY created_at DESC
     LIMIT 12"
);

$postStmt->execute(["user_id" => $profileUserId]);
$profilePosts = $postStmt->fetchAll();

$pageTitle = $profileUser["username"] . " • PinBoard";
require "includes/header.php";
?>

<main class="profile-main">
    <section class="shell">
        <div class="profile-hero">
            <div class="profile-avatar">
                <?= e(strtoupper(substr($profileUser["username"], 0, 1))) ?>
            </div>

            <h1><?= e($profileUser["username"]) ?></h1>

            <p>
                <?= e(ucfirst($profileUser["role"])) ?>
                • Joined
                <?= e(date("M Y", strtotime($profileUser["created_at"]))) ?>
            </p>

            <strong>
                <?= $postCount ?> post<?= $postCount === 1 ? "" : "s" ?>
            </strong>

            <?php if ($profileUserId === current_user_id()): ?>
                <div class="profile-actions">
                    <a class="btn btn-primary" href="create.php">
                        Create Post
                    </a>

                    <a class="btn btn-secondary" href="logout.php">
                        Logout
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="profile-grid">
            <?php if (!$profilePosts): ?>
                <div class="empty-state">
                    <div class="empty-orb">✦</div>
                    <h2>No posts yet</h2>
                    <p>This profile has not published any posts.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($profilePosts as $post): ?>
                <a
                    class="profile-tile"
                    href="view.php?id=<?= (int) $post["id"] ?>"
                >
                    <?php if (!empty($post["image"])): ?>
                        <img
                            src="uploads/<?= e($post["image"]) ?>"
                            alt="<?= e($post["title"]) ?>"
                        >
                    <?php else: ?>
                        <div class="profile-tile-placeholder">
                            <?= e($post["category"]) ?>
                        </div>
                    <?php endif; ?>

                    <span><?= e($post["title"]) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php require "includes/footer.php"; ?>
