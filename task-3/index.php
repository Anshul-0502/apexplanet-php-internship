<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "config/db.php";

$search = trim($_GET["search"] ?? "");
$page = max(1, (int)($_GET["page"] ?? 1));
$postsPerPage = 6;
$offset = ($page - 1) * $postsPerPage;

if ($search !== "") {
    $like = "%" . $search . "%";

    $countStmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total FROM posts WHERE title LIKE ? OR content LIKE ?"
    );
    mysqli_stmt_bind_param($countStmt, "ss", $like, $like);
    mysqli_stmt_execute($countStmt);
    $countResult = mysqli_stmt_get_result($countStmt);
    $totalPosts = (int)(mysqli_fetch_assoc($countResult)["total"] ?? 0);

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM posts
         WHERE title LIKE ? OR content LIKE ?
         ORDER BY created_at DESC
         LIMIT ? OFFSET ?"
    );
    mysqli_stmt_bind_param($stmt, "ssii", $like, $like, $postsPerPage, $offset);
} else {
    $countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM posts");
    $totalPosts = (int)(mysqli_fetch_assoc($countResult)["total"] ?? 0);

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM posts ORDER BY created_at DESC LIMIT ? OFFSET ?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $postsPerPage, $offset);
}

$totalPages = max(1, (int)ceil($totalPosts / $postsPerPage));

if ($page > $totalPages && $totalPosts > 0) {
    $params = ["page" => $totalPages];
    if ($search !== "") {
        $params["search"] = $search;
    }
    header("Location: index.php?" . http_build_query($params));
    exit();
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

function pageUrl($pageNumber, $searchTerm) {
    $params = ["page" => $pageNumber];
    if ($searchTerm !== "") {
        $params["search"] = $searchTerm;
    }
    return "index.php?" . http_build_query($params);
}

$pageTitle = "Dashboard • BlogSpace Pro";
include "includes/header.php";
?>

<main>
    <section class="hero">
        <div class="container">
            <div class="hero-card">
                <div>
                    <p class="small" style="color:#c7d2fe;margin-bottom:10px;">TASK 3 • ADVANCED BLOG DASHBOARD</p>
                    <h1>Hello, <?= htmlspecialchars($_SESSION["username"]) ?> 👋</h1>
                    <p>
                        Search by title or content, browse posts with pagination,
                        and manage your complete PHP & MySQL blog from one place.
                    </p>
                </div>

                <div class="hero-stat hero-stats-grid">
                    <div>
                        <div class="small" style="color:#ddd6fe;">Matching Posts</div>
                        <div class="big"><?= $totalPosts ?></div>
                    </div>
                    <div>
                        <div class="small" style="color:#ddd6fe;">Page</div>
                        <div class="big"><?= $page ?>/<?= $totalPages ?></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container">
        <div class="task3-toolbar">
            <form method="GET" action="index.php" class="search-form">
                <div class="search-box">
                    <span class="search-symbol">⌕</span>
                    <input
                        class="input search-input"
                        type="search"
                        name="search"
                        value="<?= htmlspecialchars($search) ?>"
                        placeholder="Search posts by title or content..."
                    >
                </div>
                <button class="btn btn-primary" type="submit">Search</button>
                <?php if ($search !== ""): ?>
                    <a class="btn btn-light" href="index.php">Clear</a>
                <?php endif; ?>
            </form>

            <a class="btn btn-primary" href="create.php">+ Create New Post</a>
        </div>

        <?php if ($search !== ""): ?>
            <div class="search-summary">
                Showing <strong><?= $totalPosts ?></strong> result(s) for
                <strong>“<?= htmlspecialchars($search) ?>”</strong>
            </div>
        <?php endif; ?>

        <div class="grid task3-grid">
            <?php if (mysqli_num_rows($result) === 0): ?>
                <div class="empty-state">
                    <div class="empty-icon">✦</div>
                    <h3 style="margin-bottom:8px;">
                        <?= $search !== "" ? "No matching posts found" : "No posts yet" ?>
                    </h3>
                    <p>
                        <?= $search !== ""
                            ? "Try another keyword or clear the search."
                            : "Create your first post to see it here." ?>
                    </p>
                    <?php if ($search !== ""): ?>
                        <a class="btn btn-light" href="index.php" style="margin-top:18px;">Clear Search</a>
                    <?php else: ?>
                        <a class="btn btn-primary" href="create.php" style="margin-top:18px;">Create First Post</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php while ($post = mysqli_fetch_assoc($result)): ?>
                    <article class="post-card task3-post-card">
                        <?php if (!empty($post["image"])): ?>
                            <img
                                src="uploads/<?= htmlspecialchars($post["image"]) ?>"
                                alt="<?= htmlspecialchars($post["title"]) ?>"
                                class="post-image"
                            >
                        <?php else: ?>
                            <div class="post-image post-image-placeholder">BlogSpace Pro</div>
                        <?php endif; ?>

                        <div class="post-card-content">
                            <div class="post-meta">
                                <span>Post #<?= (int)$post["id"] ?></span>
                                <span><?= date("d M Y", strtotime($post["created_at"])) ?></span>
                            </div>

                            <h3><?= htmlspecialchars($post["title"]) ?></h3>

                            <p>
                                <?php
                                $clean = strip_tags($post["content"]);
                                echo htmlspecialchars(
                                    mb_strlen($clean) > 180
                                        ? mb_substr($clean, 0, 180) . "..."
                                        : $clean
                                );
                                ?>
                            </p>

                            <div class="post-actions">
                                <a class="btn btn-light" href="edit.php?id=<?= (int)$post["id"] ?>">Edit</a>

                                <form action="delete.php" method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this post?');">
                                    <input type="hidden" name="id" value="<?= (int)$post["id"] ?>">
                                    <button class="btn btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="pagination" aria-label="Post pagination">
                <?php if ($page > 1): ?>
                    <a class="page-link" href="<?= htmlspecialchars(pageUrl($page - 1, $search)) ?>">← Previous</a>
                <?php else: ?>
                    <span class="page-link disabled">← Previous</span>
                <?php endif; ?>

                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                ?>

                <?php if ($startPage > 1): ?>
                    <a class="page-link" href="<?= htmlspecialchars(pageUrl(1, $search)) ?>">1</a>
                    <?php if ($startPage > 2): ?>
                        <span class="page-dots">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a
                        class="page-link <?= $i === $page ? "active" : "" ?>"
                        href="<?= htmlspecialchars(pageUrl($i, $search)) ?>"
                    >
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span class="page-dots">...</span>
                    <?php endif; ?>
                    <a class="page-link" href="<?= htmlspecialchars(pageUrl($totalPages, $search)) ?>">
                        <?= $totalPages ?>
                    </a>
                <?php endif; ?>

                <?php if ($page < $totalPages): ?>
                    <a class="page-link" href="<?= htmlspecialchars(pageUrl($page + 1, $search)) ?>">Next →</a>
                <?php else: ?>
                    <span class="page-link disabled">Next →</span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </section>
</main>

<?php include "includes/footer.php"; ?>
