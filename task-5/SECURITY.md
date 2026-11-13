# Security Measures

## SQL Injection
PDO prepared statements are used for dynamic database queries.

## Passwords
Passwords use `password_hash()` and `password_verify()`.

## Server Validation
Username, password, title, category, content and images are validated on the server.

## Client Validation
HTML constraints and JavaScript validation improve UX.

## CSRF
State-changing forms use random CSRF tokens generated with `random_bytes()` and checked with `hash_equals()`.

## XSS
Dynamic output is escaped using `htmlspecialchars()` through the `e()` helper.

## Sessions
The project uses strict session mode, HTTP-only cookies, SameSite=Lax and session ID regeneration after login.

## Authorization
Editors manage only their own posts. Admins can manage all posts and user roles.

## Upload Security
Images are MIME checked using `finfo`, limited to 5 MB, restricted to JPG/PNG/WEBP and saved with random server-generated filenames.

## Delete Security
Delete requests use POST, CSRF verification and server-side permission checks.

## Credentials
`config/database.php` is ignored by Git. Use `config/database.example.php` in public repositories.
