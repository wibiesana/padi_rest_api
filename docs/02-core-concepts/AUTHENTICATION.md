<!--
  Authentication & Security
  Improved for beginners: clearer structure, quick-start examples, and troubleshooting.
-->

# Authentication & Security (Beginner Friendly)

This document explains how authentication works in Padi REST API using JSON Web Tokens (JWT). It is written for beginners and includes step-by-step examples, common errors, and troubleshooting tips.

**Quick overview:**

- The app uses JWT for stateless authentication.
- Clients (web/mobile) authenticate via `/auth/login` or `/auth/register` and receive a token.
- The token is sent in the `Authorization: Bearer <token>` header on protected requests.

## Table of contents

- Prerequisites
- Quick start (example requests)
- Authentication endpoints (register, login, refresh, logout, me)
- How tokens are validated (server-side)
- Common errors & troubleshooting
- Security best practices

---

## Prerequisites

- PHP environment with dependencies installed (`composer install`).
- `.env` configured with `JWT_SECRET`, `DB_*` and other environment variables.
- Basic knowledge of HTTP and headers (GET/POST and Authorization header).

If you do not have `JWT_SECRET` set, generate one:

```bash
php -r "echo bin2hex(random_bytes(32));"
```

---

## Quick start (example flows)

1. Register a new user (example using `curl`):

```bash
curl -X POST http://localhost:8085/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com","password":"SecurePass123!","password_confirmation":"SecurePass123!"}'
```

Response (success):

```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": { "id": 1, "name": "John Doe", "email": "john@example.com" },
    "token": "<JWT_TOKEN_HERE>"
  }
}
```

2. Login (get token):

```bash
curl -X POST http://localhost:8085/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"SecurePass123!"}'
```

Response:

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "<JWT_TOKEN>",
    "user": { "id": 1, "name": "John Doe", "email": "john@example.com" }
  }
}
```

3. Call a protected endpoint (include token in header):

```bash
curl -X GET http://localhost:8085/auth/me \
  -H "Authorization: Bearer <JWT_TOKEN>"
```

Response:

```json
{
  "success": true,
  "data": { "id": 1, "name": "John Doe", "email": "john@example.com" }
}
```

---

## Authentication endpoints (details)

All endpoints expect and return JSON. Replace `http://localhost:8085` with your `APP_URL`.

- `POST /auth/register` — Register a new user. Required: `name`, `email`, `password`, `password_confirmation`.
- `POST /auth/login` — Authenticate and retrieve token. Required: `email`, `password`.
- `GET /auth/me` — Get current authenticated user. Requires `Authorization` header.
- `POST /auth/logout` — Invalidate token.
- `POST /auth/refresh` — Exchange an expiring token for a new one.
- `POST /auth/forgot-password` — Request password reset email.
- `POST /auth/reset-password` — Reset password using token.

Example request/response are shown in the Quick Start section above.

---

## How JWT tokens are validated (server-side)

When a protected endpoint is called, the server does the following:

1. Read `Authorization` header and extract the token (format: `Bearer <token>`).
2. Verify the token signature using `JWT_SECRET` and algorithm (e.g., HS256).
3. Check token expiry (`exp` claim).
4. Extract user ID or user claims from token payload.
5. Optionally validate user still exists in database.

If any check fails, the server returns `401 Unauthorized` with an error message.

---

## Password rules & hashing

- Minimum requirements (default): 8 chars, uppercase, lowercase, number, special character.
- Passwords are hashed using `password_hash(..., PASSWORD_BCRYPT)` before storage.

Example (PHP):

```php
$hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
if (!password_verify($input, $hashed)) {
    // invalid password
}
```

---

## Common errors & troubleshooting

- `401 Unauthorized` — Token missing, invalid, or expired. Check `Authorization` header and token expiry.
- `422 Unprocessable Entity` — Validation failed (e.g., invalid email, weak password). See response for validation messages.
- `429 Too Many Requests` — Rate limit exceeded. Increase `RATE_LIMIT_MAX` in `.env` for development or wait until reset.

