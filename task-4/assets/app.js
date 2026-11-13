document.addEventListener("DOMContentLoaded", () => {
    const forms = document.querySelectorAll(".js-validate");

    forms.forEach((form) => {
        form.addEventListener("submit", (event) => {
            clearClientErrors(form);

            let isValid = true;

            const username = form.querySelector(
                '[name="username"]'
            );

            if (username && username.value.trim() !== "") {
                const usernamePattern =
                    /^[A-Za-z0-9_]{3,30}$/;

                if (!usernamePattern.test(
                    username.value.trim()
                )) {
                    showClientError(
                        username,
                        "Use 3–30 letters, numbers, "
                        + "or underscores."
                    );

                    isValid = false;
                }
            }

            const password = form.querySelector(
                '[name="password"]'
            );

            const confirmPassword = form.querySelector(
                '[name="confirm_password"]'
            );

            if (
                password
                && confirmPassword
                && password.value !== ""
            ) {
                const passwordValue = password.value;

                const strongEnough =
                    passwordValue.length >= 8
                    && /[A-Z]/.test(passwordValue)
                    && /[a-z]/.test(passwordValue)
                    && /[0-9]/.test(passwordValue);

                if (!strongEnough) {
                    showClientError(
                        password,
                        "Password needs 8+ characters, "
                        + "uppercase, lowercase, and a number."
                    );

                    isValid = false;
                }

                if (
                    password.value
                    !== confirmPassword.value
                ) {
                    showClientError(
                        confirmPassword,
                        "Passwords do not match."
                    );

                    isValid = false;
                }
            }

            const title = form.querySelector(
                '[name="title"]'
            );

            if (title) {
                const titleLength =
                    title.value.trim().length;

                if (
                    titleLength < 3
                    || titleLength > 150
                ) {
                    showClientError(
                        title,
                        "Title must be between "
                        + "3 and 150 characters."
                    );

                    isValid = false;
                }
            }

            const content = form.querySelector(
                '[name="content"]'
            );

            if (content) {
                const contentLength =
                    content.value.trim().length;

                if (
                    contentLength < 10
                    || contentLength > 10000
                ) {
                    showClientError(
                        content,
                        "Content must be between "
                        + "10 and 10,000 characters."
                    );

                    isValid = false;
                }
            }

            const imageInput = form.querySelector(
                'input[type="file"][data-max-size]'
            );

            if (
                imageInput
                && imageInput.files.length > 0
            ) {
                const file = imageInput.files[0];

                const maxSize = Number(
                    imageInput.dataset.maxSize
                );

                const allowedTypes = [
                    "image/jpeg",
                    "image/png",
                    "image/webp",
                ];

                if (file.size > maxSize) {
                    showClientError(
                        imageInput,
                        "Image must be 5 MB or smaller."
                    );

                    isValid = false;
                }

                if (!allowedTypes.includes(file.type)) {
                    showClientError(
                        imageInput,
                        "Only JPG, PNG, and WEBP "
                        + "images are allowed."
                    );

                    isValid = false;
                }
            }

            if (!form.checkValidity()) {
                form.reportValidity();
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
    });

    function clearClientErrors(form) {
        form.querySelectorAll(
            ".client-error"
        ).forEach((element) => {
            element.remove();
        });
    }

    function showClientError(input, message) {
        const error = document.createElement("small");

        error.className = "client-error";
        error.textContent = message;

        input.insertAdjacentElement(
            "afterend",
            error
        );
    }
});
