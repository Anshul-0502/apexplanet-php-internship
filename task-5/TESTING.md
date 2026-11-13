# Task-5 Testing & Debugging Checklist

## Functional Testing

- Register a valid user
- Reject duplicate username
- Reject weak password
- Login with valid credentials
- Reject invalid credentials
- Create post with and without image
- View post detail
- Edit title, category, content and image
- Delete own post
- Logout

## Search Testing

Test search by:

- Title
- Content
- Category
- Username

## Pagination Testing

Create at least 19 posts.

Expected:

```text
Page 1 = 9 posts
Page 2 = 9 posts
Page 3 = 1 post
```

Test pagination with search and category filters active.

## Role Testing

### Editor
- Can browse all posts
- Can edit/delete own posts
- Cannot edit another user's post by changing the URL
- Cannot open Admin page

### Admin
- Can manage all posts
- Can open Admin page
- Can change roles
- Cannot demote the final remaining Admin

## Security Testing

### SQL Injection

Try:

```text
' OR '1'='1
```

Expected: login fails.

### XSS

Post:

```html
<script>alert('xss')</script>
```

Expected: text is escaped and no script executes.

### CSRF

Remove or change a CSRF token before submitting a state-changing form.

Expected: 403 error.

### Authorization

As Editor A, manually open Editor B's:

```text
edit.php?id=OTHER_POST_ID
```

Expected: 403 access denied.

## Usability Testing

Check:

- Desktop layout
- Tablet layout
- Mobile layout
- Search bar
- Masonry feed
- Create/Edit forms
- Detail page
- Profile page
- Admin table

## Final Demo Flow

```text
Register
→ Login
→ Create Image Post
→ Create More Posts
→ Search
→ Filter Category
→ Pagination
→ View Post
→ Edit Post
→ View Profile
→ Admin Role Management
→ Delete Post
→ Logout
```
