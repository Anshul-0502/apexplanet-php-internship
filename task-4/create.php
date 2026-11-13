<?php

declare(strict_types=1);

require_once "includes/bootstrap.php";

require_login();

$errors = [];
$title = "";
$content = "";

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
        $imageName = store_image($imageFile);

        if (
            ($imageFile["error"] ?? UPLOAD_ERR_NO_FILE)
            !== UPLOAD_ERR_NO_FILE
            && $imageName === null
        ) {
            $errors[] = "Could not save the image.";
        }
    }

    if (!$errors) {
        $insertPost = $pdo->prepare(
            "INSERT INTO posts (
                user_id,
                title,
                content,
                image
            ) VALUES (
                :user_id,
                :title,
                :content,
                :image
            )"
        );

        $insertPost->execute([
            "user_id" => current_user_id(),
            "title" => $title,
            "content" => $content,
            "image" => $imageName,
        ]);

        redirect("index.php");
    }
}

$pageTitle = "Create Post • SecureBlog";

require "includes/header.php";
?>

<main class="page-main">
    <section class="container">
        <div class="form-card">
            <div class="form-head">
                <div>
                    <span class="eyebrow eyebrow-dark">
                        CREATE POST
                    </span>

                    <h1>Publish securely</h1>

                    <p>
                        All fields are validated on both
                        client and server.
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

                <div class="field">
                    <label for="image">
                        Featured image
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
                        Publish Post
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
