# 🚀 Init App - Setup Guide

## 📝 Prerequisites

Ensure your server meets the following requirements before starting the installation:

- **PHP 8.1+**
- **Extensions:** `pdo`, `pdo_mysql` (or pgsql/sqlite), `mbstring`, `openssl`, `zlib`, `json`, `tokenizer`
- **Composer** installed globally
- **Database:** MySQL, MariaDB, PostgreSQL, or SQLite

---

## Quick Start

Run this command to create a new project:

```bash
composer create-project wibiesana/padi-rest-api my-app
cd my-app
```

Then run the automated setup script:

```bash
init_app.bat
```

This script will guide you through the application setup process interactively.

---

## 🎯 What Does This Script Do?

### 1. **Environment Configuration** (.env)

- Copy `.env.example` to `.env`
- Or update the existing `.env`

### 2. **Database Selection**

Choose the database you want to use:

- **MySQL** (Default) - Port 3306
- **MariaDB** - Port 3306 (compatible with MySQL driver)
- **PostgreSQL** - Port 5432
- **SQLite** - File-based database

### 3. **Database Configuration**

Input database configuration:

- Host (default: localhost)
- Port (auto-detect based on driver)
- Database name (default: rest_api_db)
- Username (default: root for MySQL/MariaDB, postgres for PostgreSQL)
- Password

For SQLite:

- Path to database file (default: database/database.sqlite)

### 4. **JWT Secret Generation**

- Generate a secure JWT secret key (64 hex characters)
- Automatically saved to `.env`

### 5. **Database Migrations**

Migration options:

- **Option 1**: Migrate base tables only (users)
- **Option 2**: Migrate with examples (users, posts, comments, tags, post_tags)
- **Option 3**: Skip migrations

### 6. **CRUD Generation**

Options for generating CRUD:

- **Option 1**: Generate for all tables
- **Option 2**: Choose specific tables
- **Option 3**: Skip generation

---

## 📋 Table Structures

### Base Table: `users`

```sql
users
 - id (primary key)
 - name (varchar 100)
 - email (varchar 255, unique)
 - password (varchar 255)
 - phone (varchar 20, nullable)
 - avatar (text, nullable)
 - role (varchar 50, default: 'user')
 - status (varchar 20, default: 'active')
 - email_verified_at (timestamp, nullable)
 - remember_token (varchar 100, nullable)
 - last_login_at (timestamp, nullable)
 - created_at (timestamp)
 - updated_at (timestamp)
```

**Features:**

- Support for authentication
- Role-based access
- User status tracking
- Email verification
- Remember me token
- Last login tracking

### Example Tables (Optional)

#### `posts`

```sql
posts
 - id (primary key)
 - user_id (foreign key → users.id)
 - title (varchar 255)
 - slug (varchar 255, unique)
 - content (text)
 - excerpt (text)
 - featured_image (text)
 - status (varchar 20, default: 'draft')
 - published_at (timestamp, nullable)
 - views (integer, default: 0)
 - created_at (timestamp)
 - updated_at (timestamp)
```

#### `tags`

```sql
tags
 - id (primary key)
 - name (varchar 100, unique)
 - slug (varchar 100, unique)
 - description (text)
 - created_at (timestamp)
 - updated_at (timestamp)
```

#### `post_tags` (Many-to-Many)

```sql
post_tags
 - id (primary key)
 - post_id (foreign key → posts.id)
 - tag_id (foreign key → tags.id)
 - created_at (timestamp)
 - UNIQUE(post_id, tag_id)
```

**Relationship Demonstration:**

- One-to-Many: User has many Posts
- Many-to-Many: Post has many Tags through post_tags

#### `comments`

```sql
comments
 - id (primary key)
 - post_id (foreign key → posts.id)
 - user_id (foreign key → users.id)
 - parent_id (foreign key → comments.id, nullable)
 - content (text)
 - status (varchar 20, default: 'approved')
 - created_at (timestamp)
 - updated_at (timestamp)
```

**Features:**

