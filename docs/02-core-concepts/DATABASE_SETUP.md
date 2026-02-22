# ✅ Multi-Database Support - Quick Start Guide

Multi-database system has been successfully implemented! 🎉

## 📦 Files Created

### Core Framework

1. **`Core\DatabaseManager`** - Manager for handling multiple database connections (padi-core).
2. **`Core\Database`** - Updated to support multi-database (padi-core).
3. **`Core\ActiveRecord`** - Updated with `$connection` property to specify database (padi-core).
4. **`Core\Controller`** - Added `json()` method for easier response handling (padi-core).

### Configuration

5. **`config/database.php`** - Config for MySQL, MariaDB, PostgreSQL, SQLite.
6. **`.env.example`** - Updated with environment variables for all databases.

### Models (Examples)

7. **`app/Models/Analytics.php`** - Model for PostgreSQL (analytics data).
8. **`app/Models/CacheEntry.php`** - Model for SQLite (caching).

### Controllers

9. **`app/Controllers/MultiDbExampleController.php`** - Controller with complete multi-database examples.

### Database Schemas

10. **`database/postgresql_schema.sql`** - PostgreSQL schema for analytics.
11. **`database/sqlite_schema.sql`** - SQLite schema for cache.

### Documentation

12. **`docs/MULTI_DATABASE.md`** - Complete documentation with examples.
13. **`DATABASE_SETUP.md`** - This file (setup guide).

### Routes

14. **`routes/api.php`** - Updated with routes for `/api/multi-db/*`.

---

## 🚀 How to Use

### 1. Update `.env` File

```bash
# Copy from .env.example
cp .env.example .env

# Edit .env and add:
DB_CONNECTION=mysql

# MySQL (default database)
DB_HOST=localhost
DB_DATABASE=rest_api_db
DB_USERNAME=root
DB_PASSWORD=

# PostgreSQL (optional - for analytics)
PGSQL_HOST=localhost
PGSQL_DATABASE=analytics_db
PGSQL_USERNAME=postgres
PGSQL_PASSWORD=

# SQLite (optional - for cache)
SQLITE_DATABASE=database/database.sqlite
```

### 2. Setup Database

#### MySQL (Main database - already exists)

```bash
# Use existing schema
mysql -u root -p rest_api_db < database/schema.sql
```

#### PostgreSQL (Optional - for analytics)

```bash
# Create database
createdb analytics_db

# Import schema
psql -U postgres -d analytics_db -f database/postgresql_schema.sql
```

#### SQLite (Optional - for cache)

```bash
# Create database file
mkdir -p database
sqlite3 database/database.sqlite < database/sqlite_schema.sql
```

### 3. Test API

```bash
# Start server
php -S localhost:8085 -t public

# Test database info
curl http://localhost:8085/multi-db/info

# Test dashboard (data from multiple databases)
curl http://localhost:8085/multi-db/dashboard

# Test cache (SQLite)
curl -X POST http://localhost:8085/multi-db/cache \
  -H "Content-Type: application/json" \
  -d '{"key":"test","value":"hello world","ttl":3600}'

curl http://localhost:8085/multi-db/cache/test
```

---

## 📝 Code Examples

### 1. Model with Specific Database

```php
<?php
namespace App\Models;
use Core\ActiveRecord;

class Product extends ActiveRecord
{
    protected string $table = 'products';
    // Use default MySQL connection (leave null)
    protected ?string $connection = null;
}

class Analytics extends ActiveRecord
{
    protected string $table = 'user_events';
    // Use PostgreSQL connection
    protected ?string $connection = 'pgsql';
}

class Cache extends ActiveRecord
{
    protected string $table = 'cache';
    // Use SQLite connection
    protected ?string $connection = 'sqlite';
}
```

### 2. Using Multiple Databases in Controller

```php
<?php
namespace App\Controllers;
use Core\Controller;
use App\Models\Product;
use App\Models\Analytics;
use App\Models\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        // Data from MySQL
        $productModel = new Product();
        $products = $productModel->all();

        // Data from PostgreSQL
        $analyticsModel = new Analytics();
        $events = $analyticsModel->getRecentEvents();

        // Data from SQLite
        $cacheModel = new Cache();
        $cachedData = $cacheModel->getCached('dashboard_data');

        return $this->json([
            'products' => $products,
            'analytics' => $events,
            'cached' => $cachedData
        ]);
    }
}
```

### 3. Transactions on Multiple Databases

```php
use Core\DatabaseManager;

try {
    // Start transactions
    DatabaseManager::beginTransaction('mysql');
    DatabaseManager::beginTransaction('pgsql');

    // Insert to MySQL
    $productModel = new Product();
    $productId = $productModel->create(['name' => 'New Product']);

    // Log to PostgreSQL
    $analyticsModel = new Analytics();
    $analyticsModel->create([
        'event' => 'product_created',
        'data' => json_encode(['product_id' => $productId])
    ]);

    // Commit both
    DatabaseManager::commit('mysql');
    DatabaseManager::commit('pgsql');

} catch (Exception $e) {
    // Rollback both
    DatabaseManager::rollback('mysql');
    DatabaseManager::rollback('pgsql');
}
```

---

## 🎯 Available API Endpoints

| Endpoint                       | Method | Description                           |
| ------------------------------ | ------ | ------------------------------------- |
| `/multi-db/info`               | GET    | Database connections info             |
| `/multi-db/dashboard`          | GET    | Dashboard with data from multiple DBs |
| `/multi-db/track-event`        | POST   | Track event (MySQL + PostgreSQL)      |
| `/multi-db/cache/{key}`        | GET    | Get cache from SQLite                 |
| `/multi-db/cache`              | POST   | Set cache to SQLite                   |
| `/multi-db/cache/expired`      | DELETE | Clear expired cache                   |
| `/multi-db/user/{id}/activity` | GET    | Get user activity from PostgreSQL     |
| `/multi-db/raw-query`          | GET    | Example raw queries to all databases  |

---

## 🔍 Supported Database Drivers

- ✅ **MySQL** - Default application database.
- ✅ **MariaDB** - Compatible with MySQL driver.
- ✅ **PostgreSQL** - For analytics and reporting.
- ✅ **SQLite** - For caching and lightweight data.

---

## 📖 Full Documentation

Read complete documentation at: **[docs/MULTI_DATABASE.md](docs/MULTI_DATABASE.md)**

It includes:

- Detailed configuration
- Usage examples
- Best practices
- Troubleshooting
- Real-world use cases

---

## ✨ Benefits of Multi-Database

1. **Separation of Concerns** - Separate transactional, analytics, and cache data.
2. **Performance** - Use the right database for the right use case.
3. **Scalability** - Scale databases independently.
4. **Flexibility** - Easily add/remove database connections.
5. **Testing** - Use SQLite in-memory for testing.

---

## 🎓 Next Steps

1. **Test** all created endpoints.
2. **Create** new models as needed.
3. **Configure** database connections in `.env`.
4. **Read** complete documentation in `docs/MULTI_DATABASE.md`.

---

**Happy Coding!** 🚀
