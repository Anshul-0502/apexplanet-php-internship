# Task-3: Advanced Features Implementation

## Apex Planet - PHP & MySQL Web Development Internship

### Timeline
10 Days

### Objective
Enhance the existing PHP CRUD blog application with advanced search, pagination, and an improved responsive user interface.

---

## Task-3 Features

### 1. Search Functionality
- Search form on the main dashboard.
- Search by post title.
- Search by post content.
- Uses a prepared MySQL `LIKE` query.
- Search keyword remains active while moving between pagination pages.
- Clear Search button resets the result list.
- Friendly no-result message is displayed when nothing matches.

### 2. Pagination
- 6 posts are displayed per page.
- Total matching posts are counted automatically.
- Total pages are calculated automatically.
- Previous and Next navigation is included.
- Numbered page links are included.
- Current page is highlighted.
- Search parameters are preserved in pagination URLs.

### 3. User Interface Improvements
- Professional responsive login page.
- Professional responsive registration page.
- Sticky navigation bar.
- Dashboard hero section.
- Search toolbar.
- Responsive blog post cards.
- Featured image support.
- Empty states and search result messages.
- Styled Create/Edit forms.
- Responsive pagination controls.
- Mobile/tablet/desktop layouts.

### Existing CRUD + Authentication Features
- User registration.
- User login.
- Secure `password_hash()` password storage.
- `password_verify()` during login.
- PHP session authentication.
- Create posts.
- Read/list posts.
- Update posts.
- Delete posts.
- Logout.
- Optional JPG/PNG/WEBP featured image upload.

---

## Technology Stack

- PHP
- MySQL / MariaDB
- HTML5
- CSS3
- XAMPP
- phpMyAdmin
- VS Code
- Git
- GitHub

---

## Project Structure

```text
task-3/
│
├── assets/
│   └── style.css
│
├── config/
│   └── db.php
│
├── includes/
│   ├── header.php
│   └── footer.php
│
├── uploads/
│   └── .gitkeep
│
├── create.php
├── delete.php
├── edit.php
├── index.php
├── login.php
├── logout.php
├── register.php
├── database.sql
├── migration_from_task2.sql
├── TASK-3-IMPLEMENTATION.md
├── .gitignore
└── README.md
```

---

# Database Schema

Database name:

```text
blogs
```

## users Table

| Column | Type | Description |
|---|---|---|
| id | INT | Primary key, auto increment |
| username | VARCHAR(100) | Unique username |
| password | VARCHAR(255) | Hashed password |

## posts Table

| Column | Type | Description |
|---|---|---|
| id | INT | Primary key, auto increment |
| title | VARCHAR(255) | Post title |
| content | TEXT | Post content |
| image | VARCHAR(255) | Optional uploaded image filename |
| created_at | TIMESTAMP | Date/time post was created |

---

# How to Run the Project

## Step 1 - Put the project inside XAMPP

Extract/copy the folder to:

```text
C:\xampp\htdocs\task-3
```

## Step 2 - Start the server

Open XAMPP Control Panel and start:

```text
Apache
MySQL
```

## Step 3 - Database setup

Open:

```text
http://localhost/phpmyadmin
```

### Fresh Task-3 database
Import:

```text
database.sql
```

### Reusing your old Task-2 `blogs` database
If your old `posts` table already exists but does NOT contain the `image` column, run this once:

```text
migration_from_task2.sql
```

Do not run the migration repeatedly after the column has already been added.

## Step 4 - Database connection

The connection settings are inside:

```text
config/db.php
```

If your local MySQL credentials change, edit that file.

## Step 5 - Open the application

Start from registration:

```text
http://localhost/task-3/register.php
```

Or, if you already have an account:

```text
http://localhost/task-3/login.php
```

After login, the main dashboard is:

```text
http://localhost/task-3/index.php
```

---

# Search Implementation

The dashboard reads the search term using:

```php
$search = trim($_GET["search"] ?? "");
```

When a keyword exists, the application searches both fields:

```sql
WHERE title LIKE ? OR content LIKE ?
```

Prepared statements are used instead of directly inserting user input into SQL.

Example:

```text
Search: PHP
```

Possible matches:

```text
PHP Tutorial
My PHP Internship Journey
Backend Development with PHP
```

A keyword inside the content can also produce a result.

---

# Pagination Implementation

The number of posts per page is:

```php
$postsPerPage = 6;
```

Current page:

```php
$page = max(1, (int)($_GET["page"] ?? 1));
```

Offset calculation:

```php
$offset = ($page - 1) * $postsPerPage;
```

Database query:

```sql
LIMIT ? OFFSET ?
```

The dashboard then displays:

```text
Previous  1  2  3  Next
```

When search is active, pagination keeps the search keyword in the URL.

Example:

```text
index.php?search=php&page=2
```

---

# Testing Guide

## Test Authentication
1. Open Register.
2. Create a new account.
3. Login with the same account.
4. Confirm Dashboard opens.
5. Logout.
6. Try opening `index.php` without login and confirm it redirects to Login.

## Test CRUD
1. Create a post.
2. Confirm it appears on Dashboard.
3. Edit the post.
4. Confirm changes appear.
5. Delete the post.
6. Confirm it is removed.

## Test Image Upload
1. Create a post.
2. Select JPG, PNG, or WEBP image.
3. Publish.
4. Confirm image appears on the post card.
5. Edit the post and replace/remove the image.

## Test Search
Create several posts with different titles/content.

Search for a word such as:

```text
PHP
```

Confirm only matching title/content results are displayed.

Then click Clear to display all posts again.

## Test Pagination
Create at least 13 posts.

Expected result:

```text
Page 1 -> 6 posts
Page 2 -> 6 posts
Page 3 -> 1 post
```

Test:
- Next
- Previous
- Page numbers
- Search + Pagination together

---

# Security Used

- Prepared SQL statements.
- Password hashing.
- Password verification.
- PHP sessions.
- `htmlspecialchars()` output escaping.
- POST request for deletion.
- Image MIME type validation.
- Maximum 5 MB image upload limit.

---

# Task-3 Deliverables

This folder includes:

- Functional PHP application.
- Search by title/content.
- Pagination.
- Improved responsive user interface.
- CRUD features.
- User authentication.
- Database schema.
- Migration file for Task-2 users.
- PHP source code.
- CSS source code.
- README documentation.
- 10-day implementation plan.

---

## Developed By

**Anshul Dhiman**  
Web Developer Intern  
**Apex Planet**  
PHP & MySQL Web Development

> Security note: Before pushing a public GitHub repository, do not expose your real local MySQL password. Replace it with a placeholder/example configuration if needed.
