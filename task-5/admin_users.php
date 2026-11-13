<?php
declare(strict_types=1);

require_once "includes/bootstrap.php";
require_admin();

$search = trim((string) ($_GET["search"] ?? ""));

if ($search !== "") {
    $stmt = $pdo->prepare(
        "SELECT id, username, role, created_at
         FROM users
         WHERE username LIKE :search
         ORDER BY created_at DESC"
    );

    $stmt->execute(["search" => "%" . $search . "%"]);
} else {
    $stmt = $pdo->prepare(
        "SELECT id, username, role, created_at
         FROM users
         ORDER BY created_at DESC"
    );

    $stmt->execute();
}

$users = $stmt->fetchAll();

$pageTitle = "Admin • PinBoard";
require "includes/header.php";
?>

<main class="admin-main">
    <section class="shell">
        <div class="admin-card">
            <div class="admin-heading">
                <div>
                    <span class="eyebrow dark-eyebrow">ADMIN</span>
                    <h1>User roles & permissions</h1>
                    <p>Manage Editor and Admin access.</p>
                </div>

                <a class="btn btn-secondary" href="index.php">
                    Back to Feed
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

            <form class="admin-search" method="GET">
                <input
                    class="input"
                    type="search"
                    name="search"
                    value="<?= e($search) ?>"
                    maxlength="30"
                    placeholder="Search username..."
                >

                <button class="btn btn-primary" type="submit">
                    Search
                </button>

                <?php if ($search !== ""): ?>
                    <a class="btn btn-secondary" href="admin_users.php">
                        Clear
                    </a>
                <?php endif; ?>
            </form>

            <div class="table-wrap">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Update Role</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="table-user">
                                        <span class="tiny-avatar">
                                            <?= e(strtoupper(substr($user["username"], 0, 1))) ?>
                                        </span>

                                        <div>
                                            <strong><?= e($user["username"]) ?></strong>

                                            <?php if ((int) $user["id"] === current_user_id()): ?>
                                                <small>You</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span
                                        class="role-pill <?= $user["role"] === "admin"
                                            ? "admin-role"
                                            : "editor-role" ?>"
                                    >
                                        <?= e(ucfirst($user["role"])) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= e(date("d M Y", strtotime($user["created_at"]))) ?>
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

                                        <select class="input compact" name="role" required>
                                            <option
                                                value="editor"
                                                <?= $user["role"] === "editor" ? "selected" : "" ?>
                                            >
                                                Editor
                                            </option>

                                            <option
                                                value="admin"
                                                <?= $user["role"] === "admin" ? "selected" : "" ?>
                                            >
                                                Admin
                                            </option>
                                        </select>

                                        <button class="btn btn-primary btn-small" type="submit">
                                            Save
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
