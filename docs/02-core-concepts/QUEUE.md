# 🚀 Background Jobs & Queue

**Padi REST API Framework v2.0.2**

The Queue system allows you to offload time-consuming tasks (like sending emails, generating reports, or calling slow third-party APIs) to the background.

---

## 📋 Table of Contents

- [Overview](#overview)
- [Defining a Job](#defining-a-job)
- [Pushing onto Queue](#pushing-onto-queue)
- [Running the Worker](#running-the-worker)
- [Configuration](#configuration)
- [Best Practices](#best-practices)

---

## 📖 Overview

By default, the framework uses a **Database-backed Queue**. This means jobs are stored in a `jobs` table and processed sequentially by a background worker script.

---

## 🛠️ Defining a Job

A Job is a simple PHP class located in the `app/Jobs` directory. It must implement a `handle(array $data)` method.

```php
namespace App\Jobs;

use Wibiesana\Padi\Core\Logger;

class ProcessImageJob
{
    /**
     * Handle the job processing.
     *
     * @param array $data Data passed when the job was pushed.
     */
    public function handle(array $data): void
    {
        $imageUrl = $data['url'];
        $size = $data['size'];

        Logger::info("Processing image: {$imageUrl} to size {$size}");

        // ... perform heavy logic here ...
    }
}
```

---

## 📍 Pushing onto Queue

You can push a job from anywhere in your application (typically from a Controller).

### 1. Simple Push

```php
use Wibiesana\Padi\Core\Queue;
use App\Jobs\ProcessImageJob;

Queue::push(ProcessImageJob::class, [
    'url' => 'https://example.com/image.jpg',
    'size' => 'thumb'
]);
```

### 2. Delayed Job

If you want the job to run after a certain delay (in seconds):

```php
// Run after 5 minutes (300 seconds)
Queue::push(ProcessImageJob::class, ['id' => 123], 'default', 300);
```

---

## 🏃 Running the Worker

To process the queued jobs, you must run the worker script via CLI.

```bash
php scripts/queue-worker.php
```

In production, it is recommended to use a process manager like **Supervisor** to ensure the worker keeps running.

---

## ⚙️ Configuration

You can configure queue behavior in your `.env` file.

```env
# Maximum number of retries before a job is considered failed and deleted
QUEUE_MAX_ATTEMPTS=3

# Default queue name
QUEUE_NAME=default

# Seconds to sleep between job polls (default: 3)
QUEUE_SLEEP=3
```

> **v2.0.2 Improvements**: Table initialization is cached (no repeated `CREATE TABLE IF NOT EXISTS` per push), multi-DB DDL support (MySQL, PostgreSQL, SQLite), and transaction rollback safety in error handler.

---

## 💡 Best Practices

1.  **Keep it Stateless**: High-volume jobs should be independent and not rely on global session state.
2.  **Retry logic**: The framework automatically retries failed jobs up to `QUEUE_MAX_ATTEMPTS`. Make sure your jobs are **idempotent** (safe to run multiple times).
3.  **Atomic Data**: Pass only the necessary data (like an ID) to the job, and fetch the latest model state inside the `handle` method.
4.  **Logging**: Use `Core\Logger` inside your jobs to track processing progress and debug failures.

---

**Last Updated:** 2026-02-26  
**Version:** 2.0.2
