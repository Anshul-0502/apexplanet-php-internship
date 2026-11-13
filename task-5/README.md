# Task-5 Final Project — PinBoard

## Apex Planet PHP & MySQL Internship

Task-5 integrates all previous internship features into one polished final application.

The UI uses an original visual-discovery / masonry-feed design with light inspiration from modern pin-board social layouts.

## Included Features

- Registration, Login, Logout
- Password hashing
- CRUD operations
- Image uploads
- Search by title, content, category and username
- Category filters
- Pagination
- User profiles
- Admin / Editor roles
- Role-based permissions
- PDO prepared statements
- Server-side validation
- Client-side validation
- CSRF protection
- XSS escaping
- Secure sessions
- Responsive UI
- Security documentation
- Testing documentation

## Database

```text
blogs3
```

## Project Structure

```text
task-5/
├── assets/
│   ├── app.js
│   └── style.css
├── config/
│   ├── database.php
│   └── database.example.php
├── includes/
│   ├── bootstrap.php
│   ├── footer.php
│   ├── header.php
│   └── security.php
├── uploads/
├── admin_users.php
├── create.php
├── database.sql
├── delete.php
├── edit.php
├── index.php
├── login.php
├── logout.php
├── make_admin.sql
├── profile.php
├── register.php
├── user_role_update.php
├── view.php
├── README.md
├── SECURITY.md
├── TESTING.md
└── .gitignore
```

## Run with XAMPP

1. Extract the folder to:

```text
C:\xampp\htdocs\task-5
```

2. Start Apache and MySQL.

3. Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

4. Import:

```text
database.sql
```

5. Open:

```text
http://localhost/task-5/register.php
```

6. Register and login.

## Make the First Admin

After registering, run:

```sql
USE blogs3;

UPDATE users
SET role = 'admin'
WHERE username = 'YOUR_USERNAME';
```

Logout and login again.

## Roles

### Editor
- Browse all posts
- Search and filter
- Create posts
- Edit own posts
- Delete own posts
- View profiles

### Admin
- All Editor permissions
- Edit/delete any post
- Manage user roles

## Recommended Demo Data

Create at least 12–15 posts using categories such as:

```text
Web Design
PHP
MySQL
Travel
Food
Technology
Coding
UI UX
```

That makes search, filters, pagination and the visual feed easy to demonstrate.
