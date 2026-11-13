<?php
declare(strict_types=1);

require_once "includes/bootstrap.php";
require_login();

$postId = (int) ($_GET["id"] ?? 0);

$stmt = $pdo->prepare(
    "SELECT
        p.id,
        p.user_id,
        p.title,
        p.content,
        p.category,
        p.image,
        p.created_at,
        p.updated_at,
        u.username AS author
     FROM posts p
     INNER JOIN users u ON u.id = p.user_id
     WHERE p.id = :id
     LIMIT 1"
);

$stmt->execute(["id" => $postId]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    exit("Post not found.");
}

$pageTitle = $post["title"] . " • PinBoard";
require "includes/header.php";
?>

<main class="detail-main">
    <section class="shell">
        <a class="back-link" href="index.php">← Back to Explore</a>

        <article class="detail-card">
            <div class="detail-media">
                <?php if (!empty($post["image"])): ?>
                    <img
                        src="uploads/<?= e($post["image"]) ?>"
                        alt="<?= e($post["title"]) ?>"
                    >
                <?php else: ?>
                    <div class="detail-placeholder">
                        <?= e($post["category"]) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="detail-copy">
                <span class="category-label">
                    <?= e($post["category"]) ?>
                </span>

                <h1><?= e($post["title"]) ?></h1>

                <div class="detail-author-row">
                    <a
                        class="author"
                        href="profile.php?user=<?= (int) $post["user_id"] ?>"
                    >
                        <span class="tiny-avatar">
                            <?= e(strtoupper(substr($post["author"], 0, 1))) ?>
                        </span>

                        <?= e($post["author"]) ?>
                    </a>

                    <span class="muted">
                        <?= e(date("d M Y", strtotime($post["created_at"]))) ?>
                    </span>
                </div>

                <div class="detail-content">
                    <?= nl2br(e($post["content"])) ?>
                </div>

                <?php if (can_manage_post($post)): ?>
                    <div class="detail-actions">
                        <a
                            class="btn btn-secondary"
                            href="edit.php?id=<?= (int) $post["id"] ?>"
                        >
                            Edit Post
                        </a>

                        <form
                            method="POST"
                            action="delete.php"
                            onsubmit="return confirm('Delete this post permanently?');"
                        >
                            <?= csrf_field() ?>

                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int) $post["id"] ?>"
                            >

                            <button class="btn btn-danger" type="submit">
                                Delete Post
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    </section>
</main>

<?php require "includes/footer.php"; ?>
