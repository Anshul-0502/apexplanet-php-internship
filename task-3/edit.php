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
    http_response_code(404);
    die("Post not found.");
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");
    $imageName = $post["image"] ?? null;

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
                $newImageName = uniqid("post_", true) . "." . $allowedTypes[$fileType];
                $uploadPath = __DIR__ . "/uploads/" . $newImageName;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $uploadPath)) {
                    if (!empty($imageName)) {
                        $oldPath = __DIR__ . "/uploads/" . $imageName;
                        if (is_file($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                    $imageName = $newImageName;
                } else {
                    $error = "Could not save the uploaded image.";
                }
            }
        }
    }

    if (!$error && isset($_POST["remove_image"]) && $_POST["remove_image"] === "1") {
        if (!empty($imageName)) {
            $oldPath = __DIR__ . "/uploads/" . $imageName;
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }
        $imageName = null;
    }

    if (!$error) {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE posts SET title = ?, content = ?, image = ? WHERE id = ?"
        );
        mysqli_stmt_bind_param($stmt, "sssi", $title, $content, $imageName, $id);
        mysqli_stmt_execute($stmt);

        header("Location: index.php");
        exit();
    }
}

$pageTitle = "Edit Post • BlogSpace Pro";
include "includes/header.php";
?>

<main>
    <section class="form-shell">
        <p class="small">TASK 3 • POST #<?= $id ?></p>
        <h1>Edit post</h1>
        <p class="sub">Update the title, content or featured image.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Post title</label>
                <input class="input" id="title" type="text" name="title"
                       value="<?= htmlspecialchars($_POST["title"] ?? $post["title"]) ?>" required>
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <textarea class="textarea" id="content" name="content" required><?= htmlspecialchars($_POST["content"] ?? $post["content"]) ?></textarea>
            </div>

            <?php if (!empty($post["image"])): ?>
                <div class="form-group">
                    <label>Current image</label>
                    <img src="uploads/<?= htmlspecialchars($post["image"]) ?>"
                         class="edit-preview" alt="Current image">
                    <label class="remove-image-row">
                        <input type="checkbox" name="remove_image" value="1">
                        Remove current image
                    </label>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="image">Change image <span class="small">(optional)</span></label>
                <input class="input" id="image" type="file" name="image"
                       accept="image/jpeg,image/png,image/webp">
                <p class="small" style="margin-top:7px;">JPG, PNG or WEBP • Maximum 5 MB</p>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Save Changes</button>
                <a class="btn btn-light" href="index.php">Cancel</a>
            </div>
        </form>
    </section>
</main>

<?php include "includes/footer.php"; ?>
