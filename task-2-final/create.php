<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "config/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");

    if ($title === "" || $content === "") {
        $error = "Title and content are required.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO posts (title, content) VALUES (?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ss", $title, $content);
        mysqli_stmt_execute($stmt);

        header("Location: index.php");
        exit();
    }
}

$pageTitle = "Create Post • BlogSpace";
include "includes/header.php";
?>

<main>
    <section class="form-shell">
        <p class="small">BLOG MANAGEMENT</p>
        <h1>Create a new post</h1>
        <p class="sub">Add a title and your content, then publish it to the dashboard.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="title">Post title</label>
                <input class="input" id="title" type="text" name="title"
                       placeholder="Enter a clear, engaging title" required>
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <textarea class="textarea" id="content" name="content"
                          placeholder="Write your post here..." required></textarea>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Publish Post</button>
                <a class="btn btn-light" href="index.php">Cancel</a>
            </div>
        </form>
    </section>
</main>

<?php include "includes/footer.php"; ?>
