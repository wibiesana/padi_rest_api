# 🚀 Setup Methods - init.php vs init_app.bat

## ✨ **NEW! PHP CLI Setup Script (Recommended)**

We now provide **`init.php`** - a PHP CLI script that mimics Laravel Artisan!

### 🎯 **Why PHP CLI Instead of Batch File?**

| Feature             | init.php (PHP)                   | init_app.bat (Batch)      |
| ------------------- | -------------------------------- | ------------------------- |
| **Cross-Platform**  | ✅ Works on Windows, Linux, Mac  | ❌ Windows only           |
| **Stability**       | ✅ Very stable                   | ⚠️ Can crash unexpectedly |
| **Error Handling**  | ✅ Excellent                     | ⚠️ Limited                |
| **User Experience** | ✅ Colored output, clear prompts | ⚠️ Basic                  |
| **Dependencies**    | ✅ PHP only (already required)   | ⚠️ PowerShell required    |
| **Debugging**       | ✅ Easy with try-catch           | ⚠️ Difficult              |

---

## 🎨 Method 1: PHP CLI Script (Recommended)

### Usage:

```bash
php init.php
```

### Features:

✅ **Interactive wizard** with colored output  
✅ **Works everywhere** - Windows, Linux, macOS  
✅ **No dependencies** - Only PHP required (framework dependency)  
✅ **Robust error handling** - Try-catch blocks  
✅ **User-friendly** - Clear prompts and feedback  
✅ **Similar to Artisan** - Familiar to Laravel developers

### Screenshot:

```
╔════════════════════════════════════════════════════════════════╗
║             Padi REST API Framework - Setup Wizard             ║
║                        Version: 1.0.0                             ║
║                    Powered by PHP CLI                          ║
╚════════════════════════════════════════════════════════════════╝

ℹ [1/7] Checking environment file...
✓ .env file created from .env.example

[2/7] Select Database Driver
------------------------------------------------------------
  → 1. MySQL (Default)
    2. MariaDB
    3. PostgreSQL
    4. SQLite
------------------------------------------------------------
Enter your choice [1]:
```

### What It Does:

1. **Checks prerequisites**
   - Validates `.env.example` exists
   - Ensures PHP is available

2. **Creates/updates .env**
   - Prompts for overwrite if exists
   - Safe file operations

3. **Database configuration**
   - Interactive prompts for credentials
   - Validates choices
   - Updates `.env` automatically

4. **Tests database connection**
   - Validates credentials before proceeding
   - Shows detailed error messages if connection fails
   - Provides troubleshooting tips
   - Option to continue or abort setup

5. **JWT secret generation**
   - Secure random key (64 characters)
   - Automatic save to `.env`

6. **Migrations with error handling**
   - Base tables (users, password_resets) or full examples
   - Clear progress feedback
   - Detailed error messages if migration fails
   - Troubleshooting suggestions
   - Option to continue or abort on failure

7. **CRUD generation with validation**
   - All tables or selective generation
   - Driver-specific generation
   - Per-table error tracking
   - Success/failure summary
   - Protected tables auto-skipped (users, password_resets, migrations)

### Error Handling:

✅ **Database Connection Errors**

```
✗ Database connection failed!
✗ Error: SQLSTATE[HY000] [1045] Access denied for user...

⚠ Common issues:
  • Check database credentials in .env file
  • Ensure database server is running
  • Verify database exists (for MySQL/PostgreSQL)
  • Check if port is correct

Continue anyway? (y/n) [n]:
```

✅ **Migration Errors**

```
✗ Command failed with code 1: php scripts/migrate.php migrate
✗ Error details:
  Table 'users' already exists

⚠ Troubleshooting:
  • Ensure database connection is working
  • Check if migration files exist in database/migrations/
  • Review error messages above

Continue to next step? (y/n) [y]:
```

✅ **CRUD Generation Errors**

```
✗ CRUD generation failed!
⚠ Troubleshooting:
  • Ensure database tables exist (run migrations first)
  • Check if generate.php script exists
  • Review error messages above
```

---

## 💻 Method 2: Windows Batch File (Alternative)

### Usage:

```bash
init_app.bat
```

### When to Use:

- ⚠️ **Only if you prefer GUI wizards**
- ⚠️ **Windows users who prefer simpler interfaces**
- ⚠️ **Known issues:** Can crash on some systems

### Known Issues:

1. **PowerShell dependency**
   - Requires PowerShell execution policy
   - May fail silently

2. **Error handling**
   - Limited error messages
   - Hard to debug

3. **Not cross-platform**
   - Windows only
   - Won't work on Linux/Mac

### Our Recommendation:

**Use `php init.php` instead!**

---

## 📊 Comparison

### Startup Process:

**PHP CLI (`init.php`):**

```bash
$ php init.php
╔════════════════════════════════════════╗
║    Setup Wizard Started                ║
╚════════════════════════════════════════╝
ℹ Checking prerequisites...
✓ All checks passed
```

**Batch File (`init_app.bat`):**

```batch
C:\> init_app.bat
[1/7] Checking environment file...
```

### Error Messages:

**PHP CLI:**

```bash
✗ Setup failed: .env.example not found!
  Please ensure .env.example exists in the project root.
  Current directory: C:\xampp\htdocs\project
```

**Batch File:**

```batch
ERROR: Failed to copy .env.example
```

### User Experience:

**PHP CLI:**

- ✅ Colored output (green ✓, red ✗, yellow ⚠)
- ✅ Clear progress indicators
- ✅ Helpful error messages
- ✅ Default values shown
- ✅ Confirmation prompts

**Batch File:**

- ⚠️ Basic output
- ⚠️ Limited colors
- ⚠️ Generic errors
- ⚠️ Can crash unexpectedly

---

## 🛠️ Manual Setup (Fallback)

If both methods fail, you can setup manually:

### Step 1: Create .env

```bash
cp .env.example .env
# or
copy .env.example .env
```

### Step 2: Edit .env

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=rest_api_db
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=<run: php -r "echo bin2hex(random_bytes(32));">
```

---

## ✅ Recommended Workflow

### For All Users:

```bash
# 1. Install dependencies
composer install

# 2. Run PHP setup wizard (RECOMMENDED!)
php init.php

# 3. Start server
php -S localhost:8085 -t public
```

---

## 🔧 Troubleshooting

### init.php Issues:

**Problem:** "Could not open input file: init.php"

**Solution:**

```bash
# Ensure you're in project root
cd /path/to/mvc_rest_api
ls init.php  # Should exist

# Run with full path
php /full/path/to/init.php
```

---

### init_app.bat Issues:

**Problem:** Script closes immediately

**Solutions:**

1. **Use init.php instead!** (Recommended)
2. Run from command prompt (not double-click)
3. Check PowerShell execution policy
4. See `docs/INIT_APP_TROUBLESHOOTING.md`

---

## 📝 Summary

| Question            | Answer                                        |
| ------------------- | --------------------------------------------- |
| **Which to use?**   | `php init.php`                                |
| **Why PHP?**        | Cross-platform, stable, no extra dependencies |
| **Still use .bat?** | Only if you really want to (Windows only)     |
| **Manual setup?**   | Yes, always an option                         |
| **Best practice?**  | `php init.php` → fastest and most reliable    |

---

**Happy Setup! 🚀**
