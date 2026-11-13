document.addEventListener("DOMContentLoaded", () => {
    setupValidation();
    setupImagePreview();
});

function setupValidation() {
    document.querySelectorAll(".js-validate").forEach((form) => {
        form.addEventListener("submit", (event) => {
            form.querySelectorAll(".client-error").forEach((el) => el.remove());

            let valid = true;

            const username = form.querySelector('[name="username"]');
            const password = form.querySelector('[name="password"]');
            const confirmPassword = form.querySelector('[name="confirm_password"]');
            const title = form.querySelector('[name="title"]');
            const category = form.querySelector('[name="category"]');
            const content = form.querySelector('[name="content"]');
            const image = form.querySelector('input[type="file"][data-max-size]');

            if (username && username.value.trim() !== "") {
                if (!/^[A-Za-z0-9_]{3,30}$/.test(username.value.trim())) {
                    showError(username, "Use 3–30 letters, numbers or underscores.");
                    valid = false;
                }
            }

            if (password && confirmPassword && password.value !== "") {
                const value = password.value;

                if (
                    value.length < 8
                    || !/[A-Z]/.test(value)
                    || !/[a-z]/.test(value)
                    || !/[0-9]/.test(value)
                ) {
                    showError(
                        password,
                        "Use 8+ characters with uppercase, lowercase and a number."
                    );
                    valid = false;
                }

                if (password.value !== confirmPassword.value) {
                    showError(confirmPassword, "Passwords do not match.");
                    valid = false;
                }
            }

            if (title) {
                const length = title.value.trim().length;

                if (length < 3 || length > 150) {
                    showError(title, "Title must be between 3 and 150 characters.");
                    valid = false;
                }
            }

            if (category) {
                const length = category.value.trim().length;

                if (length < 1 || length > 50) {
                    showError(category, "Category is required and must be 50 characters or fewer.");
                    valid = false;
                }
            }

            if (content) {
                const length = content.value.trim().length;

                if (length < 10 || length > 10000) {
                    showError(content, "Content must be between 10 and 10,000 characters.");
                    valid = false;
                }
            }

            if (image && image.files.length > 0) {
                const file = image.files[0];
                const max = Number(image.dataset.maxSize);
                const allowed = ["image/jpeg", "image/png", "image/webp"];

                if (file.size > max) {
                    showError(image, "Image must be 5 MB or smaller.");
                    valid = false;
                }

                if (!allowed.includes(file.type)) {
                    showError(image, "Only JPG, PNG and WEBP images are allowed.");
                    valid = false;
                }
            }

            if (!form.checkValidity()) {
                form.reportValidity();
                valid = false;
            }

            if (!valid) {
                event.preventDefault();
            }
        });
    });
}

function setupImagePreview() {
    document.querySelectorAll('input[type="file"][accept*="image"]').forEach((input) => {
        input.addEventListener("change", () => {
            const file = input.files[0];
            const label = input.closest(".upload-box");

            if (!file || !label) {
                return;
            }

            const reader = new FileReader();

            reader.addEventListener("load", () => {
                label.style.backgroundImage =
                    `linear-gradient(rgba(0,0,0,.18), rgba(0,0,0,.18)), url("${reader.result}")`;

                label.classList.add("has-preview");
            });

            reader.readAsDataURL(file);
        });
    });
}

function showError(input, message) {
    const error = document.createElement("small");
    error.className = "client-error";
    error.textContent = message;
    input.insertAdjacentElement("afterend", error);
}
