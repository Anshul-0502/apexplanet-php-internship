# Task-4: Security Enhancements

## Apex Planet PHP & MySQL Internship

Task-4 upgrades the previous CRUD application with security-focused features.

## Implemented Requirements

- PDO prepared statements
- Server-side validation
- Client-side validation
- Admin and Editor roles
- Role-based access control
- Security documentation

## Additional Security

- Password hashing
- CSRF protection
- Secure PHP sessions
- XSS output escaping
- POST-only delete operations
- Secure image validation
- Random upload filenames
- Last-admin protection

## Previous Features Preserved

- Registration
- Login
- Logout
- CRUD
- Search
- Pagination
- Image uploads
- Professional responsive UI

---

# Database

Task-4 uses:

```text
blogs2
```

---

# Project Structure

```text
task-4/
│
├── assets/
│   ├── app.js
│   └── style.css
│
├── config/
│   ├── database.php
│   └── database.example.php
│
├── includes/
│   ├── bootstrap.php
│   ├── footer.php
│   ├── header.php
│   └── security.php
│
├── uploads/
│   └── .gitkeep
│
├── admin_users.php
├── create.php
├── database.sql
├── delete.php
├── edit.php
├── index.php
├── login.php
├── logout.php
├── make_admin.sql
├── register.php
├── user_role_update.php
├── SECURITY.md
├── README.md
└── .gitignore
```

---

# Setup

## 1. Copy Project

Place the project at:

```text
C:\xampp\htdocs\task-4
```

## 2. Start XAMPP

Start:

```text
Apache
MySQL
```

## 3. Import Database

Open:

```text
http://localhost/phpmyadmin
```

Import:

```text
database.sql
```

This creates:

```text
blogs2
```

## 4. Open Project

Open:

```text
http://localhost/task-4/register.php
```

Register your first account.

New accounts automatically receive the:

```text
editor
```

role.

---

# Make First Admin

After registering your account, open phpMyAdmin.

Select:

```text
blogs2
```

Open SQL and run:

```sql
UPDATE users
SET role = 'admin'
WHERE username = 'YOUR_USERNAME';
```

Then logout and login again.

The navigation will now show:

```text
Users
```

---

# Role Permissions

## Editor

- Create post
- Search own posts
- View own posts
- Edit own posts
- Delete own posts

## Admin

- View all posts
- Create posts
- Edit any post
- Delete any post
- View all users
- Change user roles

---

# Security Tests

## SQL Injection

Try this username during login:

```text
' OR '1'='1
```

Login should fail.

## Validation

Try:

```text
Username: ab
Password: abc
Title: a
```

The application should reject these values.

## Authorization

Login as an Editor and manually open another user's post:

```text
edit.php?id=OTHER_POST_ID
```

The server should return:

```text
403 Access Denied
```

---

# Developed By

Anshul Dhiman

Web Developer Intern

Apex Planet

PHP & MySQL Web Development
