# CHANGE LOG

## v2.0.3 (2026-02-28)

### 🔴 Critical Bug Fix

- **Health Check: Connection Not Reconnected**:
  - Fixed a critical bug where `healthCheckConnections()` would disconnect a stale database connection but **not reconnect** it. This caused subsequent requests in worker mode to fail with "MySQL server has gone away" errors. The health check now forces an immediate reconnect after disconnecting a stale connection and resets the `Database` singleton to prevent stale PDO references.

### 🏗️ FrankenPHP Worker Mode Improvements

- **Database Singleton Reset**:
  - Added `Database::resetInstance()` method to clear the singleton when connections are recycled. Called automatically in `cleanupRequest()` and after health check reconnection to prevent stale PDO references persisting across worker iterations.
- **Column Cache Lifecycle**:
  - Added `ActiveRecord::clearColumnsCache()` to manage memory during worker lifetime. Called during graceful worker restart (`$count >= MAX_REQUESTS`) to release accumulated column metadata.
- **Query Builder State Safety**:
  - Added `Query::reset()` method to clear all query builder state for safe reuse in long-lived processes.
  - Fixed `Query::paginate()` to **restore** `limit` and `offset` state after execution, preventing state leakage when the query builder is reused.
- **Error History Cap**:
  - `DatabaseManager::logError()` now caps the error history array at 50 entries per-request to prevent unbounded memory growth if many errors occur within a single request cycle.

### 🌐 Shared Hosting Optimizations

- **MySQL/MariaDB Session Timeout**:
  - `createMySQLConnection()` now sets `SESSION wait_timeout` and `SESSION interactive_timeout` based on the `wait_timeout` config key (default: 28800s). This prevents premature connection closure on shared hosting environments that default to very low timeout values (60-300s).
- **Connection Limit Protection**:
  - Added max connection limit check in `DatabaseManager::connection()`. Throws `PDOException` when the configured `max_connections` limit (default: 10) is reached, preventing shared hosting connection exhaustion. Configurable via `config/database.php`.
- **Batch Insert Chunking**:
  - `ActiveRecord::batchInsert()` now accepts a `$chunkSize` parameter (default: 500) and automatically splits large datasets into smaller INSERT statements. This prevents exceeding the `max_allowed_packet` limit (typically 1MB-16MB on shared hosting).

### 🔍 Query Builder Enhancements (v2.0.3)

- **`whereRaw($expression, $params)`**: New method for complex WHERE conditions that require raw SQL (subqueries, `CASE WHEN`, etc.). Parameters are still safely bound via PDO.
- **`exists()` Optimization**: Rewritten to use `SELECT 1 LIMIT 1` instead of `one()` which fetched the entire row with all columns. Significantly reduces data transfer for existence checks.
- **Version**: Query Builder version constant updated to `2.0.3`.

### 🗃️ ActiveRecord Enhancements (v2.0.3)

- **`findOrFail($id)`**: New convenience method that throws a 404 exception if the record is not found, eliminating repetitive null-check boilerplate in controllers.
- **`count($conditions)`**: New dedicated count method for quick record counting with optional WHERE conditions, without needing the full Query Builder.
- **`upsert($data, $updateColumns)`**: New atomic INSERT ... ON DUPLICATE KEY UPDATE for MariaDB/MySQL. Useful for sync operations and bulk data imports.

### 📊 DatabaseManager Monitoring (v2.0.3)

- **`isConnected($name)`**: Check if a specific connection is active and responds to a `SELECT 1` ping. Returns boolean.
- **`getConnectionCount()`**: Returns the number of active database connections. Useful for monitoring connection usage on limited shared hosting.
- **`getStatus()`**: Returns a comprehensive status array with active connection count, per-connection health status (`healthy`/`stale`), and error count. Ideal for health check endpoints.

### 🐋 Docker & Infrastructure

