# CHANGE LOG

## v1.0.6 (2026-02-22)

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

---

## v2.0.0 (2026-02-21)

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
