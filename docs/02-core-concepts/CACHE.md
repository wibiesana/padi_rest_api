# ⚡ Caching System

The Caching system provides a unified API for various caching backends, allowing you to speed up your application by storing expensive data in a fast storage.

---

## 📋 Table of Contents

- [Architecture](#architecture)
- [Drivers](#drivers)
- [Configuration](#configuration)
- [Basic Usage](#basic-usage)
- [The Remember Pattern](#the-remember-pattern)
- [Counters](#counters)
- [Bulk Operations](#bulk-operations)
- [Memory Management](#memory-management)
- [Clearing the Cache](#clearing-the-cache)
- [Worker Mode](#worker-mode)
- [Security](#security)
- [Best Practices](#best-practices)

---

## 🏗️ Architecture

```
Request → L1 (memory, ~0ms) → L2 (Redis ~1ms / File ~5ms) → Database (~20ms)
```

The cache uses a **two-tier architecture** (v2.0.6):

| Layer  | Storage             | Speed  | Scope                                                        |
| ------ | ------------------- | ------ | ------------------------------------------------------------ |
| **L1** | PHP in-memory array | ~0ms   | Per-worker process (persists across requests in worker mode) |
| **L2** | Redis or File       | ~1-5ms | Shared across all processes                                  |

- On `get()`: L1 is checked first. On L1 miss, L2 is queried and the result is promoted to L1.
- On `set()`: Both L1 and L2 are written simultaneously.
- L1 is bounded (default: 1000 entries) and automatically evicts oldest 25% when full.

---

## 🚗 Drivers

The framework supports two caching drivers:

1.  **File**: Stores cached items in the local filesystem (`storage/cache/`). This is the default and requires no additional setup. Files are distributed across 256 subdirectories for filesystem performance.
2.  **Redis**: Stores cached items in a Redis database. This is much faster and recommended for production. Includes automatic reconnection if the connection drops.

---

## ⚙️ Configuration

Configure your preferred driver in the `.env` file.

### File Driver (Default)

```env
CACHE_DRIVER=file
CACHE_L1_MAX=1000
```

### Redis Driver

```env
CACHE_DRIVER=redis
CACHE_L1_MAX=1000
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_USERNAME=
REDIS_PASSWORD=
REDIS_DATABASE=0
REDIS_PREFIX=padi:
```

| Variable         | Default     | Description                                          |
| ---------------- | ----------- | ---------------------------------------------------- |
| `CACHE_DRIVER`   | `file`      | Cache backend: `file` or `redis`                     |
| `CACHE_L1_MAX`   | `1000`      | Maximum entries in the in-memory L1 cache            |
| `REDIS_HOST`     | `127.0.0.1` | Redis server hostname                                |
| `REDIS_PORT`     | `6379`      | Redis server port                                    |
| `REDIS_USERNAME` | _(empty)_   | Redis 6+ ACL username (leave empty for default user) |
| `REDIS_PASSWORD` | _(empty)_   | Redis authentication password                        |
| `REDIS_DATABASE` | `0`         | Redis database number                                |
| `REDIS_PREFIX`   | `padi:`     | Key namespace prefix for isolation                   |

---

## 📍 Basic Usage

Use the `Wibiesana\Padi\Core\Cache` class to interact with the cache.

### 1. Storing Items

```php
use Wibiesana\Padi\Core\Cache;

// Store for 10 minutes (600 seconds)
Cache::set('user_profile_1', $userData, 600);

// Store forever (no expiry)
Cache::set('app_version', '2.0.6', 0);

// Store with default TTL (300 seconds / 5 minutes)
Cache::set('temp_data', $data);
```

### 2. Retrieving Items

```php
// Returns null on miss
$data = Cache::get('user_profile_1');

// Returns a custom default on miss
$data = Cache::get('user_profile_1', []);

// Distinguish "cached null" from "not in cache"
$sentinel = '__MISS__';
$value = Cache::get('key', $sentinel);
if ($value === $sentinel) {
    // Truly not in cache
}
```

### 3. Checking Existence

```php
if (Cache::has('user_profile_1')) {
    // Key exists and is not expired
}
```

> Uses Redis `EXISTS` command (no JSON decode) or L1 memory check as fast path.

### 4. Deleting Items

```php
Cache::delete('user_profile_1');
```

---

## 🧠 The "Remember" Pattern

The `remember` method is the most efficient way to use cache. It checks for a key. If it exists, it returns it. If not, it executes the callback, stores the result, and returns it.

```php
use Wibiesana\Padi\Core\Cache;

$stats = Cache::remember('dashboard_stats', 3600, function() {
    // This expensive logic only runs once per hour
    return [
        'total_revenue' => Order::sum('amount'),
        'new_users' => User::where(['status' => 'active'])->count()
    ];
});
```

> **Null-safe**: If the callback returns `null`, the `null` value IS cached. This prevents repeated callback execution for queries that legitimately return no results.

---

## 🔢 Counters

Atomic increment/decrement operations, ideal for rate limiting and counters.

```php
// Increment by 1
$newCount = Cache::increment('page_views');         // → 1

// Increment by custom step
$newCount = Cache::increment('page_views', 5);      // → 6

// Decrement
$newCount = Cache::decrement('stock_count');         // → current - 1
$newCount = Cache::decrement('stock_count', 3);      // → current - 3
```

> On Redis, these use native `INCRBY`/`DECRBY` which are **atomic** — safe for concurrent access.

### Example: Simple Rate Limiter

```php
$ip = $_SERVER['REMOTE_ADDR'];
$key = "rate:{$ip}";

$hits = Cache::increment($key);

// Set TTL on first hit
if ($hits === 1) {
    Cache::set($key, 1, 60); // 60 second window
}

if ($hits > 100) {
    Response::json(['error' => 'Too many requests'], 429);
}
```

---

## 📦 Bulk Operations

### Delete Multiple Keys

```php
// Single Redis DEL command (no N+1)
$deletedCount = Cache::deleteMany([
    'user:1:profile',
    'user:1:settings',
    'user:1:permissions',
]);
```

---

## 🧩 Memory Management

### L1 In-Memory Cache

The L1 cache is a bounded PHP array that persists across requests in FrankenPHP worker mode. It provides zero-cost lookups for frequently accessed keys.

```php
// Check current L1 size (for monitoring)
$count = Cache::getMemorySize();  // → e.g., 42

// Force clear L1 only (L2 stays intact)
// Useful when you know underlying data has changed
Cache::clearMemory();
```

The L1 cache automatically:

- Evicts the oldest 25% of entries when `CACHE_L1_MAX` is reached
- Checks TTL expiry on every `get()` call
- Promotes L2 hits to L1 for subsequent fast access

---

## 🧹 Clearing the Cache

### Clear Everything (L1 + L2)

```php
Cache::clear();
```

### Clean Up Expired Files Only (File Driver)

```php
$deletedCount = Cache::cleanup();
```

> Uses `filemtime()` as a fast pre-filter: recently modified files are skipped without reading, reducing I/O on large cache directories.

---

## ⚙️ Worker Mode

### FrankenPHP Integration

The Cache class is designed for long-running FrankenPHP worker processes:

| Behavior             | Detail                                                                                     |
| -------------------- | ------------------------------------------------------------------------------------------ |
| **L1 persistence**   | In-memory cache survives across worker request iterations — intentional for performance    |
| **Redis reconnect**  | If Redis connection drops, `ensureRedisConnection()` transparently reconnects              |
| **Redis fallback**   | If reconnect fails, automatically falls back to file driver                                |
| **Memory bound**     | L1 evicts oldest entries at `CACHE_L1_MAX` to prevent unbounded growth                     |
| **`reset()` method** | Called by `Application::cleanupRequest()` — intentionally no-op since cache should persist |

### Shared Hosting

The file driver works on shared hosting without any external dependencies:

- No Redis or Memcached required
- Subdirectory bucketing prevents performance issues with many files
- Atomic writes prevent corruption under concurrent PHP-FPM/Apache processes
- Automatic fallback from Redis if connection fails

---

## 🔒 Security

### JSON Serialization

The file cache driver uses **JSON encoding** instead of PHP `serialize()`/`unserialize()`. This prevents **PHP Object Injection** attacks where a crafted cache file could instantiate arbitrary PHP objects.

```
// File cache format (safe JSON)
{"key":"user_1","value":{"name":"John"},"expires":1735000000}
```

### Atomic Writes

Cache files are written atomically using a **temp file + rename** strategy. This prevents partial/corrupted reads when multiple processes write to the same cache key simultaneously.

### Key Hashing

Cache keys are hashed using `xxh3` (10x faster than `md5`, safe for non-cryptographic use). This also prevents directory traversal attacks via malicious key names.

### Subdirectory Bucketing

File cache uses the first 2 characters of the hash as a subdirectory (256 buckets). This prevents filesystem performance degradation with thousands of cache files.

### Directory Permissions

Cache directory is created with `0750` permissions (owner: rwx, group: rx, others: none).

---

## � Real-World Examples

### 1. Cache Dropdown / Reference Data

Data like roles, statuses, or port lists rarely change. Cache them to avoid hitting the database on every request.

```php
// extend/models/RoleModel.php
use Wibiesana\Padi\Core\Cache;

class RoleModel extends \Base\RoleModel
{
    /**
     * Get all roles (cached for 1 hour)
     */
    public function getAllCached(): array
    {
        return Cache::remember('roles:all', 3600, function () {
            return $this->all(['id', 'name', 'description']);
        });
    }
}
```

```php
// In any controller
$roles = (new RoleModel())->getAllCached();
return $this->raw($roles);
```

### 2. Cache User Profile by ID

Cache individual records that are frequently accessed.

```php
// extend/models/UserModel.php
use Wibiesana\Padi\Core\Cache;

class UserModel extends \Base\UserModel
{
    public function findCached(int $id): ?array
    {
        return Cache::remember("user:{$id}:profile", 600, function () use ($id) {
            return $this->find($id, ['id', 'name', 'email', 'role', 'avatar']);
        });
    }
}
```

### 3. Cache Dashboard Statistics

Expensive aggregate queries should be cached to keep dashboard responses fast.

```php
// extend/controllers/DashboardController.php
use Wibiesana\Padi\Core\Cache;
use Wibiesana\Padi\Core\Query;

class DashboardController extends \Base\DashboardController
{
    public function stats(): array
    {
        $stats = Cache::remember('dashboard:stats', 900, function () {
            return [
                'total_vessels'  => (new VesselModel())->count(),
                'total_crew'     => (new CrewModel())->count(),
                'active_voyages' => (new VoyageModel())->count(['status' => 'active']),
                'expiring_certs' => (new Query('vessel_certificates'))
                    ->where([['expiry_date', '<', date('Y-m-d', strtotime('+30 days'))]])
                    ->count(),
            ];
        });

        return $this->raw($stats);
    }
}
```

### 4. Cache Application Settings

Settings that apply globally and rarely change — cache forever, invalidate manually.

```php
// extend/models/SettingModel.php
use Wibiesana\Padi\Core\Cache;

class SettingModel extends \Base\SettingModel
{
    /**
     * Get all settings as key-value map (cached forever)
     */
    public function getAllSettings(): array
    {
        return Cache::remember('app:settings', 0, function () {
            $rows = $this->all(['key', 'value']);
            $map = [];
            foreach ($rows as $row) {
                $map[$row['key']] = $row['value'];
            }
            return $map;
        });
    }

    /**
     * Get a single setting value
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->getAllSettings();
        return $settings[$key] ?? $default;
    }
}
```

### 5. Invalidate Cache on Data Change

Always clear related cache keys when the underlying data is modified.

```php
// extend/controllers/UserController.php
use Wibiesana\Padi\Core\Cache;

class UserController extends \Base\UserController
{
    public function update(int $id): mixed
    {
        $data = $this->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email',
        ]);

        $model = new UserModel();
        $model->update($id, $data);

        // Invalidate this user's cached profile
        Cache::delete("user:{$id}:profile");

        return $this->raw($model->find($id));
    }

    public function delete(int $id): mixed
    {
        $model = new UserModel();
        $model->delete($id);

        // Clean up all cache keys related to this user
        Cache::deleteMany([
            "user:{$id}:profile",
            "user:{$id}:permissions",
            "user:{$id}:settings",
        ]);

        return $this->noContent();
    }
}
```

### 6. Invalidate Cache After Settings Change

```php
// extend/controllers/SettingController.php
use Wibiesana\Padi\Core\Cache;

class SettingController extends \Base\SettingController
{
    public function update(int $id): mixed
    {
        $data = $this->validate(['value' => 'required']);

        (new SettingModel())->update($id, $data);

        // Invalidate so next getAllSettings() re-reads from DB
        Cache::delete('app:settings');

        return $this->raw(['message' => 'Setting updated']);
    }
}
```

### 7. Cache External API Responses

When your backend calls an external API, cache the response to avoid rate limits and reduce latency.

```php
// extend/services/ExchangeRateService.php
use Wibiesana\Padi\Core\Cache;

class ExchangeRateService
{
    public static function getRate(string $from, string $to): ?float
    {
        $key = "exchange_rate:{$from}:{$to}";

        return Cache::remember($key, 3600, function () use ($from, $to) {
            $url = "https://api.example.com/rates?from={$from}&to={$to}";
            $response = file_get_contents($url);

            if ($response === false) {
                return null; // null IS cached — won't retry for 1 hour
            }

            $data = json_decode($response, true);
            return $data['rate'] ?? null;
        });
    }
}
```

### 8. Health Check Endpoint with Cache Info

Monitor cache status in production.

```php
// extend/controllers/HealthController.php
use Wibiesana\Padi\Core\Cache;
use Wibiesana\Padi\Core\DatabaseManager;

class HealthController extends Controller
{
    public function index(): array
    {
        return $this->raw([
            'status'     => 'ok',
            'timestamp'  => date('c'),
            'cache'      => [
                'l1_entries' => Cache::getMemorySize(),
                'driver'     => $_ENV['CACHE_DRIVER'] ?? 'file',
            ],
            'database'   => DatabaseManager::getStatus(),
        ]);
    }
}
```

---

## �💡 Best Practices

1.  **Cache Keys**: Use descriptive, namespaced keys. Include IDs for specific resources (e.g., `user:42:profile`, `post:slug:hello-world`).
2.  **JSON-Safe Data**: Ensure cached data is JSON-serializable. Simple arrays, strings, numbers, and booleans work perfectly.
3.  **Invalidation**: Always `delete()` a cached key when underlying data changes (e.g., after updating a user profile).
4.  **TTL**: Don't set TTL too high for data that changes frequently. 5-15 minutes is a good starting point.
5.  **Redis for Production**: Use Redis in production for better performance, atomic operations, and automatic TTL handling.
6.  **Use `remember()`**: Prefer `Cache::remember()` over manual get/set — it handles cache miss logic cleanly.
7.  **Bulk Deletes**: Use `deleteMany()` instead of looping `delete()` — especially important with Redis.
8.  **Monitor L1**: Use `getMemorySize()` in health check endpoints to monitor memory usage in worker mode.

---

## 📊 API Reference

| Method          | Signature                                                    | Description                            |
| --------------- | ------------------------------------------------------------ | -------------------------------------- |
| `get`           | `get(string $key, mixed $default = null): mixed`             | Get cached value or default            |
| `set`           | `set(string $key, mixed $value, ?int $ttl = null): bool`     | Store value (ttl=0 for forever)        |
| `has`           | `has(string $key): bool`                                     | Check if key exists and is not expired |
| `delete`        | `delete(string $key): bool`                                  | Remove a cache entry                   |
| `deleteMany`    | `deleteMany(array $keys): int`                               | Remove multiple entries at once        |
| `remember`      | `remember(string $key, int $ttl, callable $callback): mixed` | Get-or-set pattern (null-safe)         |
| `increment`     | `increment(string $key, int $step = 1): int\|false`          | Atomic counter increment               |
| `decrement`     | `decrement(string $key, int $step = 1): int\|false`          | Atomic counter decrement               |
| `clear`         | `clear(): bool`                                              | Clear all cache (L1 + L2)              |
| `cleanup`       | `cleanup(): int`                                             | Remove expired file cache entries      |
| `clearMemory`   | `clearMemory(): void`                                        | Clear L1 in-memory cache only          |
| `getMemorySize` | `getMemorySize(): int`                                       | Get current L1 entry count             |
| `reset`         | `reset(): void`                                              | Worker cleanup hook (no-op by design)  |

---
