# ⚡ Caching System

**Padi REST API Framework v1.0.0**

The Caching system provides a unified API for various caching backends, allowing you to speed up your application by storing expensive data in a fast storage.

---

## 📋 Table of Contents

- [Drivers](#drivers)
- [Configuration](#configuration)
- [Basic Usage](#basic-usage)
- [The Remember Pattern](#the-remember-pattern)
- [Clearing the Cache](#clearing-the-cache)
- [Best Practices](#best-practices)

---

## 🚗 Drivers

The framework supports two caching drivers:

1.  **File**: Stores cached items in the local filesystem (`storage/cache/`). This is the default and requires no additional setup.
2.  **Redis**: Stores cached items in a Redis database. This is much faster and recommended for production.

---

## ⚙️ Configuration

Configure your preferred driver in the `.env` file.

### File Driver (Default)

```env
CACHE_DRIVER=file
```

### Redis Driver

```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_DATABASE=0
```

---

## 📍 Basic Usage

Use the `Core\Cache` class to interact with the cache.

### 1. Storing Items

```php
use Core\Cache;

// Store for 10 minutes (600 seconds)
Cache::set('user_profile_1', $userData, 600);
```

### 2. Retrieving Items

```php
$data = Cache::get('user_profile_1');

if ($data === null) {
    // Cache miss
}
```

### 3. Checking Existence

```php
if (Cache::has('user_profile_1')) {
    // ...
}
```

### 4. Deleting Items

```php
Cache::delete('user_profile_1');
```

---

## 🧠 The "Remember" Pattern

The `remember` method is the most efficient way to use cache. It checks for a key. If it exists, it returns it. If not, it executes the callback, stores the result, and returns it.

```php
use Core\Cache;

$stats = Cache::remember('dashboard_stats', 3600, function() {
    // This expensive logic only runs once per hour
    return [
        'total_revenue' => Order::sum('amount'),
        'new_users' => User::where(['status' => 'active'])->count()
    ];
});
```

---

## 🧹 Clearing the Cache

You can clear all cached items:

```php
use Core\Cache;

Cache::clear();
```

Or via CLI (if implemented):

```bash
php scripts/cache-clear.php
```

---

## 💡 Best Practices

1.  **Cache Keys**: Use descriptive and unique keys. For specific resources, include the ID (e.g., `post:slug:hello-world`).
2.  **Serializability**: Ensure the data you are caching is serializable. Simple arrays and objects are fine.
3.  **Invalidation**: Remember to `delete()` a cached key when the underlying data changes (e.g., update a user profile).
4.  **TTL (Time To Live)**: Don't set TTL too high for data that changes frequently. 5-15 minutes is usually a good starting point.

---

**Last Updated:** 2026-02-09  
**Version:** 1.0.0
