# AI Coding Agent Instructions - PHP Member Management System

## 🏗️ Architecture Overview

This is a **single-tier MVC PHP application** (no frontend/backend separation) deployed via Docker Compose. All code runs in the same Apache container.

### Key Components
- **Entry Point**: `app/public/index.php` - Router and session initializer
- **Database Layer**: `app/src/config/Database.php` - PDO singleton for MySQL 8.0
- **Auth Middleware**: `app/src/middleware/AuthMiddleware.php` - Session-based access control
- **Utilities**: `app/src/utils/Helper.php` - Validation, encryption, output helpers
- **Views**: `app/src/views/` - Inline HTML/Tailwind templates (no template engine)

### Data Flow
```
Request → index.php (SESSION start + Router)
        → Handle POST actions (login/register/profile updates) BEFORE output
        → Determine page from $_GET['page']
        → Load corresponding view from app/src/views/pages/
        → View directly queries DB via Database singleton
        → Output HTML with embedded PHP
```

**CRITICAL**: All POST handling happens in index.php BEFORE any HTML output to allow headers/redirects

## 🔑 Critical Patterns & Conventions

### Database Access
- **Always use PDO prepared statements** (parameters array as 2nd arg)
  ```php
  $db->query("SELECT * FROM users WHERE id = ?", [$userId])
  $db->fetch(...) // Returns single row
  $db->fetchAll(...) // Returns array of rows
  $db->execute(...) // For INSERT/UPDATE/DELETE
  ```
- **Singleton pattern**: `Database::getInstance()->getConnection()`
- **Never concatenate SQL strings** - all queries must be parameterized

### User Authentication
- Session-based, stored in `$_SESSION`
- Admin role: `$_SESSION['user_role'] === 'admin'`
- Check login: `AuthMiddleware::isLoggedIn()` or `AuthMiddleware::requireLogin()`
- Current user info: `AuthMiddleware::getCurrentUser()` returns array with id/username/email/role/avatar

### Form Validation & Output
- **Input**: Always validate with `Helper::validate*()` functions
  - `validateEmail()`, `validateUsername()`, `validatePassword()`, `validatePhone()`
- **Output**: Always use `Helper::escape()` to prevent XSS
  - **CRITICAL**: `Helper::escape()` handles NULL values (returns empty string if NULL)
- **Passwords**: Hash with `Helper::hashPassword()`, verify with `Helper::verifyPassword()`
- **Optional Fields**: Email is optional - validate only if non-empty, store as NULL if empty

### View Architecture
- No template engine - views are `.php` files with inline HTML + PHP
- Views access `$db = Database::getInstance()` directly
- All pages follow pattern: Check auth → Get data → Output HTML
- Tailwind CSS for styling (no separate CSS files)
- SweetAlert2 for modals (loaded in layout.php)

## 🐳 Docker & Startup

### Build & Run
```bash
cd e:\work\990
docker compose up --build          # First run: builds image + starts containers
docker compose up                  # Subsequent runs: just start
```

### Key Services
- **db**: MySQL 8.0 (port 3306), auto-initializes via `DBInitializer.php`
- **web**: PHP 8.2 + Apache (port **8080** → 80 internal)
- Network: Both containers connect via `app_network`
- **Access**: http://localhost:8080 (not port 80)

### Database Initialization
- Runs on first access via `app/public/index.php` (checks if `users` table exists)
- File: `app/src/config/DBInitializer.php`
- Creates tables: `users`, `login_logs`
- Seeds demo data: admin account + 5 test users
- Can be triggered via web UI: Visit http://localhost:8080 on first run, click "初始化数据库" button
- Marker check: Queries `SELECT COUNT(*) FROM users` to determine if initialized

### Environment Variables
Set in `docker-compose.yml` `web` service `environment`:
- `DB_HOST=db` (service name, not localhost)
- `DB_USER`, `DB_PASSWORD`, `DB_NAME`, `DB_PORT`
- Read in `app/src/config/config.php` via `getenv()`

## 📁 File Organization