- **Docker Compose Stack Decoupling**:
  - Renamed all containers, networks, and volumes across `docker-compose.yml`, `docker-compose.standard.yml`, `docker-compose.worker.yml`, and `docker-compose.nginx.yml` to be unique (prefixes: `padi_dev_`, `padi_std_`, `padi_wrk_`, `padi_ngx_`).
  - This allows all deployment modes to run simultaneously on the same host without naming conflicts.
- **Port Mapping Isolation**:
  - Assigned unique host ports for each environment: Development (8085), Standard (8086), Worker (8087), and Nginx (8088/8443).
- **Environment Fixes**:
  - Fixed duplicate `JWT_SECRET` key in `docker-compose.worker.yml`.
  - Standardized `REDIS_HOST` configuration across all compose files to point to their respective environment-specific Redis containers.
- **Route Management Refactor**:
  - Relocated `routes` directory to `app/Routes` for better structure within the application bundle. All core systems (Application, Generator) now point to `/app/Routes/api.php`.

---

## v2.0.2 (2026-02-26)

### 🔴 Critical Security Fixes

- **Cache: PHP Object Injection Prevention**:
  - Replaced `unserialize()` with `json_encode()`/`json_decode()` for file cache storage. Using `unserialize()` on untrusted data enables PHP Object Injection attacks that can lead to remote code execution.
- **Query Builder: SQL Injection via LIMIT/OFFSET**:
  - `LIMIT` and `OFFSET` values are now bound as `PDO::PARAM_INT` parameters instead of being directly interpolated into the SQL string. This prevents potential SQL injection through manipulated limit/offset values.
- **File Upload: Path Traversal Prevention**:
  - Added `sanitizePath()` method with null byte injection protection, directory traversal component removal (`..`), and `realpath()` verification on delete operations.
  - Added dangerous file extension blacklist (`.php`, `.phar`, `.exe`, `.sh`, etc.) to block remote code execution via uploads.
  - Added MIME type verification using `finfo` as defense-in-depth against file disguise attacks.
- **Response: Header Injection Prevention**:
  - Download filenames are now sanitized to prevent HTTP header injection via `\r\n` characters.
  - Redirect URLs are validated to prevent open redirect attacks.
- **Env: Operator Precedence Bug Fix**:
  - Fixed critical bug in `Env::get()` where the `?:` operator was used instead of explicit `false` check. `getenv()` returns `false` (not empty string) when a variable is not found, causing `?:` to also swallow legitimate empty string values.

### ⚡ Performance Optimizations

- **Response: GZip Compression Rewrite**:
  - Replaced `ob_start('ob_gzhandler')` with manual `gzencode()`. The `ob_gzhandler` approach creates output buffer leaks in FrankenPHP worker mode since buffers persist between request iterations.
  - `JSON_PRETTY_PRINT` is now only applied in development mode, saving ~30% bandwidth in production.
  - Compression is automatically skipped for small payloads (< 1KB) where the overhead outweighs the benefit.
- **Request: Single Input Read**:
  - `php://input` is now read exactly **once** and cached internally. Previously, `raw()` would re-read the input stream, which returns empty on the second read.
  - `input()` method now performs direct key lookup instead of creating a merged array on every call.
- **Auth: JWT Verification Optimization**:
  - Pre-creates `Firebase\JWT\Key` object once and caches it (eliminated per-verification instantiation).
  - Added quick JWT format validation (`substr_count('.') !== 2`) before expensive `JWT::decode()`.
  - `Auth::userId()` and `Auth::user()` no longer create a new `Request()` instance (which re-reads `php://input`). Now accepts optional `$request` parameter or reads directly from `$_SERVER`.
- **DatabaseManager: Connection Optimizations**:
  - MySQL/MariaDB: `STRICT_TRANS_TABLES` SQL mode enabled for data integrity.
  - MySQL/MariaDB: `MYSQL_ATTR_FOUND_ROWS` enabled for accurate affected-row counts.
  - SQLite: WAL journal mode, 20MB cache, and `NORMAL` synchronous mode for ~5x faster writes.
  - Default connection timeout set to 5 seconds to prevent hanging on unresponsive databases.
