# Error Handling & Message Codes

Complete guide to error handling and response codes in Padi REST API Framework.

## Table of Contents

- [Overview](#overview)
- [Response Structure](#response-structure)
- [Message Codes Reference](#message-codes-reference)
- [Success Codes](#success-codes)
- [Error Codes](#error-codes)
- [Database Error Handling](#database-error-handling)
- [Frontend Integration](#frontend-integration)
- [Custom Error Handling](#custom-error-handling)
- [Best Practices](#best-practices)
- [Debugging](#debugging)

## Overview

All API responses include a standardized `message_code` field to help frontend applications identify and handle specific scenarios without parsing error messages.

### Key Benefits

- ✅ **Easy error identification** - No need to parse message strings
- ✅ **Internationalization ready** - Display custom messages per locale
- ✅ **Type-safe handling** - Use constants/enums in frontend
- ✅ **Consistent API** - Same structure across all endpoints

---

## Response Structure

### Success Response

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "message_code": "SUCCESS",
  "data": {
    // Response data here
  }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Human-readable error message",
  "message_code": "ERROR_CODE_HERE"
}
```

### Validation Error Response

```json
{
  "success": false,
  "message": "Validation failed",
  "message_code": "VALIDATION_FAILED",
  "errors": {
    "email": ["Email is required", "Email must be valid"],
    "password": ["Password must be at least 8 characters"]
  }
}
```

---

## Message Codes Reference

### Success Codes

| Code         | HTTP Status | Description                              | Usage                          |
| ------------ | ----------- | ---------------------------------------- | ------------------------------ |
| `SUCCESS`    | 200         | Request successful                       | GET, general success responses |
| `CREATED`    | 201         | Resource created successfully            | POST - new resource created    |
| `NO_CONTENT` | 204         | Request successful, no content to return | DELETE - resource deleted      |

---

## Error Codes

### Authentication & Authorization Errors (401, 403)

| Code                  | HTTP Status | Description                                     | When It Occurs                                        |
| --------------------- | ----------- | ----------------------------------------------- | ----------------------------------------------------- |
| `UNAUTHORIZED`        | 401         | Authentication required (generic)               | Default 401 error                                     |
| `INVALID_CREDENTIALS` | 401         | Login failed - wrong username/email or password | Login endpoint with wrong credentials                 |
| `NO_TOKEN_PROVIDED`   | 401         | No authentication token provided                | Protected route accessed without Bearer token         |
| `INVALID_TOKEN`       | 401         | Invalid or expired token                        | Protected route accessed with invalid/expired token   |
| `FORBIDDEN`           | 403         | Access denied                                   | User doesn't have permission for the requested action |

### Validation & Client Errors (400, 422)

| Code                | HTTP Status | Description               | When It Occurs                     |
| ------------------- | ----------- | ------------------------- | ---------------------------------- |
| `BAD_REQUEST`       | 400         | Invalid request           | Malformed request, missing headers |
| `VALIDATION_FAILED` | 422         | Request validation failed | Input validation errors            |

### Resource Errors (404)

| Code              | HTTP Status | Description            | When It Occurs                   |
| ----------------- | ----------- | ---------------------- | -------------------------------- |
| `NOT_FOUND`       | 404         | Resource not found     | Requested resource doesn't exist |
| `ROUTE_NOT_FOUND` | 404         | API endpoint not found | Invalid API endpoint             |

### Rate Limiting (429)

| Code                  | HTTP Status | Description       | When It Occurs      |
| --------------------- | ----------- | ----------------- | ------------------- |
| `RATE_LIMIT_EXCEEDED` | 429         | Too many requests | Rate limit exceeded |

### Database Errors (500)

| Code             | HTTP Status | Description            | When It Occurs          |
| ---------------- | ----------- | ---------------------- | ----------------------- |
| `DATABASE_ERROR` | 500         | Database related error | Query failure, SQL sync |

### Server Errors (500)

| Code                    | HTTP Status | Description  | When It Occurs                |
| ----------------------- | ----------- | ------------ | ----------------------------- |
| `INTERNAL_SERVER_ERROR` | 500         | Server error | Unhandled exceptions, crashes |

---

## Database Error Handling

The framework provides detailed database error handling for easier debugging while maintaining security in production.

### Key Features

1.  **Automatic Logging**: All database errors are logged with full details (SQL, parameters, stack trace).
2.  **Sensitive Data Redaction**: Automatically redacts sensitive fields (passwords, tokens) from debug logs.
3.  **ActiveRecord Integration**: CRUD operations automatically catch and log database exceptions.
4.  **Enhanced Debug Info**: Detailed error reports when `APP_DEBUG=true`.

### Usage in Controllers

```php
try {
    $id = $this->model->create($data);
    return $this->created(['id' => $id]);
} catch (\PDOException $e) {
    // Standardized database error response
    return $this->databaseError('Failed to save data', $e);
}
```

### Response in Debug Mode (`APP_DEBUG=true`)

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
    }
  }
}
```

### New Environment Variables

- `DEBUG_SHOW_ALL_DB_ERRORS=true` - Display all database errors in the response.
- `DEBUG_SHOW_QUERIES=true` - Show raw SQL queries in the debug metadata.

---

## Frontend Integration

### Vue 3 Example

```javascript
// composables/useApi.js
export const useApi = () => {
  const handleError = (data) => {
    const messages = {
      INVALID_CREDENTIALS: "Username atau password salah",
      INVALID_TOKEN: "Sesi Anda telah berakhir",
      NO_TOKEN_PROVIDED: "Silakan login terlebih dahulu",
      VALIDATION_FAILED: "Data yang Anda masukkan tidak valid",
      DATABASE_ERROR: "Gangguan pada server data",
      NOT_FOUND: "Data tidak ditemukan",
      FORBIDDEN: "Anda tidak memiliki akses",
    };

    return messages[data.message_code] || data.message;
  };

  return { handleError };
};
```

---

## Custom Error Handling

### Custom Exception with Code

```php
public function purchase(int $id)
{
    $product = $this->model->find($id);

    if ($product['stock'] < 1) {
        throw new \Exception('Product is out of stock', 400);
    }

    // Process purchase...
}
```

---

## Best Practices

1.  **Use Specific Status Codes**: Return 404 for missing items, 401 for auth issues, 422 for validation.
2.  **Keep Messages User-Friendly**: Avoid showing raw SQL errors to users; use `message_code` for mapping.
3.  **Security-First**: Never reveal user existence in auth endpoints (e.g., use "Invalid credentials" instead of "User not found").
4.  **Log Everything**: Let the framework log the details; return only what the client needs.

---

## Debugging

In development mode (`APP_DEBUG=true`), all responses include a `debug` section:

```json
{
  "data": { ... },
  "debug": {
    "execution_time": "45.23ms",
    "memory_usage": "12.45MB",
    "query_count": 3,
    "queries": [ ... ]
  }
}
```

---

**Last Updated:** 2026-02-09  
**Version:** 1.0.0