```
app/
├── public/index.php                # Main entry point
├── vhost.conf                      # Apache config (Rewrite rules)
└── src/
    ├── config/
    │   ├── config.php              # Constants (database, app settings)
    │   ├── Database.php            # PDO singleton class
    │   └── DBInitializer.php       # Auto-init script (runs on first access)
    ├── middleware/
    │   └── AuthMiddleware.php      # Session checks & permission gates
    ├── utils/
    │   └── Helper.php              # Validation, encryption, sanitization
    ├── views/
    │   ├── layout.php              # Header/nav/footer wrapper
    │   └── pages/
    │       ├── admin.php           # User management (admin only)
    │       ├── home.php            # Dashboard
    │       ├── init.php            # Database initialization UI
    │       ├── login.php           # User login
    │       ├── profile.php         # Edit user info + password
    │       └── register.php        # User signup
    ├── controllers/                # Empty (no controllers needed yet)
    └── models/                     # Empty (queries in views)
```

## 🔐 Security Must-Knows

1. **SQL Injection Prevention**: Use `$db->query($sql, $params)` with parameter array
2. **XSS Prevention**: Wrap all user output with `Helper::escape()`
3. **Password Security**: Use `password_hash()` & `password_verify()` (bcrypt)
4. **Session Security**: Check `$_SESSION['user_id']` before DB operations
5. **Admin Gates**: Use `AuthMiddleware::requireAdmin()` at top of admin pages
6. **CSRF**: Not implemented - add if handling form submissions from untrusted sources

## 🧪 Testing & Debugging

### Common Commands
```bash
docker compose logs -f web              # Stream PHP/Apache logs
docker compose exec web bash            # Enter container shell
docker compose exec db mysql -u member_user -pmember_pass123 member_system -e "SELECT * FROM users;"
```

### Test Accounts
- Admin: `admin` / `admin123456`
- User: `user1` / `user123456` (also user2-5)

### Manual Testing Workflow
1. `docker compose up --build`
2. Visit http://localhost:8080
3. Check database initialized: `SELECT COUNT(*) FROM users` → should be 6
4. Login with test accounts (see Test Accounts above)

### Login Error Messages
Login failures show specific errors stored in `$_SESSION['login_error']`:
- "用户名不存在" - Username not found
- "密码错误" - Wrong password
- "账户已被禁用，请联系管理员" - Account disabled
- "用户名和密码不能为空" - Empty credentials

## 📝 Common Tasks

### Add a New Page
1. Create `app/src/views/pages/newpage.php`
2. Check auth at top: `AuthMiddleware::requireLogin()` or similar
3. Query DB: `$db = Database::getInstance()`
4. Output HTML with `Helper::escape()` on user data
5. Access via `http://localhost:8080/?page=newpage`

### Add a New Database Table
1. Add CREATE TABLE to `DBInitializer.php` in `createTables()` method
2. Drop and recreate the database to re-run initialization
3. Restart container: `docker compose restart web`

### Add Form Validation
1. Use existing helpers: `Helper::validate*()` functions
2. If new type needed, add method to `Helper.php`
3. Return validation messages to view in error variable

## ⚠️ Gotchas & Quirks

- **No controllers**: Business logic runs directly in views (trade-off for simplicity)
- **No ORM**: Direct PDO queries (faster, but requires manual parameter binding)
- **No environment file**: Config in `docker-compose.yml`, not .env
- **Session per browser**: No token-based auth, relies on browser cookies
- **MariaDB client**: Not MySQL client (use `mariadb-client` in apt-get)
- **Apache Rewrite**: Enabled in Dockerfile + vhost.conf for clean URLs
- **Tailwind via CDN**: No build process, classes evaluated in browser

## 📚 Reference

- PHP Docs: PDO, password_hash, htmlspecialchars
- Tailwind: https://tailwindcss.com (utility classes inline in HTML)
- MySQL: DATETIME fields use `NOW()` function, indexed on user lookup columns
- Docker: Service names resolve automatically on internal network (db:3306)
