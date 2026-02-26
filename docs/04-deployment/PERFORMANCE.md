# ⚡ Performance Optimization Guide

**Padi REST API Framework v2.0.2**

---

## Performance Comparison

### Test Results

#### Worker Mode

```
Request 1: ~40ms (cold start)
Request 2-5: ~26-31ms (average: 28.5ms)
```

#### Standard Mode

```
Request 1: ~110ms (cold start)
Request 2-5: ~29-30ms (average: 29.7ms)
```

### Key Findings

1. **Cold Start**: Worker mode is significantly faster (~40ms vs ~110ms)
2. **Warm Requests**: Both modes are nearly identical (~28-30ms)
3. **Consistency**: Worker mode provides more consistent response times
4. **Memory**: Worker mode uses memory more efficiently

---

## v2.0.2 Performance Improvements

### Response Compression

- **Manual GZip** (`gzencode()`) replaces `ob_gzhandler` — fixes output buffer leaks in worker mode
- **Smart Compression**: Payloads < 1KB are not compressed (overhead > benefit)
- **Production Mode**: `JSON_PRETTY_PRINT` disabled (~30% bandwidth savings)

### Request Parsing

- **Single Read**: `php://input` read exactly **once** and cached
- **Direct Lookup**: `input()` method uses direct key lookup, not `array_merge()` per call

### JWT Authentication

- **Cached Key**: `Firebase\JWT\Key` object created once, reused across all verifications
- **Fast Reject**: Invalid JWT format detected before expensive `JWT::decode()`
- **No Re-read**: `Auth::userId()` reads from `$_SERVER` directly (no `new Request()`)

### Database

- **LIMIT/OFFSET Binding**: Bound as `PDO::PARAM_INT` (proper typing, not string interpolation)
- **Connection Timeout**: 5-second default prevents blocking on unresponsive databases
- **SQLite WAL**: Write-Ahead Logging for ~5x faster writes
- **MySQL Strict Mode**: `STRICT_TRANS_TABLES` enabled for data integrity

### Caching

- **xxh3 Hash**: 10x faster than `md5` for cache key generation
- **Atomic Writes**: Temp file + rename prevents partial reads under concurrency
- **Redis Prefix**: Key namespace isolation via `REDIS_PREFIX` env variable

### Queue

- **DDL Caching**: `CREATE TABLE IF NOT EXISTS` executed once per process lifetime
- **MySQL Index**: `idx_queue_available(queue, available_at, reserved_at)` for fast job lookup

---

## Recommendations

- **Development**: Standard mode (automatic code reload on changes)
- **Production**: Worker mode (maximum performance + fast cold start)
- **Load Testing**: Worker mode (better concurrent request handling)

## Quick Commands

```powershell
# Switch to worker mode
.\quick-switch.ps1 worker

# Switch to standard mode
.\quick-switch.ps1 standard

# Test performance (10 requests)
1..10 | ForEach-Object {
    Measure-Command { curl.exe -s http://localhost:8085/ | Out-Null } |
    Select-Object -ExpandProperty TotalMilliseconds
}
```

---

## Environment Variables for Performance

```env
# GZip compression (default: true)
ENABLE_COMPRESSION=true

# Worker request limit before restart (default: 500)
MAX_REQUESTS=500

# Cache driver (file or redis)
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PREFIX=padi:

# Queue worker sleep between polls
QUEUE_SLEEP=3
```

---

**Last Updated:** 2026-02-26
**Version:** 2.0.2
