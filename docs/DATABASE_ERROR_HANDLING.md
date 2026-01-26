# Database Error Handling Enhancement

Fitur ini menambahkan error handling yang lebih baik untuk database dengan informasi debug yang detail ketika terjadi error 500 internal error.

## Fitur Baru

### 1. Database Error Logging

- **DatabaseManager::logError()** - Log error database dengan detail lengkap
- **DatabaseManager::getLastError()** - Mendapatkan error database terakhir
- **DatabaseManager::getAllErrors()** - Mendapatkan semua error database
- **DatabaseManager::clearErrors()** - Membersihkan log error

### 2. Controller Database Error Method

- **databaseError()** - Method baru di Controller untuk menangani database error dengan informasi debug

### 3. Enhanced Response Debug Info

Ketika `APP_DEBUG=true`, response JSON akan menyertakan:

```json
{
  "success": false,
  "message": "Database error occurred",
  "message_code": "DATABASE_ERROR",
  "debug": {
    "execution_time": "15.32ms",
    "memory_usage": "2.34MB",
    "query_count": 3,
    "database_error": {
      "type": "query_error",
      "message": "SQLSTATE[23000]: Integrity constraint violation",
      "code": 23000,
      "file": "/path/to/file.php",
      "line": 123,
      "query": "INSERT INTO users ...",
      "params": {...},
      "timestamp": "2026-01-26 10:30:45"
    },
    "database_errors_count": 1
  }
}
```

### 4. Environment Variables Baru

- `DEBUG_SHOW_ALL_DB_ERRORS=true` - Tampilkan semua database errors (default: false)
- `DEBUG_SHOW_QUERIES=true` - Tampilkan detail queries (sudah ada sebelumnya)

## Cara Penggunaan

### 1. Menggunakan databaseError() di Controller

```php
try {
    $id = $this->model->create($data);
    $this->success($id, 'Data created successfully');
} catch (\PDOException $e) {
    $this->databaseError('Failed to create data', $e);
}
```

### 2. Manual Error Logging

```php
try {
    // database operation
} catch (\PDOException $e) {
    Database::logQueryError($e, $sql, $params);
    throw $e;
}
```

### 3. Mendapatkan Database Error Info

```php
$lastError = DatabaseManager::getLastError();
$allErrors = DatabaseManager::getAllErrors();
```

## Keamanan

- Sensitive parameters seperti password, token, dll otomatis di-redact
- Error detail hanya tampil ketika `APP_DEBUG=true`
- Production mode tetap aman dengan informasi minimal

## ActiveRecord Error Handling

Semua method ActiveRecord (create, update, delete) sudah otomatis menangkap dan log PDOException:

- Error akan di-log secara otomatis
- Exception akan di-throw ulang untuk handling di controller
- Query dan parameters akan direkam untuk debugging

## Exception Handler Global

Exception handler di `public/index.php` telah diperbarui untuk:

- Mendeteksi PDOException secara khusus
- Menyertakan database error info pada response debug
- Memberikan message yang lebih spesifik untuk database errors

## Contoh Response Error

### Debug Mode (APP_DEBUG=true)

```json
{
  "success": false,
  "message": "Failed to create user",
  "message_code": "DATABASE_ERROR",
  "database_error": {
    "type": "query_error",
    "message": "Duplicate entry 'john@example.com' for key 'users_email_unique'",
    "code": 1062,
    "file": "/app/core/ActiveRecord.php",
    "line": 295,
    "query": "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)",
    "params": {
      "username": "john",
      "email": "john@example.com",
      "password": "***REDACTED***"
    },
    "timestamp": "2026-01-26 10:30:45"
  },
  "exception": {
    "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry",
    "code": 23000,
    "file": "/app/core/ActiveRecord.php",
    "line": 295
  }
}
```

### Production Mode (APP_DEBUG=false)

```json
{
  "success": false,
  "message": "Failed to create user",
  "message_code": "DATABASE_ERROR"
}
```
