# Error Handling & Message Codes

Complete guide to error handling and response codes in Padi REST API Framework.

## Table of Contents

- [Overview](#overview)
- [Response Structure](#response-structure)
- [Message Codes Reference](#message-codes-reference)
- [Success Codes](#success-codes)
- [Error Codes](#error-codes)
- [Frontend Integration](#frontend-integration)
- [Custom Error Handling](#custom-error-handling)

## Overview

All API responses include a standardized `message_code` field to help frontend applications identify and handle specific scenarios without parsing error messages.

### Key Benefits

- ✅ **Easy error identification** - No need to parse message strings
- ✅ **Internationalization ready** - Display custom messages per locale
- ✅ **Type-safe handling** - Use constants/enums in frontend
- ✅ **Consistent API** - Same structure across all endpoints

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

## Message Codes Reference

### Success Codes

| Code         | HTTP Status | Description                              | Usage                          |
| ------------ | ----------- | ---------------------------------------- | ------------------------------ |
| `SUCCESS`    | 200         | Request successful                       | GET, general success responses |
| `CREATED`    | 201         | Resource created successfully            | POST - new resource created    |
| `NO_CONTENT` | 204         | Request successful, no content to return | DELETE - resource deleted      |

**Examples:**

```php
// GET /api/users/1
return $user; // Auto-formatted based on RESPONSE_FORMAT

// POST /api/users
$this->setStatusCode(201);
return $user; // Status 201, auto-formatted
```

## Error Codes

### Authentication & Authorization Errors (401, 403)

| Code                  | HTTP Status | Description                                     | When It Occurs                                        |
| --------------------- | ----------- | ----------------------------------------------- | ----------------------------------------------------- |
| `UNAUTHORIZED`        | 401         | Authentication required (generic)               | Default 401 error                                     |
| `INVALID_CREDENTIALS` | 401         | Login failed - wrong username/email or password | Login endpoint with wrong credentials                 |
| `NO_TOKEN_PROVIDED`   | 401         | No authentication token provided                | Protected route accessed without Bearer token         |
| `INVALID_TOKEN`       | 401         | Invalid or expired token                        | Protected route accessed with invalid/expired token   |
| `FORBIDDEN`           | 403         | Access denied                                   | User doesn't have permission for the requested action |

**Security Note:** `INVALID_CREDENTIALS` uses a generic message "Invalid credentials" to prevent username enumeration attacks, but the `message_code` allows frontend to display specific user-friendly messages.

**Examples:**

```json
// Login with wrong password
POST /api/auth/login
{
  "success": false,
  "message": "Invalid credentials",
  "message_code": "INVALID_CREDENTIALS"
}

// Access protected route without token
GET /api/users
{
  "success": false,
  "message": "Unauthorized - No token provided",
  "message_code": "NO_TOKEN_PROVIDED"
}

// Access with expired token
GET /api/users
{
  "success": false,
  "message": "Unauthorized - Invalid or expired token",
  "message_code": "INVALID_TOKEN"
}
```

### Validation & Client Errors (400, 422)

| Code                | HTTP Status | Description               | When It Occurs                     |
| ------------------- | ----------- | ------------------------- | ---------------------------------- |
| `BAD_REQUEST`       | 400         | Invalid request           | Malformed request, missing headers |
| `VALIDATION_FAILED` | 422         | Request validation failed | Input validation errors            |

**Examples:**

```json
// Validation error
POST /api/auth/register
{
  "success": false,
  "message": "Validation failed",
  "message_code": "VALIDATION_FAILED",
  "errors": {
    "email": ["Email is required"],
    "password": ["Password must be at least 8 characters"]
  }
}
```

### Resource Errors (404)

| Code              | HTTP Status | Description            | When It Occurs                   |
| ----------------- | ----------- | ---------------------- | -------------------------------- |
| `NOT_FOUND`       | 404         | Resource not found     | Requested resource doesn't exist |
| `ROUTE_NOT_FOUND` | 404         | API endpoint not found | Invalid API endpoint             |

**Examples:**

```json
// Resource not found
GET /api/users/99999
{
  "success": false,
  "message": "User not found",
  "message_code": "NOT_FOUND"
}

// Invalid endpoint
GET /api/invalid-endpoint
{
  "success": false,
  "message": "Route not found",
  "message_code": "ROUTE_NOT_FOUND"
}
```

### Rate Limiting (429)

| Code                  | HTTP Status | Description       | When It Occurs      |
| --------------------- | ----------- | ----------------- | ------------------- |
| `RATE_LIMIT_EXCEEDED` | 429         | Too many requests | Rate limit exceeded |

**Example:**

```json
{
  "success": false,
  "message": "Too many requests. Please try again later.",
  "message_code": "RATE_LIMIT_EXCEEDED"
}
```

### Server Errors (500)

| Code                    | HTTP Status | Description  | When It Occurs                |
| ----------------------- | ----------- | ------------ | ----------------------------- |
| `INTERNAL_SERVER_ERROR` | 500         | Server error | Unhandled exceptions, crashes |

**Example:**

```json
{
  "success": false,
  "message": "Internal Server Error",
  "message_code": "INTERNAL_SERVER_ERROR"
}
```

### Generic Error

| Code    | HTTP Status | Description   | When It Occurs             |
| ------- | ----------- | ------------- | -------------------------- |
| `ERROR` | Various     | Generic error | Custom error with any code |

## Frontend Integration

### React Example

```javascript
// API service
const handleApiError = (data) => {
  switch (data.message_code) {
    case "INVALID_CREDENTIALS":
      return "Wrong username or password. Please try again.";

    case "INVALID_TOKEN":
    case "NO_TOKEN_PROVIDED":
      // Redirect to login
      localStorage.removeItem("token");
      window.location.href = "/login";
      return "Session expired. Please login again.";

    case "VALIDATION_FAILED":
      // Handle validation errors
      return Object.values(data.errors).flat().join(", ");

    case "RATE_LIMIT_EXCEEDED":
      return "Too many attempts. Please wait a moment.";

    case "NOT_FOUND":
      return "Resource not found.";

    case "FORBIDDEN":
      return "You do not have permission to perform this action.";

    default:
      return data.message || "An error occurred";
  }
};

// Usage in component
const login = async (credentials) => {
  try {
    const response = await fetch("/api/auth/login", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(credentials),
    });

    const data = await response.json();

    if (data.success) {
      // Handle success
      localStorage.setItem("token", data.data.token);
      navigate("/dashboard");
    } else {
      // Handle error with message_code
      const errorMessage = handleApiError(data);
      setError(errorMessage);
    }
  } catch (error) {
    setError("Network error. Please try again.");
  }
};
```

### Vue 3 Example

```javascript
// composables/useApi.js
export const useApi = () => {
  const handleError = (data) => {
    const messages = {
      INVALID_CREDENTIALS: 'Username atau password salah',
      INVALID_TOKEN: 'Sesi Anda telah berakhir',
      NO_TOKEN_PROVIDED: 'Silakan login terlebih dahulu',
      VALIDATION_FAILED: 'Data yang Anda masukkan tidak valid',
      RATE_LIMIT_EXCEEDED: 'Terlalu banyak percobaan, tunggu sebentar',
      NOT_FOUND: 'Data tidak ditemukan',
      FORBIDDEN: 'Anda tidak memiliki akses',
    };

    return messages[data.message_code] || data.message;
  };

  return { handleError };
};

// Usage in component
<script setup>
import { ref } from 'vue';
import { useApi } from '@/composables/useApi';

const { handleError } = useApi();
const error = ref('');

const login = async (credentials) => {
  const response = await fetch('/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(credentials)
  });

  const data = await response.json();

  if (!data.success) {
    error.value = handleError(data);
  }
};
</script>
```

### Angular Example

```typescript
// error-handler.service.ts
import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class ErrorHandlerService {
  handleApiError(data: any): string {
    const errorMessages: { [key: string]: string } = {
      INVALID_CREDENTIALS: 'Invalid username or password',
      INVALID_TOKEN: 'Your session has expired',
      NO_TOKEN_PROVIDED: 'Please login to continue',
      VALIDATION_FAILED: 'Please check your input',
      RATE_LIMIT_EXCEEDED: 'Too many requests. Please wait.',
      NOT_FOUND: 'Resource not found',
      FORBIDDEN: 'Access denied'
    };

    return errorMessages[data.message_code] || data.message;
  }
}

// auth.service.ts
login(credentials: any) {
  return this.http.post('/api/auth/login', credentials).pipe(
    map((data: any) => {
      if (!data.success) {
        throw new Error(this.errorHandler.handleApiError(data));
      }
      return data;
    })
  );
}
```

### TypeScript Constants

```typescript
// constants/message-codes.ts
export enum MessageCode {
  // Success
  SUCCESS = "SUCCESS",
  CREATED = "CREATED",
  NO_CONTENT = "NO_CONTENT",

  // Auth errors
  UNAUTHORIZED = "UNAUTHORIZED",
  INVALID_CREDENTIALS = "INVALID_CREDENTIALS",
  NO_TOKEN_PROVIDED = "NO_TOKEN_PROVIDED",
  INVALID_TOKEN = "INVALID_TOKEN",
  FORBIDDEN = "FORBIDDEN",

  // Client errors
  BAD_REQUEST = "BAD_REQUEST",
  VALIDATION_FAILED = "VALIDATION_FAILED",
  NOT_FOUND = "NOT_FOUND",
  ROUTE_NOT_FOUND = "ROUTE_NOT_FOUND",

  // Rate limit
  RATE_LIMIT_EXCEEDED = "RATE_LIMIT_EXCEEDED",

  // Server error
  INTERNAL_SERVER_ERROR = "INTERNAL_SERVER_ERROR",
  ERROR = "ERROR",
}

export interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  message_code: MessageCode;
  data?: T;
  errors?: Record<string, string[]>;
}

// Usage
const handleResponse = (response: ApiResponse) => {
  switch (response.message_code) {
    case MessageCode.INVALID_CREDENTIALS:
      // Handle invalid credentials
      break;
    case MessageCode.INVALID_TOKEN:
      // Redirect to login
      break;
    // ... etc
  }
};
```

## Custom Error Handling

### In Controllers

You can pass custom `message_code` to error methods:

```php
<?php

namespace App\Controllers;

use Core\Controller;

class ProductController extends Controller
{
    public function show(int $id)
    {
        $product = $this->model->find($id);

        if (!$product) {
            // Custom exception with specific message for scenario
            throw new \Exception('Product not found', 404);
        }

        return $product;
    }

    public function purchase(int $id)
    {
        $product = $this->model->find($id);

        if (!$product) {
            throw new \Exception('Product not found', 404);
        }

        if ($product['stock'] < 1) {
            // Custom error for out of stock
            throw new \Exception('Product is out of stock', 400);
        }

        // Process purchase...
        $this->setStatusCode(201);
        return $order;
    }
}
```

### Available Controller Methods

```php
// Return data directly (auto-formatted)
return $data;                                     // Auto status 200, auto message_code
$this->setStatusCode(201); return $data;         // Custom status with data

// Exception handling (automatic error responses)
throw new \Exception('Error message', 400);      // Auto message_code based on status
throw new \Exception('Not found', 404);          // Auto message_code: NOT_FOUND
throw new \Exception('Forbidden', 403);          // Auto message_code: FORBIDDEN
throw new \Exception('Not found', 404);          // Auto message_code: NOT_FOUND

// Helper methods (for special cases)
return $this->databaseError('Database connection failed');  // Database error handler

// Validation (automatic)
$this->validate([...]);                          // Auto message_code: VALIDATION_FAILED on error
```

### Direct Response Usage

```php
use Core\Response;

$response = new Response();

// Custom error with specific code
$response->json([
    'success' => false,
    'message' => 'Payment processing failed',
    'message_code' => 'PAYMENT_FAILED',
    'data' => [
        'transaction_id' => '12345',
        'reason' => 'Insufficient funds'
    ]
], 402);
```

## Best Practices

### 1. Use Specific Codes When Appropriate

```php
// ❌ Generic
$this->error('Error', 400);

// ✅ Specific
$this->error('Product out of stock', 400, null, 'OUT_OF_STOCK');
```

### 2. Keep Messages User-Friendly

```php
// ❌ Technical
$this->error('Foreign key constraint violation', 400);

// ✅ User-friendly
$this->error('Cannot delete user with active orders', 400, null, 'HAS_DEPENDENCIES');
```

### 3. Security-First for Auth Errors

```php
// ✅ Good - Generic message, specific code
$this->unauthorized('Invalid credentials', 'INVALID_CREDENTIALS');

// ❌ Bad - Reveals user existence
$this->error('User not found', 404);
```

### 4. Document Custom Codes

If you add custom `message_code` values, document them in your API documentation.

```php
/**
 * Purchase product
 *
 * @throws 400 OUT_OF_STOCK - Product is out of stock
 * @throws 402 PAYMENT_FAILED - Payment processing failed
 * @throws 404 PRODUCT_NOT_FOUND - Product not found
 */
public function purchase(int $id): void { }
```

## Debugging

In development mode (`APP_DEBUG=true`), responses include debug information:

```json
{
  "data": {
    "success": false,
    "message": "Internal Server Error",
    "message_code": "INTERNAL_SERVER_ERROR"
  },
  "debug": {
    "execution_time": "45.23ms",
    "memory_usage": "12.45MB",
    "query_count": 3,
    "queries": [
      // SQL queries if DEBUG_SHOW_QUERIES=true
    ]
  }
}
```

## Related Documentation

- [Role-Based Access Control (RBAC)](../02-core-concepts/RBAC.md) - Authorization and permissions
- [Authentication Guide](../02-core-concepts/AUTHENTICATION.md) - JWT authentication
- [API Testing Guide](API_TESTING.md)
- [Frontend Integration](FRONTEND_INTEGRATION.md)
- [Security Best Practices](SECURITY.md)
- [Postman Collections](POSTMAN_GUIDE.md)

---

**Last Updated:** 2026-01-24  
**Version:** 1.0.0
