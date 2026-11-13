<?php
declare(strict_types=1);

require_once "includes/bootstrap.php";
require_login();

$search = trim((string) ($_GET["search"] ?? ""));
$category = trim((string) ($_GET["category"] ?? ""));
$page = max(1, (int) ($_GET["page"] ?? 1));

$perPage = 9;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($search !== "") {
    $where[] = "(p.title LIKE :search OR p.content LIKE :search OR p.category LIKE :search OR u.username LIKE :search)";
    $params["search"] = "%" . $search . "%";
}

if ($category !== "") {
    $where[] = "p.category = :category";
    $params["category"] = $category;
}

$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

$countStmt = $pdo->prepare(
    "SELECT COUNT(*)
     FROM posts p
     INNER JOIN users u ON u.id = p.user_id
     {$whereSql}"
);

foreach ($params as $key => $value) {
    $countStmt->bindValue(":" . $key, $value, PDO::PARAM_STR);
}

$countStmt->execute();

$totalPosts = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalPosts / $perPage));

$listStmt = $pdo->prepare(
    "SELECT
        p.id,
        p.user_id,
        p.title,
        p.content,
        p.category,
        p.image,
        p.created_at,
        u.username AS author
     FROM posts p
     INNER JOIN users u ON u.id = p.user_id
     {$whereSql}
     ORDER BY p.created_at DESC
     LIMIT :limit OFFSET :offset"
);

foreach ($params as $key => $value) {
    $listStmt->bindValue(":" . $key, $value, PDO::PARAM_STR);
}

$listStmt->bindValue(":limit", $perPage, PDO::PARAM_INT);
$listStmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$listStmt->execute();

$posts = $listStmt->fetchAll();

$categoryStmt = $pdo->prepare(
    "SELECT DISTINCT category
     FROM posts
     WHERE category <> ''
     ORDER BY category
     LIMIT 12"
);

$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);

function page_url(int $page, string $search, string $category): string
{
    $query = ["page" => $page];

    if ($search !== "") {
        $query["search"] = $search;
    }

    if ($category !== "") {
        $query["category"] = $category;
    }

    return "index.php?" . http_build_query($query);
}

$pageTitle = "Explore • PinBoard";
require "includes/header.php";
?>

<main class="feed-main">
    <section class="shell feed-hero">
        <div>
            <span class="eyebrow dark-eyebrow">FINAL PROJECT • TASK 5</span>
            <h1>Find something worth sharing.</h1>
            <p>
                Explore community posts, search ideas, filter categories
                and publish your own.
            </p>
        </div>

        <a class="btn btn-primary" href="create.php">+ Create a Post</a>
    </section>

    <section class="shell">
        <div class="chips">
            <a class="chip <?= $category === "" ? "active" : "" ?>" href="index.php">
                All
            </a>

            <?php foreach ($categories as $item): ?>
                <a
                    class="chip <?= $category === $item ? "active" : "" ?>"
                    href="index.php?<?= e(http_build_query(["category" => $item])) ?>"
                >
                    <?= e($item) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($search !== "" || $category !== ""): ?>
            <div class="result-strip">
                <span>
                    <strong><?= $totalPosts ?></strong> result<?= $totalPosts === 1 ? "" : "s" ?>
                </span>
                <a href="index.php">Clear filters</a>
            </div>
        <?php endif; ?>

        <?php if (!$posts): ?>
            <div class="empty-state">
                <div class="empty-orb">✦</div>
                <h2>No posts found</h2>
                <p>Try another search or create the first post.</p>
                <a class="btn btn-primary" href="create.php">Create Post</a>
            </div>
        <?php else: ?>
            <div class="masonry">
                <?php foreach ($posts as $post): ?>
                    <article class="pin-card">
                        <a
                            class="pin-visual"
                            href="view.php?id=<?= (int) $post["id"] ?>"
                        >
                            <?php if (!empty($post["image"])): ?>
                                <img
                                    src="uploads/<?= e($post["image"]) ?>"
                                    alt="<?= e($post["title"]) ?>"
                                    loading="lazy"
                                >
                            <?php else: ?>
                                <div class="pin-placeholder">
                                    <?= e($post["category"]) ?>
                                </div>
                            <?php endif; ?>

                            <span class="view-pill">View</span>
                        </a>

                        <div class="pin-body">
                            <div class="pin-kicker">
                                <span class="category-label">
                                    <?= e($post["category"]) ?>
                                </span>

                                <span>
                                    <?= e(date("d M", strtotime($post["created_at"]))) ?>
                                </span>
                            </div>

                            <a
                                class="pin-title"
                                href="view.php?id=<?= (int) $post["id"] ?>"
                            >
                                <?= e($post["title"]) ?>
                            </a>

                            <p>
                                <?php
                                $preview = strip_tags((string) $post["content"]);
                                echo e(strlen($preview) > 120
                                    ? substr($preview, 0, 120) . "..."
                                    : $preview);
                                ?>
                            </p>

                            <div class="pin-footer">
                                <a
                                    class="author"
                                    href="profile.php?user=<?= (int) $post["user_id"] ?>"
                                >
                                    <span class="tiny-avatar">
                                        <?= e(strtoupper(substr($post["author"], 0, 1))) ?>
                                    </span>

                                    <?= e($post["author"]) ?>
                                </a>

                                <?php if (can_manage_post($post)): ?>
                                    <a href="edit.php?id=<?= (int) $post["id"] ?>">Edit</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($totalPages > 1): ?>
            <nav class="pagination">
                <?php if ($page > 1): ?>
                    <a
                        class="page-link"
                        href="<?= e(page_url($page - 1, $search, $category)) ?>"
                    >
                        ← Previous
                    </a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a
                        class="page-link <?= $i === $page ? "active" : "" ?>"
                        href="<?= e(page_url($i, $search, $category)) ?>"
                    >
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a
                        class="page-link"
                        href="<?= e(page_url($page + 1, $search, $category)) ?>"
                    >
                        Next →
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </section>
</main>

<?php require "includes/footer.php"; ?>
