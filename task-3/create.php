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
    $imageName = null;

    if ($title === "" || $content === "") {
        $error = "Title and content are required.";
    }

    if (!$error && isset($_FILES["image"]) && $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
            $error = "Image upload failed. Please try again.";
        } elseif ($_FILES["image"]["size"] > 5 * 1024 * 1024) {
            $error = "Image must be smaller than 5 MB.";
        } else {
            $allowedTypes = [
                "image/jpeg" => "jpg",
                "image/png" => "png",
                "image/webp" => "webp"
            ];

            $fileType = mime_content_type($_FILES["image"]["tmp_name"]);

            if (!isset($allowedTypes[$fileType])) {
                $error = "Only JPG, PNG and WEBP images are allowed.";
            } else {
                $imageName = uniqid("post_", true) . "." . $allowedTypes[$fileType];
                $uploadPath = __DIR__ . "/uploads/" . $imageName;

                if (!move_uploaded_file($_FILES["image"]["tmp_name"], $uploadPath)) {
                    $error = "Could not save the uploaded image.";
                }
            }
        }
    }

    if (!$error) {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO posts (title, content, image) VALUES (?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "sss", $title, $content, $imageName);
        mysqli_stmt_execute($stmt);

        header("Location: index.php");
        exit();
    }
}

$pageTitle = "Create Post • BlogSpace Pro";
include "includes/header.php";
?>

<main>
    <section class="form-shell">
        <p class="small">TASK 3 • CREATE CONTENT</p>
        <h1>Create a new post</h1>
        <p class="sub">Add a title, content and an optional featured image.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Post title</label>
                <input class="input" id="title" type="text" name="title"
                       placeholder="Enter a clear, engaging title"
                       value="<?= htmlspecialchars($_POST["title"] ?? "") ?>" required>
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <textarea class="textarea" id="content" name="content"
                          placeholder="Write your post here..." required><?= htmlspecialchars($_POST["content"] ?? "") ?></textarea>
            </div>

            <div class="form-group">
                <label for="image">Featured image <span class="small">(optional)</span></label>
                <input class="input" id="image" type="file" name="image"
                       accept="image/jpeg,image/png,image/webp">
                <p class="small" style="margin-top:7px;">JPG, PNG or WEBP • Maximum 5 MB</p>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Publish Post</button>
                <a class="btn btn-light" href="index.php">Cancel</a>
            </div>
        </form>
    </section>
</main>

<?php include "includes/footer.php"; ?>
