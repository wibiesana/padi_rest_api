# CHANGE LOG

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