Troubleshooting checklist:

1. Ensure `.env` has `JWT_SECRET` and `JWT_ALGORITHM` set.
2. Confirm time on server and client are synchronized (token expiry depends on server time).
3. For local Docker development, use `APP_URL` and ports in `.env` correctly.

---

## Security best practices (short)

- Keep `JWT_SECRET` secret and long (use `openssl rand -hex 32`).
- Use HTTPS in production so tokens are not exposed over the network.
- Prefer short token TTLs and implement refresh logic if needed.
- Use Redis or DB blacklist if you need immediate token revocation on logout.

---

## Password Recovery

The framework includes a secure password reset flow:

1. **Request Reset Link**: `POST /auth/forgot-password`
   - Payload: `{"email": "user@example.com"}`
   - The server generates a single-use token and saves it to the `password_resets` table.
   - For security, the response always suggests success even if the email is not registered.

2. **Reset Password**: `POST /auth/reset-password`
   - Payload: `{"email": "user@example.com", "token": "...", "password": "...", "password_confirmation": "..."}`
   - The token is verified against the database and checked for expiration.
   - Upon success, the user's password is updated and the token is invalidated.

---

## Frontend Integration

### Store Token

```javascript
// After login
const response = await api.post("/auth/login", {
  email: "user@example.com",
  password: "SecurePass123!",
});

// Store token
localStorage.setItem("access_token", response.data.token);
```

### Send Token with Requests

```javascript
// Axios interceptor
axios.interceptors.request.use((config) => {
  const token = localStorage.getItem("access_token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});
```

### Handle Token Expiry

```javascript
// Response interceptor
axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expired or invalid
      localStorage.removeItem("access_token");
      window.location.href = "/login";
    }
    return Promise.reject(error);
  },
);
```

See [FRONTEND_INTEGRATION.md](../03-advanced/FRONTEND_INTEGRATION.md) for complete examples.

---

## Security Best Practices

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new JWT_SECRET (64+ characters)
- [ ] Configure CORS_ALLOWED_ORIGINS with specific domains
- [ ] Enable HTTPS (SSL/TLS)
- [ ] Use strong database password
- [ ] Set appropriate rate limits
- [ ] Enable response compression
- [ ] Disable DEBUG_SHOW_QUERIES

### JWT Best Practices

1. **Never expose JWT_SECRET**
2. **Use different secrets** for dev/staging/production
3. **Set appropriate expiry** (1-24 hours)
4. **Implement token refresh** for long sessions
5. **Validate tokens** on every request
6. **Store tokens securely** (localStorage or httpOnly cookies)

### Password Best Practices

1. **Enforce strong passwords** (8+ chars, mixed case, numbers, symbols)
2. **Never store plain passwords**
3. **Use bcrypt** with cost 10+
4. **Implement password reset** with email verification
5. **Rate limit login attempts**

---

## Troubleshooting

### Common Issues

| Issue                 | Solution                                    |
| --------------------- | ------------------------------------------- |
| 401 Unauthorized      | Check if token is valid and not expired     |
| Invalid JWT signature | Verify JWT_SECRET matches                   |
| Token expired         | Refresh token or login again                |
| CORS error            | Add frontend domain to CORS_ALLOWED_ORIGINS |
| 429 Too Many Requests | Wait or increase rate limit                 |

---

## Next Steps

1. **Models** - [MODELS.md](MODELS.md)
2. **Controllers** - [CONTROLLERS.md](CONTROLLERS.md)
3. **Security Best Practices** - [../03-advanced/SECURITY.md](../03-advanced/SECURITY.md)

---

**Previous:** [← First Steps](../01-getting-started/FIRST_STEPS.md) | **Next:** [Models Guide →](MODELS.md)
