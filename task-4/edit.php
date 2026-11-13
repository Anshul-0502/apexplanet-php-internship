<?php

declare(strict_types=1);

require_once "includes/bootstrap.php";

require_login();

$postId = (int) ($_GET["id"] ?? 0);

$findPost = $pdo->prepare(
    "SELECT
        id,
        user_id,
        title,
        content,
        image,
        created_at
     FROM posts
     WHERE id = :id
     LIMIT 1"
);

$findPost->execute([
    "id" => $postId,
]);

$post = $findPost->fetch();

if (!$post) {
    http_response_code(404);
    exit("Post not found.");
}

if (!can_manage_post($post)) {
    http_response_code(403);
    exit(
        "You do not have permission "
        . "to edit this post."
    );
}

$errors = [];
$title = $post["title"];
$content = $post["content"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();

    $title = trim(
        (string) ($_POST["title"] ?? "")
    );

    $content = trim(
        (string) ($_POST["content"] ?? "")
    );

    $errors = array_merge(
        $errors,
        validate_post(
            $title,
            $content
        )
    );

    $imageFile = $_FILES["image"] ?? [];
    $imageError = validate_image($imageFile);

    if ($imageError !== null) {
        $errors[] = $imageError;
    }

    if (!$errors) {
        $currentImage = $post["image"];
        $newImageName = $currentImage;

        $hasNewImage =
            ($imageFile["error"] ?? UPLOAD_ERR_NO_FILE)
            !== UPLOAD_ERR_NO_FILE;

        if ($hasNewImage) {
            $storedImage = store_image($imageFile);

            if ($storedImage === null) {
                $errors[] = "Could not save the new image.";
            } else {
                $newImageName = $storedImage;
            }
        }

        $removeCurrentImage =
            isset($_POST["remove_image"])
            && $_POST["remove_image"] === "1";

        if (!$errors) {
            if ($removeCurrentImage && !$hasNewImage) {
                $newImageName = null;
            }

            $updatePost = $pdo->prepare(
                "UPDATE posts
                 SET
                    title = :title,
                    content = :content,
                    image = :image
                 WHERE id = :id"
            );

            $updatePost->execute([
                "title" => $title,
                "content" => $content,
                "image" => $newImageName,
                "id" => $postId,
            ]);

            if ($hasNewImage && $currentImage) {
                delete_image($currentImage);
            }

            if (
                $removeCurrentImage
                && !$hasNewImage
                && $currentImage
            ) {
                delete_image($currentImage);
            }

            redirect("index.php");
        }
    }
}

$pageTitle = "Edit Post • SecureBlog";

require "includes/header.php";
?>

<main class="page-main">
    <section class="container">
        <div class="form-card">
            <div class="form-head">
                <div>
                    <span class="eyebrow eyebrow-dark">
                        EDIT POST #<?= $postId ?>
                    </span>

                    <h1>Update post</h1>

                    <p>
                        <?php if (is_admin()): ?>
                            Administrator access:
                            you may edit any post.
                        <?php else: ?>
                            You may edit your own post.
                        <?php endif; ?>
                    </p>
                </div>

                <a
                    class="btn btn-ghost"
                    href="index.php"
                >
                    ← Dashboard
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
                class="form-stack js-validate"
                novalidate
            >
                <?= csrf_field() ?>

                <div class="field">
                    <label for="title">
                        Title
                    </label>

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
                    <label for="content">
                        Content
                    </label>

                    <textarea
                        class="input textarea"
                        id="content"
                        name="content"
                        minlength="10"
                        maxlength="10000"
                        required
                    ><?= e($content) ?></textarea>
                </div>

                <?php if (!empty($post["image"])): ?>
                    <div class="field">
                        <label>
                            Current image
                        </label>

                        <img
                            class="edit-preview"
                            src="uploads/<?= e($post["image"]) ?>"
                            alt="Current post image"
                        >

                        <label class="check-row">
                            <input
                                type="checkbox"
                                name="remove_image"
                                value="1"
                            >
                            Remove current image
                        </label>
                    </div>
                <?php endif; ?>

                <div class="field">
                    <label for="image">
                        Replace image
                        <span>(optional)</span>
                    </label>

                    <input
                        class="input"
                        id="image"
                        name="image"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        data-max-size="5242880"
                    >

                    <small>
                        JPG, PNG, or WEBP.
                        Maximum 5 MB.
                    </small>
                </div>

                <div class="form-actions">
                    <button
                        class="btn btn-primary"
                        type="submit"
                    >
                        Save Changes
                    </button>

                    <a
                        class="btn btn-ghost"
                        href="index.php"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </section>
</main>

<?php require "includes/footer.php"; ?>