- Nested comments (parent_id)
- Comment moderation (status)

---

## 🔧 Manual Setup (Alternative)

If you want to setup manually without the script:

### 1. Copy .env

```bash
copy .env.example .env
```

### 2. Edit .env

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=rest_api_db
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Generate JWT Secret

```bash
php -r "echo bin2hex(random_bytes(32));"
```

Paste the result to `JWT_SECRET` in `.env`

### 4. Run Migrations

```bash
# Base tables only
php scripts/migrate.php migrate --tables=users

# All tables
php scripts/migrate.php migrate

# Specific tables
php scripts/migrate.php migrate --tables=users,posts,tags
```

### 5. Generate CRUD (Optional)

```bash
# List tables
php scripts/generate.php list

# Generate for specific table
php scripts/generate.php crud users --write

# Generate for all tables
php scripts/generate.php crud-all --write
```

---

## 🗄️ Database-Specific Notes

### MySQL/MariaDB

- Default port: 3306
- Auto-increment primary keys
- Foreign key support with InnoDB
- UTF8MB4 charset for emoji support

### PostgreSQL

- Default port: 5432
- Serial/BIGSERIAL for auto-increment
- Triggers for auto-updating timestamps
- JSONB support for advanced features

### SQLite

- File-based database
- Lightweight, suitable for development
- `:memory:` for in-memory database (testing)
- Limited foreign key support (must be enabled)

---

## 📝 Migration Commands

```bash
# Run migrations
php scripts/migrate.php migrate

# Run specific tables
php scripts/migrate.php migrate --tables=users,posts

# Check migration status
php scripts/migrate.php status

# Rollback last batch
php scripts/migrate.php rollback

# Rollback multiple batches
php scripts/migrate.php rollback --step=3

# Create new migration
php scripts/migrate.php make create_products_table
```

---

## 🎯 Generator Commands

```bash
# List all tables
php scripts/generate.php list

# Generate CRUD for one table
php scripts/generate.php crud products --write

# Generate for all tables
php scripts/generate.php crud-all --write

# Preview without writing
php scripts/generate.php crud products
```

---

## 🚦 Start Server

```bash
# Development server
php -S localhost:8085 -t public

# Access API
http://localhost:8085

# Access documentation
http://localhost:8085/docs
```

---

## ✅ After Setup Checklist

- [ ] `.env` file configured
- [ ] Database created and accessible
- [ ] JWT_SECRET generated
- [ ] Migrations executed successfully
- [ ] CRUD generated (if needed)
- [ ] Server running
- [ ] Test endpoints working

---

## 🔐 Security Notes

**Production Checklist:**

- [ ] Change `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Use strong JWT_SECRET (minimum 64 characters)
- [ ] Configure CORS properly
- [ ] Use strong database password
- [ ] Enable HTTPS
- [ ] Review rate limiting settings

---

## 🆘 Troubleshooting

### Database Connection Failed

```
Error: SQLSTATE[HY000] [1045] Access denied
```

**Solution:** Check database credentials in `.env`

### Migration Error

```
Error: Table 'migrations' doesn't exist
```

**Solution:** Migrator will auto-create migrations table, ensure user has permissions

### JWT Error

```
Error: JWT secret must be at least 32 characters
```

**Solution:** Generate new JWT secret with the command in step 3

### Port Already in Use

```
Warning: Failed to listen on localhost:8085
```

**Solution:** Use another port:

```bash
php -S localhost:8080 -t public
```

---

## 📚 Additional Documentation

- **Multi-Database Guide:** [docs/MULTI_DATABASE.md](docs/MULTI_DATABASE.md)
- **Full Documentation:** [docs/README.md](docs/README.md)
- **Frontend Integration:** [docs/FRONTEND_INTEGRATION.md](docs/FRONTEND_INTEGRATION.md)
- **API Testing:** [API_TESTING.md](API_TESTING.md)

---

**Happy Coding! 🚀**

If you have any questions or issues, please check the documentation or create an issue.
