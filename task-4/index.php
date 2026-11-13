<?php

declare(strict_types=1);

require_once "includes/bootstrap.php";

require_login();

$search = trim((string) ($_GET["search"] ?? ""));
$page = max(1, (int) ($_GET["page"] ?? 1));

$postsPerPage = 6;
$offset = ($page - 1) * $postsPerPage;

$whereParts = [];
$queryParams = [];

if (!is_admin()) {
    $whereParts[] = "p.user_id = :user_id";
    $queryParams["user_id"] = current_user_id();
}

if ($search !== "") {
    $whereParts[] = "
        (
            p.title LIKE :search
            OR p.content LIKE :search
        )
    ";

    $queryParams["search"] = "%" . $search . "%";
}

$whereSql = $whereParts
    ? "WHERE " . implode(" AND ", $whereParts)
    : "";

$countSql = "
    SELECT COUNT(*)
    FROM posts p
    {$whereSql}
";

$countPosts = $pdo->prepare($countSql);

foreach ($queryParams as $key => $value) {
    $type = $key === "user_id"
        ? PDO::PARAM_INT
        : PDO::PARAM_STR;

    $countPosts->bindValue(
        ":" . $key,
        $value,
        $type
    );
}

$countPosts->execute();

$totalPosts = (int) $countPosts->fetchColumn();
$totalPages = max(
    1,
    (int) ceil($totalPosts / $postsPerPage)
);

if ($page > $totalPages && $totalPosts > 0) {
    $query = [
        "page" => $totalPages,
    ];

    if ($search !== "") {
        $query["search"] = $search;
    }

    redirect(
        "index.php?"
        . http_build_query($query)
    );
}

$listSql = "
    SELECT
        p.id,
        p.user_id,
        p.title,
        p.content,
        p.image,
        p.created_at,
        u.username AS author
    FROM posts p
    INNER JOIN users u
        ON u.id = p.user_id
    {$whereSql}
    ORDER BY p.created_at DESC
    LIMIT :limit
    OFFSET :offset
";

$listPosts = $pdo->prepare($listSql);

foreach ($queryParams as $key => $value) {
    $type = $key === "user_id"
        ? PDO::PARAM_INT
        : PDO::PARAM_STR;

    $listPosts->bindValue(
        ":" . $key,
        $value,
        $type
    );
}

$listPosts->bindValue(
    ":limit",
    $postsPerPage,
    PDO::PARAM_INT
);

$listPosts->bindValue(
    ":offset",
    $offset,
    PDO::PARAM_INT
);

$listPosts->execute();

$posts = $listPosts->fetchAll();

function page_url(
    int $targetPage,
    string $search
): string {
    $query = [
        "page" => $targetPage,
    ];

    if ($search !== "") {
        $query["search"] = $search;
    }

    return "index.php?"
        . http_build_query($query);
}

$pageTitle = "Dashboard • SecureBlog";

require "includes/header.php";
?>

