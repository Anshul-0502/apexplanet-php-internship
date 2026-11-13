# Task-4 Security Documentation

## 1. PDO Prepared Statements

All queries that use user-controlled values are executed through PDO prepared statements.

Example:

```php
$stmt = $pdo->prepare(
    "SELECT id, username, password, role
     FROM users
     WHERE username = :username
     LIMIT 1"
);

$stmt->execute([
    "username" => $username,
]);
```

PDO emulated prepared statements are disabled:

```php
PDO::ATTR_EMULATE_PREPARES => false
```

This significantly reduces SQL injection risk.

---

## 2. Server-Side Validation

Server-side validation is implemented in:

```text
includes/security.php
```

Validated fields include:

- Username
- Password
- Post title
- Post content
- Image size
- Image MIME type
- User role values

Server-side validation is always treated as the final source of truth.

---

## 3. Client-Side Validation

Client-side validation is implemented in:

```text
assets/app.js
```

HTML attributes are also used:

```text
required
minlength
maxlength
pattern
accept
```

Client validation improves UX but is never trusted by itself.

---

## 4. Password Security

Passwords are stored using:

```php
password_hash()
```

Login verification uses:

```php
password_verify()
```

Plain-text passwords are never stored in the database.

---

## 5. Session Security

The application uses:

- Strict session mode
- HTTP-only cookies
- SameSite=Lax cookies
- Secure cookies automatically on HTTPS
- Session ID regeneration after successful login

---

## 6. CSRF Protection

State-changing forms contain a CSRF token.

Tokens are generated using:

```php
random_bytes(32)
```

Verification uses:

```php
hash_equals()
```

CSRF protection is applied to:

- Registration
- Login
- Create post
- Edit post
- Delete post
- User role changes

---

## 7. XSS Protection

Output is escaped through:

```php
htmlspecialchars()
```

The helper function:

```php
e($value)
```

is used throughout the project.

---

## 8. Role-Based Access Control

The application provides two roles:

```text
admin
editor
```

### Editor

Editors can:

- Login
- Create posts
- Search posts
- View their own posts
- Edit their own posts
- Delete their own posts

### Admin

Admins can:

- View all posts
- Create posts
- Edit any post
- Delete any post
- Open user management
- Promote users
- Demote users

The final administrator cannot be demoted.

---

## 9. Secure File Uploads

Uploaded images are validated by MIME type using PHP `finfo`.

Allowed formats:

```text
JPG
PNG
WEBP
```

Maximum size:

```text
5 MB
```

Uploaded files receive random names generated using:

```php
random_bytes()
```

Original filenames are not trusted.

---

## 10. Protected Delete Requests

Delete operations use POST requests.

Every delete requires:

- Authentication
- Authorization
- CSRF verification

---

## 11. Database Credentials

Real credentials are stored only in:

```text
config/database.php
```

The project `.gitignore` excludes this file.

A safe template is provided:

```text
config/database.example.php
```
