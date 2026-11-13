<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "config/db.php";

$result = mysqli_query(
    $conn,
    "SELECT * FROM posts ORDER BY created_at DESC"
);

$postCountResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM posts");
$postCount = (int)(mysqli_fetch_assoc($postCountResult)["total"] ?? 0);

$pageTitle = "Dashboard • BlogSpace";
include "includes/header.php";
?>

<main>
    <section class="hero">
        <div class="container">
            <div class="hero-card">
                <div>
                    <p class="small" style="color:#c7d2fe;margin-bottom:10px;">YOUR CONTENT DASHBOARD</p>
                    <h1>Hello, <?= htmlspecialchars($_SESSION["username"]) ?> 👋</h1>
                    <p>
                        Manage your blog posts from one place. Create new ideas,
                        update existing content and keep everything organized.
                    </p>
                </div>

                <div class="hero-stat">
                    <div class="small" style="color:#ddd6fe;">Total Posts</div>
                    <div class="big"><?= $postCount ?></div>
                    <div style="color:#e0e7ff;margin-top:6px;">Published in your blog database</div>
                </div>
            </div>
        </div>
    </section>

    <section class="container">
        <div class="section-head">
            <div>
                <h2>Latest Posts</h2>
                <p>View and manage all your published posts.</p>
            </div>
            <a class="btn btn-primary" href="create.php">+ Create New Post</a>
        </div>

        <div class="grid">
            <?php if (mysqli_num_rows($result) === 0): ?>
                <div class="empty-state">
                    <h3 style="margin-bottom:8px;">No posts yet</h3>
                    <p>Create your first post to see it here.</p>
                    <a class="btn btn-primary" href="create.php" style="margin-top:18px;">
                        Create First Post
                    </a>
                </div>
            <?php else: ?>
                <?php while ($post = mysqli_fetch_assoc($result)): ?>
                    <article class="post-card">
                        <div class="post-meta">
                            <span>Post #<?= (int)$post["id"] ?></span>
                            <span><?= htmlspecialchars($post["created_at"]) ?></span>
                        </div>

                        <h3><?= htmlspecialchars($post["title"]) ?></h3>

                        <p><?= nl2br(htmlspecialchars($post["content"])) ?></p>

                        <div class="post-actions">
                            <a class="btn btn-light"
                               href="edit.php?id=<?= (int)$post["id"] ?>">
                                Edit
                            </a>

                            <form action="delete.php" method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this post?');">
                                <input type="hidden" name="id" value="<?= (int)$post["id"] ?>">
                                <button class="btn btn-outline-danger" type="submit">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include "includes/footer.php"; ?>
