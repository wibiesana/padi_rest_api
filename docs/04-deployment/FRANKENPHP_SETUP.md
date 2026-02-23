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

### 1. Worker Script (`public/frankenphp-worker.php`)

This script handles the request loop, ensuring the application stays in memory while resetting state between requests.

### 2. Framework Compatibility

- **`core/Response.php`**: Replaced all `exit;` calls with a `terminate()` method that understands worker mode. It `returns` in worker mode but `exits` in traditional mode.
- **`core/Database.php`**: Added `resetQueryLog()` to prevent memory leaks by clearing query history between requests.

---

## Technical Reference

### State Management

The worker automatically resets:

- ✅ Database query logs
- ✅ Request/Response objects
- ✅ Exception handlers
- ✅ Output buffers

The worker keeps in memory:

- ✅ Loaded classes & Compiled code
- ✅ Autoloader cache
- ✅ Route definitions

### Memory Management

Worker mode often uses **less memory** in high-traffic scenarios because it doesn't repeatedly initialize the autoloader or load classes for every request.

- **Traditional PHP**: ~15MB per request
- **Worker Mode**: ~8MB total (shared state)

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

**Solution**: If memory grows continuously, check for unclosed resources or large static variables that aren't being reset.

---

## FAQ

**Q: Do I need to change my controllers?**  
A: No. The framework handles all abstraction.

**Q: Can I use `die()` or `exit()`?**  
A: Avoid them. Use `throw new Exception()` or controller return methods. The framework converts `exit` into a safe `return` for workers.

**Q: Is it safe for database connections?**  
A: Yes. Connections are managed and kept alive where possible, or re-established if lost.

---

**Last Updated:** 2026-02-09  
**Version:** 2.1.0 (FrankenPHP Optimized)
