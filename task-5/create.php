<?php
declare(strict_types=1);

require_once "includes/bootstrap.php";
require_login();

$errors = [];
$title = "";
$content = "";
$category = "";

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

    $imageName = null;

    if (!$errors) {
        $hasImage =
            ($imageFile["error"] ?? UPLOAD_ERR_NO_FILE)
            !== UPLOAD_ERR_NO_FILE;

        if ($hasImage) {
            $imageName = store_image($imageFile);

            if ($imageName === null) {
                $errors[] = "Could not save the uploaded image.";
            }
        }
    }

    if (!$errors) {
        $insert = $pdo->prepare(
            "INSERT INTO posts (
                user_id,
                title,
                content,
                category,
                image
             ) VALUES (
                :user_id,
                :title,
                :content,
                :category,
                :image
             )"
        );

        $insert->execute([
            "user_id" => current_user_id(),
            "title" => $title,
            "content" => $content,
            "category" => $category,
            "image" => $imageName,
        ]);

        redirect("index.php");
    }
}

$pageTitle = "Create Post • PinBoard";
require "includes/header.php";
?>

<main class="editor-main">
    <section class="shell">
        <div class="editor-card">
            <div class="editor-heading">
                <div>
                    <span class="eyebrow dark-eyebrow">CREATE</span>
                    <h1>Share a new idea</h1>
                    <p>Add a visual, title, category and description.</p>
                </div>

                <a class="btn btn-secondary" href="index.php">
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
                    <label class="upload-box" for="image">
                        <span class="upload-icon">↑</span>
                        <strong>Choose a featured image</strong>
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
                            placeholder="Add a clear title"
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
                            placeholder="Design, PHP, Travel..."
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
                            placeholder="Tell people about this idea..."
                            required
                        ><?= e($content) ?></textarea>
                    </div>

                    <button class="btn btn-primary btn-block" type="submit">
                        Publish Post
                    </button>
                </div>
            </form>
        </div>
    </section>
</main>

<?php require "includes/footer.php"; ?>
