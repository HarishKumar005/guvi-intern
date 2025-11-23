
# GUVI Internship Project – Register/Login/Profile (PHP + Redis + MySQL + AJAX)

This is a complete implementation for the GUVI internship task with separated frontend and backend files, using Bootstrap for responsive forms, jQuery AJAX for communication, PHP (PDO) with prepared statements for database access, Redis for session storage, and optional MongoDB for audit logs.

---

## Included Files & Structure

```
guvi_project_full/
  frontend/
    index.html          # Register page
    login.html          # Login page
    profile.html        # Profile page + logout button
    css/
      style.css
    js/
      register.js
      login.js
      profile.js
  backend/
    uploads/
      profile/
    db/
      config.php        # Edit DB/Redis/Mongo credentials here
      mysql.php         # PDO connection
      redis.php         # Redis connection (phpredis)
      mongo.php         # Optional MongoDB client (requires extension)
    register.php
    login.php
    profile_get.php
    profile_update.php
    logout.php
    change_password.php
    delete_photo.php
    upload_photo.php
  README.md
```

---

1) Prerequisites (show screen)
- Install XAMPP/LAMP/WAMP or a PHP environment with:
  - PHP 8+
  - Extensions: `pdo_mysql`, `redis` (phpredis). `mongodb` is optional.
- Install MySQL and Redis and start both services.
- (Optional) Install MongoDB if you want audit logs.

2) Database Setup (show terminal or phpMyAdmin)
- Open terminal or phpMyAdmin and run:

```sql
CREATE DATABASE guvi_intern;
USE guvi_intern;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  age INT DEFAULT NULL,
  dob DATE DEFAULT NULL,
  contact VARCHAR(50) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

3) Configure project (show file edit)
- Open `backend/db/config.php` and set your MySQL user/password and Redis host/port.
- If using MongoDB, enter its URI as well.

4) Deploy files (show file explorer)
- Place `frontend/` files in your web root (e.g., `htdocs/frontend`).
- Place `backend/` files in web-accessible location (e.g., `htdocs/backend`) or route accordingly.
- Ensure PHP can read/write temp directory for session fallback.

5) Test registration (screen recording)
- Open `http://localhost/frontend/index.html`
- Fill name/email/password and click Register.
- Expect success message then go to Login.

6) Test login and token (screen recording)
- Open `http://localhost/frontend/login.html`
- Login with registered credentials.
- On success, token is stored in `localStorage` (open devtools -> Application -> Local Storage to show token).
- You are redirected to `profile.html`.

7) Test profile update & logout (screen recording)
- On `profile.html` edit profile fields and click Save.
- Profile updates via AJAX and database updates succeed.
- Click Logout -> frontend calls `backend/logout.php` and clears `localStorage` then redirects to login page.

8) Troubleshooting tips (show console + terminal)
- If Redis not available, sessions fallback to temp files (development only).
- Check PHP error logs if endpoints return 500.
- Ensure database credentials in `config.php` are correct.

---

Notes & Security
- This project stores the token in `localStorage` (as required by task). For production systems prefer HttpOnly cookies with CSRF protections.
- Use HTTPS in production.
- Redis should be secured with authentication in production.
- Add rate limiting for authentication endpoints.

---
