# 📦 Installation Guide

**Padi REST API Framework v1.0.0**

---

## Requirements

### Server Requirements

- **PHP 8.1+**
- **Composer** (Latest version recommended)
- **Web Server** (Apache, NGINX, or PHP Built-in Server for development)

### Required PHP Extensions

- `pdo` & `pdo_mysql`: Core database access (or `pdo_pgsql`/`pdo_sqlite`)
- `mbstring`: For UTF-8 string manipulation
- `openssl`: For JWT encryption and data security
- `zlib`: For response compression (Gzip)
- `json`: For API data parsing and encoding
- `tokenizer`: Internal PHP requirement
- `fileinfo`: Optional, for MIME-type file upload validation
- `bcmath`: Optional, for high-precision math calculations

### Database Requirements

- **MySQL 5.7+** / **MariaDB 10.3+**
- **PostgreSQL 12+** (Optional)
- **SQLite 3** (Optional)

---

## Installation Steps

### Step 1: Install via Composer

You can create a new project using our Composer template:

```bash
composer create-project wibiesana/padi-rest-api my-app
cd my-app
```

This command will:

1. Download the latest version of the framework.
2. Automatically install all dependencies (including core framework).
3. Set up the directory structure.

### Step 2: Environment Setup

```bash
# Copy environment file (if not already created by composer)
cp .env.example .env

# On Windows
copy .env.example .env
```

### Step 4: Generate JWT Secret

```bash
# Generate 64-character random secret
php -r "echo bin2hex(random_bytes(32));"

# Example output:
# 9a6d4f7ebe57a4ebd702e6108f4e5bd1722fa2812ae4b9ae696ce68739e06b18b
```

**Update `.env` file:**

```env
JWT_SECRET=9a6d4f7ebe57a4ebd702e6108f4e5bd1722fa2812ae4b9ae696ce68739e06b18b
```

### Step 5: Create Database

```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE rest_api_db;
exit;
```

### Step 6: Import Schema

```bash
# Import database schema
mysql -u root -p rest_api_db < database/schema.sql
```

### Step 7: Create Storage Directories

```bash
# Create cache directories
mkdir -p storage/cache
mkdir -p storage/cache/ratelimit

# Set permissions (Linux/Mac)
chmod -R 755 storage

# On Windows, no need to set permissions
```

### Step 8: Start Development Server

```bash
# Start PHP built-in server
php -S localhost:8085 -t public

# Server running at http://localhost:8085
```

---

## Verify Installation

### Test Health Check

```bash
curl http://localhost:8085/

# Expected response:
{
  "success": true,
  "message": "Padi REST API is running",
  "version": "1.0.0"
}
```

### Check PHP Extensions

```bash
php -m | grep -E "pdo|mbstring|openssl|json"
```

---

## Alternative: Automated Setup

Use the automated setup script for faster installation:

```bash
# Run automated setup
php scripts/init.php

# Or on Windows
init_app.bat
```

See [INIT_APP_GUIDE.md](INIT_APP_GUIDE.md) for detailed automated setup instructions.

---

## Next Steps

1. **Configure Environment** - See [CONFIGURATION.md](CONFIGURATION.md)
2. **First Steps** - See [FIRST_STEPS.md](FIRST_STEPS.md)
3. **Database Setup** - See [../02-core-concepts/DATABASE_SETUP.md](../02-core-concepts/DATABASE_SETUP.md)

---

## Troubleshooting

If you encounter issues during installation:

- Check [INIT_APP_TROUBLESHOOTING.md](INIT_APP_TROUBLESHOOTING.md)
- Verify PHP version: `php -v`
- Check PHP extensions: `php -m`
- Verify database connection in `.env`

---

**Next:** [Configuration Guide →](CONFIGURATION.md)
