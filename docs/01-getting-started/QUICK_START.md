# 🚀 Quick Start - Run This!

## 📝 Prerequisites

- **PHP 8.1+**
- **Extensions:** `pdo`, `mbstring`, `openssl`, `zlib`, `json`
- **Database:** MySQL/MariaDB/PostgreSQL/SQLite

---

## Automated Setup

Run this command to create a new project:

```bash
composer create-project wibiesana/padi-rest-api my-app
cd my-app
```

Then run the setup script:

```bash
php scripts/init.php
```

Or on Windows:

```bash
init_app.bat
```

The script will guide you through:

1. ✅ **Setup .env file**
2. ✅ **Choose database** (MySQL/MariaDB/PostgreSQL/SQLite)
3. ✅ **Configure database** (host, port, username, password)
4. ✅ **Generate JWT Secret** (secure 64-char hex)
5. ✅ **Run migrations** (base only or with examples)
6. ✅ **Generate CRUD** (optional)

---

## What You Get

### Base Migration:

- ✅ **users** table - Enhanced with role, status, email verification, etc.

### Example Migrations (Optional):

- ✅ **posts** table - Blog posts with foreign key to users
- ✅ **tags** table - For categorization
- ✅ **post_tags** table - Many-to-Many relationship
- ✅ **comments** table - Nested comments support

---

## Database Support

| Database   | Port | Auto-Increment | Notes            |
| ---------- | ---- | -------------- | ---------------- |
| MySQL      | 3306 | AUTO_INCREMENT | Default, UTF8MB4 |
| MariaDB    | 3306 | AUTO_INCREMENT | MySQL compatible |
| PostgreSQL | 5432 | SERIAL         | Auto triggers    |
| SQLite     | -    | AUTOINCREMENT  | File-based       |

---

## Commands

### Migration

```bash
# Run all migrations
php scripts/migrate.php migrate

# Run specific tables
php scripts/migrate.php migrate --tables=users,posts

# Check status
php scripts/migrate.php status

# Rollback
php scripts/migrate.php rollback
```

### Generate CRUD

```bash
# List tables
php scripts/generate.php list

# Generate for one table
php scripts/generate.php crud users --write

# Generate all
php scripts/generate.php crud-all --write
```

### Start Server

```bash
php -S localhost:8085 -t public
```

Visit: `http://localhost:8085`

---

## Next Steps

1. Run `init_app.bat`
2. Follow the prompts
3. Start server
4. Test API endpoints

---

**Full Documentation:** [INIT_APP_GUIDE.md](INIT_APP_GUIDE.md)
