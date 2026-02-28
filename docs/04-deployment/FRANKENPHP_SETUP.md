# 🚀 FrankenPHP Worker Mode Setup & Implementation

Complete guide for using FrankenPHP with Padi REST API Framework to achieve **3-10x performance improvements** in production.

## Table of Contents

- [Overview](#overview)
- [Performance Gains](#performance-gains)
- [Installation](#installation)
- [How to Run](#how-to-run)
- [Implementation Details](#implementation-details)
- [Technical Reference](#technical-reference)
- [Configuration](#configuration)
- [Docker Deployment](#docker-deployment)
- [Troubleshooting](#troubleshooting)
- [FAQ](#faq)

---

## Overview

**FrankenPHP** is a modern PHP application server built on top of the Caddy web server. It keeps your application in memory between requests (Worker Mode), eliminating the overhead of reloading the framework for every request.

### Key Benefits

- ⚡ **Ultra-fast**: Up to 10x faster than traditional PHP environments.
- 🔄 **Worker Mode**: Padi REST API stays loaded in memory.
- 🛡️ **Secure**: Built-in HTTPS with automatic certificates.
- 📦 **Simple**: Single binary, easy Docker integration.

---

## Performance Gains

### Benchmark Results (1000 requests)

| Server                | Time     | Req/sec    | Improvement |
| --------------------- | -------- | ---------- | ----------- |
| PHP Built-in          | 20.5s    | 48.78      | Baseline    |
| Apache + PHP-FPM      | 10.2s    | 98.04      | 2x          |
| Nginx + PHP-FPM       | 8.5s     | 117.65     | 2.4x        |
| **FrankenPHP Worker** | **2.1s** | **476.19** | **9.7x**    |

**Real-World Impact**: For an API handling **10,000 requests/hour**, FrankenPHP completes the tasks in **21 minutes** compared to **3.4 hours** with the built-in server.

---

## Installation

### Windows

1. Download from [FrankenPHP Releases](https://github.com/dunglas/frankenphp/releases).
2. Extract `frankenphp.exe` to your project root.

### Linux/Mac

```bash
# Direct install
curl -fsSL https://frankenphp.dev/install.sh | sh

# Or using Homebrew (Mac)
brew install frankenphp
```

---

## How to Run

### 1. Development Mode (No Worker)

Ideal for quick debugging and hot reloading.

```bash
frankenphp php-server -r public/
```

### 2. Worker Mode (Production - Recommended)

Uses the included `Caddyfile` to enable full performance.

```bash
# Windows
.\frankenphp.exe run

# Linux/Mac
frankenphp run
```

---

## Implementation Details

Padi REST API is **100% compatible** with worker mode out of the box. Key components involved:

### 1. Application Class (`Application.php`)

The `Application::run()` method handles the FrankenPHP worker loop with automatic per-request cleanup:

- **`cleanupRequest()`** — Flushes output buffers, clears `$_GET`, `$_POST`, `$_FILES`, `$_COOKIE` between iterations
- **`gc_collect_cycles()`** — Called before graceful restart to free circular references
- **`MAX_REQUESTS`** — Configurable limit (default: 500) before worker restarts

### 2. Framework Compatibility (v2.0.3)

- **`Response.php`**: Uses `TerminateException` instead of `exit()`. GZip uses manual `gzencode()` (not `ob_gzhandler` which leaks buffers between iterations).
- **`Database.php`**: `resetQueryCount()` clears query logs per request. `resetInstance()` clears singleton when connections are recycled (v2.0.3).
- **`DatabaseManager.php`**: `clearErrors()` resets error history per request. Error history capped at 50 entries (v2.0.3).
- **`ActiveRecord.php`**: `clearColumnsCache()` releases column metadata during graceful restart (v2.0.3).
- **`Auth.php`**: `userId()` reads from `$_SERVER` directly (does not create `new Request()` which would re-read consumed `php://input`).
- **`Application.php`**: Health-checks active DB connections with `SELECT 1` and **forces immediate reconnect** if stale (v2.0.3 fix).

---

## Technical Reference

### State Management (v2.0.3)

The worker automatically resets **per request**:

- ✅ Database query logs & error history
- ✅ Database singleton instance (prevents stale PDO references)
- ✅ Request/Response objects
- ✅ Output buffers (flushed via `ob_end_clean()`)
- ✅ Superglobals (`$_GET`, `$_POST`, `$_FILES`, `$_COOKIE`)

The worker keeps in memory **across requests**:

- ✅ Loaded classes & compiled code
- ✅ Autoloader cache
- ✅ Route definitions (compiled regex)
- ✅ Database connections (health-checked + auto-reconnected)
- ✅ Column metadata cache (cleared on graceful restart)
- ✅ Redis connections (Cache driver)
- ✅ JWT Key object (Auth)
- ✅ Logger instance

### Memory Management

Worker mode often uses **less memory** in high-traffic scenarios because it doesn't repeatedly initialize the autoloader or load classes for every request.

- **Traditional PHP**: ~15MB per request
- **Worker Mode**: ~8MB total (shared state)
- **Graceful Restart**: After `MAX_REQUESTS` iterations, column cache is cleared, GC runs, then the worker exits

---

## Configuration

### Caddyfile (Local Development)

The included `Caddyfile` is pre-configured for local testing:

```caddyfile
:8085 {
    root * public
    php_server {
        worker public/frankenphp-worker.php
    }
    file_server
}
```

### Production (with HTTPS)

Update your domain and email for automatic SSL:

```caddyfile
api.yourdomain.com {
    root * public
    php_server {
        worker public/frankenphp-worker.php
    }
    file_server
    header {
        Strict-Transport-Security "max-age=31536000;"
        X-Content-Type-Options "nosniff"
    }
}
```

---

## Docker Deployment

### docker-compose.yml

```yaml
services:
  api:
    image: dunglas/frankenphp
    ports:
      - "8085:8085"
    volumes:
      - .:/app
    environment:
      - APP_ENV=production
    command: ["frankenphp", "run", "--config", "/app/Caddyfile"]
```

---

## Troubleshooting

### Issue: "frankenphp: command not found"

**Solution**: Ensure the binary is in your PATH or run with `./frankenphp`.

### Issue: Changes not reflected

**Solution**: In worker mode, code is kept in memory. You **must restart** FrankenPHP to see code changes.

### Issue: Memory Leaks

**Solution**: As of v2.0.2, the framework automatically:

- Flushes output buffers between requests (`cleanupRequest()`)
- Clears superglobals between requests
- Runs `gc_collect_cycles()` before worker restart
- Restarts after `MAX_REQUESTS` iterations (default: 500)

If leaks persist, check for large static variables in your **extend/** code.

### Issue: "MySQL server has gone away"

**Solution (v2.0.3)**: The framework health-checks DB connections with `SELECT 1` before each request. If a connection is stale, it's automatically disconnected **and immediately reconnected**. The `Database` singleton is also reset to prevent stale PDO references.

> **v2.0.3 Fix**: Previously, stale connections were only disconnected but not reconnected, which could cause the current request to fail.

### Issue: Auth returns null in worker mode

**Solution**: As of v2.0.2, `Auth::userId()` reads from `$_SERVER['HTTP_AUTHORIZATION']` directly instead of creating a new `Request()` (which would re-read the already-consumed `php://input` stream).

---

## Configuration

### Environment Variables

```env
# Maximum requests before worker restart (prevents memory buildup)
MAX_REQUESTS=500

# Enable GZip compression (uses gzencode, not ob_gzhandler)
ENABLE_COMPRESSION=true
```

---

## FAQ

**Q: Do I need to change my controllers?**  
A: No. The framework handles all abstraction.

**Q: Can I use `die()` or `exit()`?**  
A: Avoid them. Use `throw new Exception()` or controller return methods. The framework uses `TerminateException` for safe control flow in workers.

**Q: Is it safe for database connections?**  
A: Yes. Connections are health-checked per request and auto-reconnected if stale.

**Q: What about output buffers?**  
A: The framework flushes all output buffers between requests via `cleanupRequest()`. GZip uses `gzencode()` (not `ob_gzhandler`) to avoid buffer leaks.

---

## 🌐 Shared Hosting Notes (v2.0.3)

For shared hosting deployments:

- **Connection Limits**: Configure `max_connections` in `config/database.php` (default: 10). The framework throws an exception before exhausting the limit.
- **Session Timeout**: Configure `wait_timeout` in `config/database.php` to prevent premature disconnection (default: 28800s, some hosts set as low as 60s).
- **Batch Insert Chunking**: `batchInsert()` auto-chunks to 500 rows to respect `max_allowed_packet`.
- **Connection Monitoring**: Use `DatabaseManager::getStatus()` for health check endpoints.

```php
// config/database.php
return [
    'default' => 'mysql',
    'max_connections' => 10,  // Shared hosting protection
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => Env::get('DB_HOST', 'localhost'),
            'database' => Env::get('DB_DATABASE'),
            'username' => Env::get('DB_USERNAME'),
            'password' => Env::get('DB_PASSWORD'),
            'wait_timeout' => 28800,  // Session timeout in seconds
        ],
    ],
];
```

---

**Last Updated:** 2026-02-28  
**Version:** 2.0.3
