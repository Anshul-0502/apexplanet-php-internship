<?php

declare(strict_types=1);

require_once "includes/bootstrap.php";

require_admin();

$search = trim(
    (string) ($_GET["search"] ?? "")
);

if ($search !== "") {
    $findUsers = $pdo->prepare(
        "SELECT
            id,
            username,
            role,
            created_at
         FROM users
         WHERE username LIKE :search
         ORDER BY created_at DESC"
    );

    $findUsers->execute([
        "search" => "%" . $search . "%",
    ]);
} else {
    $findUsers = $pdo->prepare(
        "SELECT
            id,
            username,
            role,
            created_at
         FROM users
         ORDER BY created_at DESC"
    );

    $findUsers->execute();
}

$users = $findUsers->fetchAll();

$pageTitle = "User Management • SecureBlog";

require "includes/header.php";
?>

<main class="page-main">
    <section class="container">
        <div class="form-card wide-card">
            <div class="form-head">
                <div>
                    <span class="eyebrow eyebrow-dark">
                        ADMINISTRATION
                    </span>

                    <h1>
                        User roles & permissions
                    </h1>

                    <p>
                        Only administrators can access
                        this page.
                    </p>
                </div>

                <a
                    class="btn btn-ghost"
                    href="index.php"
                >
                    ← Dashboard
                </a>
            </div>

            <?php if (isset($_GET["updated"])): ?>
                <div class="alert alert-success">
                    User role updated successfully.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET["error"])): ?>
                <div class="alert alert-danger">
                    <?= e((string) $_GET["error"]) ?>
                </div>
            <?php endif; ?>

            <form
                class="search-form admin-search"
                method="GET"
            >
                <input
                    class="input"
                    type="search"
                    name="search"
                    value="<?= e($search) ?>"
                    maxlength="30"
                    placeholder="Search username..."
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
                        href="admin_users.php"
                    >
                        Clear
                    </a>
                <?php endif; ?>
            </form>

            <div class="table-wrap">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Current Role</th>
                            <th>Joined</th>
                            <th>Change Role</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    #<?= (int) $user["id"] ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= e($user["username"]) ?>
                                    </strong>

                                    <?php if (
                                        (int) $user["id"]
                                        === current_user_id()
                                    ): ?>
                                        <small class="you-label">
                                            You
                                        </small>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span
                                        class="role-badge <?= $user["role"] === "admin"
                                            ? "role-admin"
                                            : "role-editor" ?>"
                                    >
                                        <?= e(
                                            ucfirst(
                                                $user["role"]
                                            )
                                        ) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= e(
                                        date(
                                            "d M Y",
                                            strtotime(
                                                $user["created_at"]
                                            )
                                        )
                                    ) ?>
                                </td>

                                <td>
                                    <form
                                        method="POST"
                                        action="user_role_update.php"
                                        class="role-form"
                                    >
                                        <?= csrf_field() ?>

                                        <input
                                            type="hidden"
                                            name="user_id"
                                            value="<?= (int) $user["id"] ?>"
                                        >

                                        <select
                                            class="input compact-input"
                                            name="role"
                                            required
                                        >
                                            <option
                                                value="editor"
                                                <?= $user["role"] === "editor"
                                                    ? "selected"
                                                    : "" ?>
                                            >
                                                Editor
                                            </option>

                                            <option
                                                value="admin"
                                                <?= $user["role"] === "admin"
                                                    ? "selected"
                                                    : "" ?>
                                            >
                                                Admin
                                            </option>
                                        </select>

                                        <button
                                            class="btn btn-primary btn-sm"
                                            type="submit"
                                        >
                                            Update
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<?php require "includes/footer.php"; ?>
