<?php
declare(strict_types=1);

require_once "includes/bootstrap.php";
require_login();

$postId = (int) ($_GET["id"] ?? 0);

$findPost = $pdo->prepare(
    "SELECT id, user_id, title, content, category, image, created_at
     FROM posts
     WHERE id = :id
     LIMIT 1"
);

$findPost->execute(["id" => $postId]);
$post = $findPost->fetch();

if (!$post) {
    http_response_code(404);
    exit("Post not found.");
}

if (!can_manage_post($post)) {
    http_response_code(403);
    exit("You do not have permission to edit this post.");
}

$errors = [];
$title = $post["title"];
$content = $post["content"];
$category = $post["category"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();

    $title = trim((string) ($_POST["title"] ?? ""));
    $content = trim((string) ($_POST["content"] ?? ""));
    $category = trim((string) ($_POST["category"] ?? ""));

    $errors = array_merge(
        $errors,
        validate_post($title, $content, $category)
    );

    $imageFile = $_FILES["image"] ?? [];
    $imageError = validate_image($imageFile);

    if ($imageError !== null) {
        $errors[] = $imageError;
    }

    if (!$errors) {
        $currentImage = $post["image"];
        $newImage = $currentImage;

        $hasNewImage =
            ($imageFile["error"] ?? UPLOAD_ERR_NO_FILE)
            !== UPLOAD_ERR_NO_FILE;

        if ($hasNewImage) {
            $storedImage = store_image($imageFile);

            if ($storedImage === null) {
                $errors[] = "Could not save the uploaded image.";
            } else {
                $newImage = $storedImage;
            }
        }

        $removeImage =
            isset($_POST["remove_image"])
            && $_POST["remove_image"] === "1";

        if (!$errors) {
            if ($removeImage && !$hasNewImage) {
                $newImage = null;
            }

            $update = $pdo->prepare(
                "UPDATE posts
                 SET
                    title = :title,
                    content = :content,
                    category = :category,
                    image = :image
                 WHERE id = :id"
            );

            $update->execute([
                "title" => $title,
                "content" => $content,
                "category" => $category,
                "image" => $newImage,
                "id" => $postId,
            ]);

            if ($hasNewImage && $currentImage) {
                delete_image($currentImage);
            }

            if ($removeImage && !$hasNewImage && $currentImage) {
                delete_image($currentImage);
            }

            redirect("view.php?id=" . $postId);
        }
    }
}

$pageTitle = "Edit Post • PinBoard";
require "includes/header.php";
?>

<main class="editor-main">
    <section class="shell">
        <div class="editor-card">
            <div class="editor-heading">
                <div>
                    <span class="eyebrow dark-eyebrow">EDIT</span>
                    <h1>Update your post</h1>
                    <p>Refine the content or replace the visual.</p>
                </div>

                <a class="btn btn-secondary" href="view.php?id=<?= $postId ?>">
                    Cancel
                </a>
            </div>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form
                method="POST"
                enctype="multipart/form-data"
                class="composer js-validate"
                novalidate
            >
                <?= csrf_field() ?>

                <div class="upload-panel">
                    <?php if (!empty($post["image"])): ?>
                        <img
                            class="edit-image-preview"
                            src="uploads/<?= e($post["image"]) ?>"
                            alt="Current image"
                        >

                        <label class="remove-image-row">
                            <input type="checkbox" name="remove_image" value="1">
                            Remove current image
                        </label>
                    <?php endif; ?>

                    <label class="upload-box" for="image">
                        <span class="upload-icon">↑</span>
                        <strong>
                            <?= !empty($post["image"]) ? "Replace image" : "Add image" ?>
                        </strong>
                        <small>JPG, PNG or WEBP • Maximum 5 MB</small>

                        <input
                            id="image"
                            name="image"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            data-max-size="5242880"
                        >
                    </label>
                </div>

                <div class="composer-fields">
                    <div class="field">
                        <label for="title">Title</label>
                        <input
                            class="input"
                            id="title"
                            name="title"
                            type="text"
                            value="<?= e($title) ?>"
                            minlength="3"
                            maxlength="150"
                            required
                        >
                    </div>

                    <div class="field">
                        <label for="category">Category</label>
                        <input
                            class="input"
                            id="category"
                            name="category"
                            type="text"
                            value="<?= e($category) ?>"
                            maxlength="50"
                            required
                        >
                    </div>

                    <div class="field">
                        <label for="content">Description</label>
                        <textarea
                            class="input textarea"
                            id="content"
                            name="content"
                            minlength="10"
                            maxlength="10000"
                            required
                        ><?= e($content) ?></textarea>
                    </div>

                    <button class="btn btn-primary btn-block" type="submit">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </section>
</main>

<?php require "includes/footer.php"; ?>
