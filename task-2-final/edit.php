<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "config/db.php";

$id = (int)($_GET["id"] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT * FROM posts WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$post = mysqli_fetch_assoc($result);

if (!$post) {
    die("Post not found.");
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");

    if ($title === "" || $content === "") {
        $error = "Title and content are required.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE posts SET title = ?, content = ? WHERE id = ?"
        );
        mysqli_stmt_bind_param($stmt, "ssi", $title, $content, $id);
        mysqli_stmt_execute($stmt);

        header("Location: index.php");
        exit();
    }
}

$pageTitle = "Edit Post • BlogSpace";
include "includes/header.php";
?>

<main>
    <section class="form-shell">
        <p class="small">POST #<?= $id ?></p>
        <h1>Edit post</h1>
        <p class="sub">Update the content below and save your changes.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="title">Post title</label>
                <input class="input" id="title" type="text" name="title"
                       value="<?= htmlspecialchars($post["title"]) ?>" required>
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <textarea class="textarea" id="content" name="content"
                          required><?= htmlspecialchars($post["content"]) ?></textarea>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Save Changes</button>
                <a class="btn btn-light" href="index.php">Cancel</a>
            </div>
        </form>
    </section>
</main>

<?php include "includes/footer.php"; ?>
