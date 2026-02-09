# 🗄️ Multi-Database Support Guide

This framework supports multiple types of databases simultaneously:

- ✅ **MySQL**
- ✅ **MariaDB**
- ✅ **PostgreSQL**
- ✅ **SQLite**

---

## 📋 Table of Contents

1. [Database Configuration](#database-configuration)
2. [Basic Usage](#basic-usage)
3. [Model with Specific Database](#model-with-specific-database)
4. [Multiple Databases in One Controller](#multiple-databases-in-one-controller)
5. [Direct Database Access](#direct-database-access)
6. [Transactions](#transactions)
7. [Real-World Examples](#real-world-examples)

---

## 🔧 Database Configuration

### 1. Update `.env` File

```bash
# Default database connection
DB_CONNECTION=mysql

# MySQL Configuration (Default)
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=rest_api_db
DB_USERNAME=root
DB_PASSWORD=

# PostgreSQL Configuration
PGSQL_HOST=localhost
PGSQL_PORT=5432
PGSQL_DATABASE=analytics_db
PGSQL_USERNAME=postgres
PGSQL_PASSWORD=secret

# SQLite Configuration
SQLITE_DATABASE=database/cache.sqlite
```

### 2. Configuration is in `app/Config/database.php`

This file is pre-configured to support all database drivers. You do not need to change it unless you want to add a new connection.

---

## 🚀 Basic Usage

### Default Database (MySQL)

By default, all models will use the database specified in `DB_CONNECTION`:

```php
<?php

namespace App\Models;

use Core\ActiveRecord;

class User extends ActiveRecord
{
    protected string $table = 'users';
    protected array $fillable = ['name', 'email', 'password'];
    protected array $hidden = ['password'];
}
```

Usage:

```php
$userModel = new User();
$users = $userModel->all();
```

---

## 🎯 Model with Specific Database

### Example: Model Using PostgreSQL

```php
<?php

namespace App\Models;

use Core\ActiveRecord;

class Analytics extends ActiveRecord
{
    protected string $table = 'analytics';
    protected ?string $connection = 'pgsql'; // ← Use PostgreSQL
    protected array $fillable = ['event', 'user_id', 'data'];
}
```

### Example: Model Using SQLite

```php
<?php

namespace App\Models;

use Core\ActiveRecord;

class CacheEntry extends ActiveRecord
{
    protected string $table = 'cache';
    protected ?string $connection = 'sqlite'; // ← Use SQLite
    protected array $fillable = ['key', 'value', 'expires_at'];
}
```

---

## 🔀 Multiple Databases in One Controller

```php
<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\User;        // MySQL
use App\Models\Analytics;   // PostgreSQL
use App\Models\CacheEntry;  // SQLite

class DashboardController extends Controller
{
    public function index()
    {
        // Data from MySQL
        $userModel = new User();
        $users = $userModel->all();

        // Data from PostgreSQL
        $analyticsModel = new Analytics();
        $analytics = $analyticsModel->where(['user_id' => 1]);

        // Data from SQLite
        $cacheModel = new CacheEntry();
        $cache = $cacheModel->find(1);

        return $this->json([
            'users' => $users,
            'analytics' => $analytics,
            'cache' => $cache
        ]);
    }
}
```

---

## 🔌 Direct Database Access

### Using DatabaseManager Directly

```php
use Core\DatabaseManager;

// Get default connection
$db = DatabaseManager::connection();

// Get specific connection
$mysql = DatabaseManager::connection('mysql');
$pgsql = DatabaseManager::connection('pgsql');
$sqlite = DatabaseManager::connection('sqlite');

// Execute query
$stmt = $pgsql->prepare("SELECT * FROM analytics WHERE user_id = ?");
$stmt->execute([1]);
$results = $stmt->fetchAll();
```

### Using Database Class (Backward Compatible)

```php
use Core\Database;

// Default connection
$db = Database::getInstance()->getConnection();

// Specific connection (NEW)
$pgsql = Database::connection('pgsql');
$sqlite = Database::connection('sqlite');
```

---

## 💼 Transactions

### Transaction on a Specific Database

```php
use Core\DatabaseManager;

try {
    // Start transaction on PostgreSQL
    DatabaseManager::beginTransaction('pgsql');

    // Insert to PostgreSQL
    $pgsql = DatabaseManager::connection('pgsql');
    $stmt = $pgsql->prepare("INSERT INTO analytics (event, data) VALUES (?, ?)");
    $stmt->execute(['login', json_encode(['ip' => '127.0.0.1'])]);

    // Commit
    DatabaseManager::commit('pgsql');

} catch (Exception $e) {
    DatabaseManager::rollback('pgsql');
    throw $e;
}
```

### Transaction on Multiple Databases

```php
use Core\DatabaseManager;

try {
    // Start transactions
    DatabaseManager::beginTransaction('mysql');
    DatabaseManager::beginTransaction('pgsql');

    // Insert to MySQL
    $mysql = DatabaseManager::connection('mysql');
    $stmt = $mysql->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
    $stmt->execute(['John', 'john@example.com']);
    $userId = $mysql->lastInsertId();

    // Insert to PostgreSQL
    $pgsql = DatabaseManager::connection('pgsql');
    $stmt = $pgsql->prepare("INSERT INTO analytics (user_id, event) VALUES (?, ?)");
    $stmt->execute([$userId, 'user_registered']);

    // Commit both
    DatabaseManager::commit('mysql');
    DatabaseManager::commit('pgsql');

} catch (Exception $e) {
    // Rollback both
    DatabaseManager::rollback('mysql');
    DatabaseManager::rollback('pgsql');
    throw $e;
}
```

---

## 💡 Real-World Examples

### Case 1: Primary Data in MySQL, Analytics in PostgreSQL

```php
<?php

namespace App\Controllers;

use Core\Controller;
use Core\DatabaseManager;
use App\Models\User;

class UserController extends Controller
{
    public function register()
    {
        $data = $this->request->all();

        try {
            // Start transactions
            DatabaseManager::beginTransaction('mysql');
            DatabaseManager::beginTransaction('pgsql');

            // Save user to MySQL
            $userModel = new User();
            $userId = $userModel->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_BCRYPT)
            ]);

            // Log to PostgreSQL Analytics
            $pgsql = DatabaseManager::connection('pgsql');
            $stmt = $pgsql->prepare(
                "INSERT INTO user_events (user_id, event, ip_address, created_at)
                 VALUES (?, ?, ?, NOW())"
            );
            $stmt->execute([
                $userId,
                'user_registered',
                $_SERVER['REMOTE_ADDR']
            ]);

            // Commit both
            DatabaseManager::commit('mysql');
            DatabaseManager::commit('pgsql');

            return $this->json([
                'success' => true,
                'user_id' => $userId
            ]);

        } catch (Exception $e) {
            DatabaseManager::rollback('mysql');
            DatabaseManager::rollback('pgsql');

            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
```

### Case 2: Cache in SQLite, Data in MySQL

```php
<?php

namespace App\Controllers;

use Core\Controller;
use Core\DatabaseManager;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        // Check cache first (SQLite)
        $sqlite = DatabaseManager::connection('sqlite');
        $stmt = $sqlite->prepare("SELECT value FROM cache WHERE key = ? AND expires_at > ?");
        $stmt->execute(['products_list', time()]);
        $cached = $stmt->fetch();

        if ($cached) {
            return $this->json(json_decode($cached['value'], true));
        }

        // Get from MySQL
        $productModel = new Product();
        $products = $productModel->all();

        // Save to cache
        $stmt = $sqlite->prepare(
            "INSERT OR REPLACE INTO cache (key, value, expires_at) VALUES (?, ?, ?)"
        );
        $stmt->execute([
            'products_list',
            json_encode($products),
            time() + 300 // 5 minutes
        ]);

        return $this->json($products);
    }
}
```

---

## 🛠️ Utility Methods

### Check Connection Availability

```php
use Core\DatabaseManager;

// Check if connection exists
if (DatabaseManager::hasConnection('pgsql')) {
    $pgsql = DatabaseManager::connection('pgsql');
    // ... use connection
}
```

### Get Active Connections

```php
use Core\DatabaseManager;

$connections = DatabaseManager::getConnections();
// Returns: ['mysql', 'pgsql'] (only connected ones)
```

### Add Connection at Runtime

```php
use Core\DatabaseManager;

DatabaseManager::addConnection('mongodb', [
    'driver' => 'mongodb',
    'host' => 'localhost',
    'port' => 27017,
    // ... more config
]);
```

---

## 🎨 Best Practices

1. **Set Connection in Model**
   - Set `protected ?string $connection = 'pgsql';` in the model.
   - Cleaner and more maintainable.

2. **Use Transactions for Critical Operations**
   - Always use transactions for multi-step operations.
   - Rollback if an error occurs.

3. **Cache Configuration**
   - SQLite is good for simple caching.
   - PostgreSQL is suitable for analytics and reporting.
   - MySQL/MariaDB for primary transactional data.

4. **Connection Pooling**
   - The framework automatically reuses established connections.
   - No need to worry about multiple connections.

5. **Search Operator Compatibility**
   - The framework automatically handles `ILIKE` for PostgreSQL to provide case-insensitive search by default.
   - For other drivers like MySQL or SQLite, it falls back to the standard `LIKE`.
   - You can bypass this behavior by using the `autoIlike(false)` method in Query Builder or passing `true` as the first argument to the model's `getLikeOperator(true)` method.

---

## 🐛 Troubleshooting

### Error: "Database connection 'xxx' not configured"

**Solution:** Ensure the connection exists in `app/Config/database.php` and environment variables are set in `.env`.

### Error: PDO Driver not found

**Solution:**

```bash
# Install PDO extensions
# For PostgreSQL
sudo apt-get install php-pgsql

# For SQLite
sudo apt-get install php-sqlite3
```

---

## 📚 Resources

- **Config File:** `app/Config/database.php`
- **DatabaseManager:** `core/DatabaseManager.php`
- **Database (Legacy):** `core/Database.php`
- **Model Base:** `core/ActiveRecord.php`

---

**Last Updated:** 2026-01-22  
**Version:** 1.0.0