- **Query Builder: Proper PDO Type Binding**:
  - New `bindAndExecute()` method uses proper PDO parameter types: `PARAM_INT` for integers, `PARAM_BOOL` for booleans, `PARAM_NULL` for null values.
- **Cache: Faster Hashing & Atomic Writes**:
  - File cache keys now use `xxh3` hash (10x faster than `md5`, non-crypto use is safe for cache keys).
  - Atomic file writes via temp file + `rename()` prevent partial/corrupted reads under concurrent access.
- **Queue: Cached Table Init**:
  - `CREATE TABLE IF NOT EXISTS` is now cached with a static flag, preventing redundant DDL queries on every `push()` call.
  - Added MySQL index `idx_queue_available(queue, available_at, reserved_at)` for fast job lookup.
- **Router: Modern PHP Constructs**:
  - `isCollection()` now uses PHP 8.1+ `array_is_list()` (faster than manual key checking).
  - Response format routing uses `match` expression instead of `switch`.

### 🏗️ FrankenPHP Worker Mode Fixes

- **Application: Per-Request Cleanup**:
  - New `cleanupRequest()` method flushes all output buffers and clears superglobals (`$_GET`, `$_POST`, `$_FILES`, `$_COOKIE`) between worker iterations to prevent state bleed.
  - `gc_collect_cycles()` called before graceful worker restart to free circular references.
- **Response: Output Buffer Leak Fix**:
  - Replaced `ob_gzhandler` (which creates persistent output buffers across worker iterations) with explicit `gzencode()`.
- **Auth: Input Stream Fix**:
  - `Auth::userId()` no longer creates `new Request()` which would re-read the already-consumed `php://input` stream. Falls back to `$_SERVER['HTTP_AUTHORIZATION']` or `$_SERVER['REDIRECT_HTTP_AUTHORIZATION']` directly.

### 🛡️ Security Headers

- **New Default Headers** (set per request in `Application.php`):
  - `X-Frame-Options: DENY` — Prevents clickjacking.
  - `X-Content-Type-Options: nosniff` — Prevents MIME sniffing.
  - `X-XSS-Protection: 0` — Disabled (modern browsers use CSP instead; old value `1; mode=block` can introduce vulnerabilities).
  - `Referrer-Policy: strict-origin-when-cross-origin` — Controls referrer information leakage.
  - `Permissions-Policy: camera=(), microphone=(), geolocation=()` — Restricts browser feature access.
  - `Strict-Transport-Security: max-age=31536000; includeSubDomains` — HSTS (production HTTPS only).
  - `Access-Control-Max-Age: 86400` — Preflight cache for 24 hours (reduces OPTIONS requests).
  - `Vary: Origin` — Proper CORS response caching.

### 📦 New Features & Improvements

- **Validator**: Added `array`, `regex`, and `nullable` validation rules. String length checks now use `mb_strlen()` for Unicode support.
- **Logger**: Added `critical()` log level method.
- **File**: New `sanitizePath()` for path sanitization. Cryptographically secure filenames via `random_bytes(16)`.
- **Controller**: `isOwner()` now uses strict integer comparison with type cast to prevent type juggling bypass.
- **Response**: Added HTTP status codes: 301, 304, 405, 409, 429, 502, 503.
- **Router**: `getStatusCodeName()` made `public static` for reuse from Controller. Added codes: 405, 409, 429, 502, 503.
- **Queue**: Multi-database DDL support (PostgreSQL, SQLite, MySQL). Transaction rollback safety in error handler.
- **Email**: Added recipient email validation, config file existence check, UTF-8 charset, SMTP timeout setting.
- **Resource**: Proper `mixed` type hints and static arrow functions for collection mapping.
- **All Files**: Added `declare(strict_types=1)` across all core classes.

### 🔧 Directory Permission Hardening

