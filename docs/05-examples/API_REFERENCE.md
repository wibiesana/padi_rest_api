# 📚 API Reference

**Padi REST API Framework v1.0.0**

---

## Standard Response Format

All API responses follow a consistent JSON structure, including a `message_code` for programmatic error handling.

### Success Response

```json
{
  "success": true,
  "message": "Operation successful",
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
  "message": "Error message",
  "message_code": "ERROR_CODE",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

---

## 🔑 Message Codes

| Code                    | HTTP Status | Description                                     |
| :---------------------- | :---------- | :---------------------------------------------- |
| `SUCCESS`               | 200         | Request successful                              |
| `CREATED`               | 201         | Resource created successfully                   |
| `NO_CONTENT`            | 204         | Request successful, no content to return        |
| `VALIDATION_FAILED`     | 422         | Request validation failed                       |
| `BAD_REQUEST`           | 400         | Invalid request format or parameters            |
| `UNAUTHORIZED`          | 401         | Authentication required                         |
| `INVALID_CREDENTIALS`   | 401         | Login failed - wrong username/email or password |
| `NO_TOKEN_PROVIDED`     | 401         | No authentication token provided                |
| `INVALID_TOKEN`         | 401         | Invalid or expired token                        |
| `FORBIDDEN`             | 403         | Access denied - insufficient permissions        |
| `NOT_FOUND`             | 404         | Resource not found                              |
| `ROUTE_NOT_FOUND`       | 404         | API endpoint not found                          |
| `RATE_LIMIT_EXCEEDED`   | 429         | Too many requests                               |
| `INTERNAL_SERVER_ERROR` | 500         | Server-side error                               |

---

## HTTP Status Codes

| Code    | Status                | Usage                              |
| ------- | --------------------- | ---------------------------------- |
| **200** | OK                    | Successful GET, PUT, DELETE        |
| **201** | Created               | Successful POST (resource created) |
| **400** | Bad Request           | Invalid request format             |
| **401** | Unauthorized          | Missing or invalid authentication  |
| **403** | Forbidden             | Authenticated but not authorized   |
| **404** | Not Found             | Resource not found                 |
| **422** | Unprocessable Entity  | Validation errors                  |
| **429** | Too Many Requests     | Rate limit exceeded                |
| **500** | Internal Server Error | Server error                       |

---

## Authentication Endpoints

### Register New User

**Endpoint:** `POST /auth/register`

**Request:**

```json
{
  "name": "John Doe",
  "username": "johndoe",
  "email": "john@example.com",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!"
}
```

**Response (201):**

```json
{
  "success": true,
  "message": "User registered successfully",
  "message_code": "CREATED",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "username": "johndoe",
      "email": "john@example.com",
      "created_at": "2026-02-09 09:50:00"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }
}
```

### Login

**Endpoint:** `POST /auth/login`

**Request:**

```json
{
  "username": "john@example.com",
  "password": "SecurePass123!",
  "remember_me": true
}
```

**Parameters:**

- `username` (required): Email or username
- `password` (required): User password
- `remember_me` (optional): Set to `true` or `1` for extended session (365 days). Default session is 1 hour.

**Response (200):**

```json
{
  "success": true,
  "message": "Login successful",
  "message_code": "SUCCESS",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "username": "johndoe",
      "email": "john@example.com"
    }
  }
}
```

### Get Current User

**Endpoint:** `GET /auth/me`

**Headers:**

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Response (200):**

```json
{
  "success": true,
  "message_code": "SUCCESS",
  "data": {
    "id": 1,
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "created_at": "2026-02-09 09:50:00"
  }
}
```

### Logout

**Endpoint:** `POST /auth/logout`

**Headers:**

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Response (200):**

```json
{
  "success": true,
  "message": "Logged out successfully",
  "message_code": "SUCCESS"
}
```

---

## CRUD Endpoints

All auto-generated resources follow this pattern.

### List All Resources

**Endpoint:** `GET /resources`

**Query Parameters:**

- `page` (integer): Page number (default: 1)
- `per_page` (integer): Items per page (default: 20)
- `search` (string): Search keyword
- `sort` (string): Column to sort by (e.g., `id`, `name`)
- `order` (string): `asc` or `desc`

**Example:**

```
GET /products?page=1&per_page=20&search=laptop&sort=price&order=desc
```

**Response (200):**

```json
{
  "success": true,
  "message_code": "SUCCESS",
  "data": [
    {
      "id": 1,
      "name": "Product 1",
      "price": 99.99,
      "created_at": "2026-02-09 09:50:00"
    }
  ],
  "pagination": {
    "total": 100,
    "page": 1,
    "per_page": 20,
    "total_pages": 5
  }
}
```

### Get Single Resource

**Endpoint:** `GET /resources/{id}`

**Response (200):**

```json
{
  "success": true,
  "message_code": "SUCCESS",
  "data": {
    "id": 1,
    "name": "Product 1",
    "price": 99.99,
    "created_at": "2026-02-09 09:50:00"
  }
}
```

---

## Validation Rules

| Rule                  | Description                         |
| :-------------------- | :---------------------------------- |
| `required`            | Field must be present and not empty |
| `string`              | Must be a valid string              |
| `numeric`             | Must be a numeric value             |
| `email`               | Must be a valid email format        |
| `min:n`               | Minimum length/value                |
| `max:n`               | Maximum length/value                |
| `unique:table,column` | Must be unique in the database      |
| `exists:table,column` | Must exist in the database          |

---

## Rate Limiting

### Headers

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1707480000
```

### Exceeded Response (429)

```json
{
  "success": false,
  "message": "Too many requests. Please try again later.",
  "message_code": "RATE_LIMIT_EXCEEDED"
}
```

---

## cURL Examples

### Register

```bash
curl -X POST http://localhost:8085/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!"
  }'
```

---

**Next Steps:**

- [Frontend Integration](../03-advanced/FRONTEND_INTEGRATION.md)
- [Postman Guide](../03-advanced/POSTMAN_GUIDE.md)
