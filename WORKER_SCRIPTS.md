# Worker Scripts

This project now uses clearer naming for worker scripts to avoid confusion:

## 📁 File Structure

### 🔄 Queue Worker

- **File**: `scripts/queue-worker.php`
- **Purpose**: Background job processing (email queues, etc.)
- **Usage**: `php scripts/queue-worker.php [queue_name]`
- **When to use**: For processing background tasks

### 🚀 FrankenPHP Worker

- **File**: `public/frankenphp-worker.php`
- **Purpose**: High-performance HTTP request handling
- **Usage**: Automatically used by FrankenPHP in worker mode
- **When to use**: For production deployments with FrankenPHP

## 🎯 Quick Commands

### Start Queue Worker

```bash
# Default queue
php scripts/queue-worker.php

# Specific queue
php scripts/queue-worker.php email
```

### Start FrankenPHP Worker (Production)

```bash
# Using Caddyfile
frankenphp run --config Caddyfile.worker

# Direct command
frankenphp php-server --worker public/frankenphp-worker.php --listen :8085
```

## 🔄 Migration Notes

If you have scripts or documentation that reference the old files:

- `scripts/worker.php` → `scripts/queue-worker.php`
- `public/worker.php` → `public/frankenphp-worker.php`

All configuration files and documentation have been updated automatically.