- Changed default directory creation permissions from `0777` to `0750` across all core classes:
  - `Cache.php` — `storage/cache/`
  - `Logger.php` — `storage/logs/`
  - `File.php` — `uploads/`
  - `DatabaseManager.php` — SQLite database directory

---

## v2.0.1 (2026-02-23)

### New Console CLI (Padi CLI)

- **Introduction of `padi` CLI**:
  - Created a new command-line interface inspired by `artisan` and `yii`.
  - Added entry point executable `padi` in the project root.
- **Built-in Commands**:
  - `serve`: Start the PHP development server with host/port options.
  - `init` (alias `setup`): Launch the interactive setup wizard for new projects.
  - `make:controller`: Generate new controllers.
  - `make:model`: Generate models from database tables.
  - `make:migration`: Generate new migration files with timestamps.
  - `migrate`, `migrate:rollback`, `migrate:status`: Manage database migrations.
  - `generate:crud` (alias `g`): Generate complete CRUD scaffolding for a single table.
  - `generate:crud-all` (alias `ga`): Bulk generate CRUD for all tables in the database with auto-routing.
- **Improved Batch Scripts**: Replaced legacy `init_app.bat` and `init_server.bat` with native `padi` CLI commands for better consistency and error handling.
- **Core Architecture Refactoring**:
  - Refactored `public/index.php` into a dedicated `Wibiesana\Padi\Core\Application` class.
  - Slimmed down the entry point to a minimal bootstrap script.
  - Improved separation of concerns and maintainability for the core request lifecycle.

### Performance & Stability

- **High-Performance Query Builder**:
  - Optimized `Query::buildWhere()` loop logic.
  - Reduced complexity from O(N²) to O(N) by eliminating redundant `array_keys()` and `array_search()` calls during condition parsing.
- **FrankenPHP Worker Mode Support**:
  - **Memory Leak Protection**: Automatic reset of static states (Query logs, database errors, and query counters) at the beginning of every request dispatch to ensure stability in long-running worker processes.
  - **Graceful Termination**: Implemented `TerminateException` for clean control flow exit when sending JSON/Redirect responses, preventing unwanted execution of remaining controller logic in worker mode.

### Documentation Enhancements

- **Reorganized Documentation**:
  - Updated all documentation files to reflect the new versioning.
  - Added CLI documentation to `CODE_GENERATOR.md`.

## v2.0.0 (2026-02-22)

### Namespace Refactoring

- **Core Namespace Consolidation**:
  - Standardized all core framework classes under the `Wibiesana\Padi\Core` namespace.
  - Updated all internal references, scripts, and templates to reflect the new namespace structure.
  - This change improves package organization and prevents naming collisions.

### Authentication & Security

- **Secure Password Reset**:
  - Implemented `PasswordResetController` and `PasswordReset` model for a robust recovery flow.
  - Decoupled recovery logic from `AuthController` for better modularity.
  - Added support for token-based password updates with security expiration checks.

### Generator Enhancements

- **Inverse Relation Detection**:
  - Implemented automatic detection of `hasMany` and `hasOne` relationships.
  - The generator now scans all tables to identify foreign keys pointing back to the model being generated.
  - **Smart Selection**: Automatically decides between `hasOne` (if unique index exists) and `hasMany` (if not).
  - **Automatic Pluralization**: Generates logical method names (e.g., `user->posts()`) automatically.
- **Code Cleanup**:
  - Removed unused variables and dead code from `Generator.php` for better performance and maintainability.

### Core & Server Optimizations

- **Database Connection Reliability**:
  - Implemented automatic "Keep-Alive" health checks in `index.php`.
  - The framework now detects dead connections (e.g., "MySQL server has gone away") and automatically reconnects, which is essential for **FrankenPHP Worker Mode** and long-running processes.
- **Improved Routing & Hosting**:
  - Enhanced URI normalization in `public/index.php` to better support shared hosting environments and sub-directory deployments.
  - Better handling of `REQUEST_URI` when the script path is included in the URL.