<main>
    <section class="hero">
        <div class="container hero-grid">
            <div>
                <span class="eyebrow eyebrow-dark">
                    SECURE CONTENT DASHBOARD
                </span>

                <h1>
                    Hello, <?= e(current_username()) ?>.
                </h1>

                <p>
                    <?php if (is_admin()): ?>
                        As an administrator, you can manage all
                        posts and user roles.
                    <?php else: ?>
                        As an editor, you can create and manage
                        only your own posts.
                    <?php endif; ?>
                </p>
            </div>

            <div class="stats">
                <div>
                    <span>Role</span>
                    <strong>
                        <?= e(ucfirst(current_role())) ?>
                    </strong>
                </div>

                <div>
                    <span>Results</span>
                    <strong><?= $totalPosts ?></strong>
                </div>

                <div>
                    <span>Page</span>
                    <strong>
                        <?= $page ?>/<?= $totalPages ?>
                    </strong>
                </div>
            </div>
        </div>
    </section>

    <section class="container content-section">
        <div class="toolbar">
            <form
                class="search-form"
                method="GET"
                action="index.php"
            >
                <input
                    class="input"
                    type="search"
                    name="search"
                    value="<?= e($search) ?>"
                    maxlength="100"
                    placeholder="Search by title or content..."
                >

                <button
                    class="btn btn-primary"
                    type="submit"
                >
                    Search
                </button>

                <?php if ($search !== ""): ?>
                    <a
                        class="btn btn-ghost"
                        href="index.php"
                    >
                        Clear
                    </a>
                <?php endif; ?>
            </form>

            <a
                class="btn btn-primary"
                href="create.php"
            >
                + Create Post
            </a>
        </div>

        <?php if ($search !== ""): ?>
            <div class="info-box">
                Search results for
                <strong>
                    "<?= e($search) ?>"
                </strong>
                — <?= $totalPosts ?> found.
            </div>
        <?php endif; ?>

        <div class="post-grid">
            <?php if (!$posts): ?>
                <div class="empty-state">
                    <div class="empty-icon">✓</div>

                    <h3>
                        <?= $search
                            ? "No matching posts"
                            : "No posts yet" ?>
                    </h3>

                    <p>
                        <?= $search
                            ? "Try another search term."
                            : "Create your first secure post." ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php foreach ($posts as $post): ?>
                <article class="post-card">
                    <?php if (!empty($post["image"])): ?>
                        <img
                            class="post-image"
                            src="uploads/<?= e($post["image"]) ?>"
                            alt="<?= e($post["title"]) ?>"
                        >
                    <?php else: ?>
                        <div
                            class="post-image post-placeholder"
                        >
                            SecureBlog
                        </div>
                    <?php endif; ?>

                    <div class="post-body">
                        <div class="post-meta">
                            <span>
                                <?= e($post["author"]) ?>
                            </span>

                            <span>
                                <?= e(
                                    date(
                                        "d M Y",
                                        strtotime(
                                            $post["created_at"]
                                        )
                                    )
                                ) ?>
                            </span>
                        </div>

                        <h3>
                            <?= e($post["title"]) ?>
                        </h3>

                        <p>
                            <?php
                            $preview = strip_tags(
                                (string) $post["content"]
                            );

                            if (strlen($preview) > 180) {
                                $preview = substr(
                                    $preview,
                                    0,
                                    180
                                ) . "...";
                            }

                            echo e($preview);
                            ?>
                        </p>

                        <?php if (can_manage_post($post)): ?>
                            <div class="post-actions">
                                <a
                                    class="btn btn-ghost btn-sm"
                                    href="edit.php?id=<?= (int) $post["id"] ?>"
                                >
                                    Edit
                                </a>

                                <form
                                    method="POST"
                                    action="delete.php"
                                    onsubmit="return confirm(
                                        'Delete this post permanently?'
                                    );"
                                >
                                    <?= csrf_field() ?>

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int) $post["id"] ?>"
                                    >

                                    <button
                                        class="btn btn-danger-soft btn-sm"
                                        type="submit"
                                    >
                                        Delete
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="pagination">
                <?php if ($page > 1): ?>
                    <a
                        class="page-link"
                        href="<?= e(
                            page_url(
                                $page - 1,
                                $search
                            )
                        ) ?>"
                    >
                        ← Previous
                    </a>
                <?php endif; ?>

                <?php
                $startPage = max(
                    1,
                    $page - 2
                );

                $endPage = min(
                    $totalPages,
                    $page + 2
                );
                ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a
                        class="page-link <?= $i === $page ? "active" : "" ?>"
                        href="<?= e(
                            page_url(
                                $i,
                                $search
                            )
                        ) ?>"
                    >
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a
                        class="page-link"
                        href="<?= e(
                            page_url(
                                $page + 1,
                                $search
                            )
                        ) ?>"
                    >
                        Next →
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </section>
</main>

<?php require "includes/footer.php"; ?>