- **Project Structure Refactoring**:
  - Relocated the `config/` directory from `app/config/` to the project root for better accessibility and standardization across the framework.
  - Updated `Core\Auth`, `Core\DatabaseManager`, `Core\Email`, and `Core\Logger` to support the new configuration path.

### Generator Improvements

- **Query Builder Integration**:
  - Refactored `padi_core/Generator.php` to utilize the `Core\Query` builder for all generated search methods.
  - Replaced raw SQL concatenation with the fluent API for improved security and database engine abstraction.

---

## v1.0.4 (2026-02-20)

### Query Builder Enhancements

- **PostgreSQL Case-Insensitivity**:
  - Implemented automatic `ILIKE` conversion for PostgreSQL.
  - Added `autoIlike(bool)` method to toggle this behavior.
- **Aggregate Methods**:
  - Added dedicated methods for common aggregations: `sum()`, `avg()`, `min()`, and `max()`.
- **Ordering Improvements**:
  - Added `addOrderBy()` for building complex sort criteria incrementally.
- **New Helper Methods**:
  - Added specific WHERE helpers: `whereIn`, `whereNotIn`, `whereBetween`, `whereNotBetween`, `whereNull`, `whereNotNull`.
  - Added `paginate($perPage, $page)` for easy pagination.
  - Added `rawSql()` for debugging generated SQL.

---

## v1.0.3 (2026-02-17)

### Performance & Serving

- **FrankenPHP Worker Mode**:
  - Added native support for FrankenPHP worker mode in `index.php` for massive performance gains.
  - Implemented automatic state resetting (`Database` & `DatabaseManager`) between requests in persistent worker loops.
- **Request Lifecycle Optimizations**:
  - Integrated CORS and Preflight (`OPTIONS`) handling directly into the entry point.
  - Enhanced global exception handling to provide structured JSON responses for all uncaught errors and PDO exceptions.

### Environment & Configuration

- **Debug Enforcement**:
  - Strictly enforced `app_debug` logic based on `APP_ENV`: forced `off` in production and `on` (by default) in development.
  - Fixed `.env` parsing issue where boolean strings were not correctly evaluated.
- **PHP 8.4 Support**:
  - Updated minimum PHP requirement to `v8.4` in `composer.json`.
- **Debugging Enhancements**:
  - Added `debug_log` global helper for streamlined error logging.
  - Integrated server environment dumping for improved development diagnostics.

## v1.0.2 (2026-02-17)

### Package & Dependency Management

- **Packagist Integration**:
  - Official registration of `padi-template` on Packagist as `wibiesana/padi-rest-api`.
  - Migrated core functionality to external dependency `wibiesana/padi-core` (v1.0.2+).
  - Removed local `core/` directory; framework core is now managed via Composer.

## v1.0.1 (2026-02-17)

### Core Framework Updates

- **PHP Compatibility**:
  - Fixed "Implicitly nullable parameter" deprecation warnings for PHP 8.1+.
  - Updated `core/Cache.php`, `core/Controller.php`, and `core/ActiveRecord.php` with explicit nullable type hints.
- **Generator Improvements**:
  - Added support for sorting in generated `searchPaginate` methods.
  - Set default pagination size to 25 items.
  - Fixed `primaryKey` type hint to support composite keys (`string|array`).
- **ActiveRecord enhancements**:
  - Refined `searchPaginate` with improved SQL join logic and table aliasing.
  - Enhanced relationship eager loading (`loadRelations`).
- **Database & Routing**:
  - Improved multi-database connection management in `DatabaseManager`.
  - Added URI normalization to filter redundant slashes in request paths.
- **Audit System**:
  - Integrated semi-automatic audit fields (`created_at`, `updated_at`, etc.) directly into `ActiveRecord`.

## v1.0.0

- Initial release of Padi REST API Framework.
- Core features: ActiveRecord, Fluent Query Builder, Autoloading, JWT Auth.
