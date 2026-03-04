# 🔒 Security Best Practices

---

## Security Overview

### Security Score: 9.5/10 🛡️

Padi REST API implements multiple layers of security to protect your application and data.

---

## Security Checklist

### Before Production Deployment

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate strong JWT_SECRET (64+ characters)
- [ ] Configure CORS_ALLOWED_ORIGINS with specific domains
- [ ] Enable HTTPS (SSL/TLS)
- [ ] Use strong database password
- [ ] Set appropriate rate limits
- [ ] Disable DEBUG_SHOW_QUERIES
- [ ] Review and update validation rules
- [ ] Implement proper error logging

---

## Implemented Security Features

| Feature                         | Status | Description                                               |
| ------------------------------- | ------ | --------------------------------------------------------- |
| **SQL Injection Protection**    | ✅     | PDO prepared statements + LIMIT/OFFSET bound as PARAM_INT |
| **XSS Protection**              | ✅     | CSP headers (X-XSS-Protection deprecated in favor of CSP) |
| **CSRF Protection**             | ✅     | Stateless JWT (no cookies)                                |
| **Clickjacking Protection**     | ✅     | X-Frame-Options: DENY                                     |
| **MIME Sniffing Protection**    | ✅     | X-Content-Type-Options: nosniff                           |
| **Password Hashing**            | ✅     | Bcrypt with cost 10                                       |
| **Rate Limiting**               | ✅     | 60 requests/minute per IP                                 |
| **CORS Whitelist**              | ✅     | Environment-based configuration + Vary: Origin            |
| **HTTPS Enforcement**           | ✅     | HSTS header (production)                                  |
| **Input Validation**            | ✅     | Required for all endpoints                                |
| **Path Traversal Protection**   | ✅     | `sanitizePath()` + `realpath()` verification              |
| **File Upload Security**        | ✅     | Extension blacklist + MIME verification                   |
| **Header Injection Protection** | ✅     | Filename sanitization in downloads                        |
| **Open Redirect Prevention**    | ✅     | URL validation on redirects                               |
| **Referrer Policy**             | ✅     | `strict-origin-when-cross-origin`                         |
| **Permissions Policy**          | ✅     | Camera, microphone, geolocation disabled by default       |
| **Object Injection Prevention** | ✅     | JSON encode/decode instead of unserialize for cache       |

---

## SQL Injection Protection

### How It Works

1. **PDO Prepared Statements** - All queries use parameterized statements
2. **LIMIT/OFFSET Binding** - Bound as `PDO::PARAM_INT` (not interpolated into SQL) **(v2.0.2)**
3. **Column Name Validation** - Validates column names against table schema
4. **Emulated Prepares Disabled** - `PDO::ATTR_EMULATE_PREPARES = false` ensures real server-side prepared statements

### Example

```php
// ✅ SAFE - Uses prepared statements
$products = Query::find()->from('products')
    ->where(['status' => $userInput])
    ->limit(10)   // Bound as PDO::PARAM_INT
    ->offset(20)  // Bound as PDO::PARAM_INT
    ->all();

// ❌ UNSAFE - Raw SQL (avoid)
$sql = "SELECT * FROM products WHERE status = '$userInput'";
```

### Best Practices

✅ **DO:**

- Use the Query Builder or ActiveRecord for all database operations
- Use parameterized conditions (`where(['column' => $value])`)
- Validate and whitelist column names for dynamic sorting

❌ **DON'T:**

- Write raw SQL with string concatenation
- Interpolate user input directly into SQL strings
- Trust `$_GET`/`$_POST` values for column or table names

---

## Security Headers (v2.0.2)

### Automatically Added Headers

The framework now sets comprehensive security headers on **every response**:

```
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 0
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=()
Strict-Transport-Security: max-age=31536000; includeSubDomains  (HTTPS only)
Access-Control-Max-Age: 86400
Vary: Origin
```

> **Note:** `X-XSS-Protection` is set to `0` (disabled) because modern browsers have deprecated the built-in XSS auditor. Using `1; mode=block` can actually introduce vulnerabilities. Use Content-Security-Policy (CSP) instead.

### Additional Headers (Optional)

```php
// Add in middleware for enhanced protection
header("Content-Security-Policy: default-src 'self'");
```

### Best Practices

✅ **DO:**

- Keep all default security headers enabled
- Add Content-Security-Policy for stricter protection
- Use HSTS in production with HTTPS

❌ **DON'T:**

- Remove or override security headers without understanding their purpose
- Use `X-XSS-Protection: 1; mode=block` (deprecated, can cause vulnerabilities)
- Disable `X-Frame-Options` unless you need to embed your API in iframes

---

## File Upload Security (v2.0.2)

### Built-in Protections

The `Core\File` class now includes multiple layers of upload security:

```php
use Wibiesana\Padi\Core\File;

// ✅ SAFE - Extension whitelist + blacklist + MIME check
$path = File::upload($_FILES['document'], 'documents', ['jpg', 'png', 'pdf']);
```

### Security Layers

1. **Extension Blacklist** — Blocks dangerous extensions (`.php`, `.phar`, `.exe`, `.sh`, etc.) regardless of whitelist
2. **Extension Whitelist** — Only allows specified file types when provided
3. **MIME Type Verification** — Uses `finfo` to verify file content matches extension
4. **Path Traversal Protection** — `sanitizePath()` removes `..`, null bytes, and normalizes separators
5. **Secure Filenames** — Uses `bin2hex(random_bytes(16))` (32-character hex) instead of `uniqid()`
6. **Delete Verification** — `realpath()` check ensures deletion stays within uploads directory
7. **Directory Permissions** — `0750` (owner: rwx, group: rx, others: none)

### Dangerous Extension Blacklist

The following extensions are **always blocked**, even if listed in `$allowedTypes`:

```
php, phtml, phar, php3, php4, php5, php7, php8, phps
cgi, pl, asp, aspx, shtml, htaccess
sh, bat, cmd, com, exe, dll, msi
py, rb, js, jsp, war
```

### Best Practices

✅ **DO:**

- Always specify an extension whitelist (`$allowedTypes`)
- Validate file size limits appropriate for your use case
- Store files outside the web root when possible
- Use the built-in `File::upload()` instead of manual `move_uploaded_file()`

❌ **DON'T:**

- Trust the original filename from the client
- Trust the `$_FILES['type']` MIME (it's client-controlled)
- Allow executable file extensions under any circumstance
- Use `0777` directory permissions for upload folders

---

## Cache Security (v2.0.2)

### Object Injection Prevention

**Before (v2.0.1):** File cache used `serialize()`/`unserialize()` which is vulnerable to PHP Object Injection attacks.

**After (v2.0.2):** File cache uses `json_encode()`/`json_decode()` which cannot instantiate PHP objects.

```php
// ✅ SAFE - JSON-based serialization (v2.0.2)
Cache::set('user', ['name' => 'John'], 3600);

// File content: {"key":"user","value":{"name":"John"},"expires":1735000000}
```

### Atomic Writes

Cache files are now written atomically (temp file + rename) to prevent partial reads under concurrent access.

### Best Practices

✅ **DO:**

- Only cache JSON-serializable data (arrays, strings, numbers, booleans)
- Set appropriate TTL for sensitive data (short expiry)
- Use Redis in production for better isolation and performance

❌ **DON'T:**

- Cache user passwords or secret tokens
- Use `serialize()`/`unserialize()` for custom cache implementations
- Set `0777` permissions on cache directories

---

## CSRF (Cross-Site Request Forgery) Protection

### How It Works

- **Stateless JWT** - No cookies, no CSRF vulnerability
- **Token-based authentication** - Requires explicit Authorization header

### Why It's Safe

```javascript
// ❌ CSRF vulnerable (cookie-based)
// Cookies sent automatically with every request

// ✅ CSRF safe (JWT in header)
axios.get("/api/products", {
  headers: {
    Authorization: `Bearer ${token}`, // Explicit, not automatic
  },
});
```

---

## Password Security

### Password Requirements

- ✅ Minimum 8 characters
- ✅ At least 1 uppercase letter
- ✅ At least 1 lowercase letter
- ✅ At least 1 number
- ✅ At least 1 special character (@$!%\*?&#)

### Password Hashing

```php
// Hashing (registration)
$hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

// Verification (login)
if (password_verify($inputPassword, $hashedPassword)) {
    // Password correct
}
```

### Best Practices

✅ **DO:**

- Use `password_hash()` with `PASSWORD_BCRYPT` (cost 10+)
- Enforce strong password requirements
- Implement password reset with email verification
- Rate limit login attempts

❌ **DON'T:**

- Store plain text passwords
- Use weak hashing (MD5, SHA1)
- Allow weak or common passwords
- Log passwords in any form

---

## JWT Security

### Strong JWT Secret

```bash
# Generate 64-character random secret
php -r "echo bin2hex(random_bytes(32));"
```

### JWT Configuration

```env
JWT_SECRET=<64-character-random-secret>
JWT_ALGORITHM=HS256
JWT_EXPIRY=3600
```

### Built-in Security Checks (v2.0.2)

The framework automatically validates JWT secrets at startup:

- ❌ Rejects secrets shorter than 32 characters
- ❌ Rejects common weak secrets (`secret`, `change-this`, `your-secret-key`, etc.)
- ✅ Pre-creates `Key` object once for efficient verification
- ✅ Adds `nbf` (not-before) claim to generated tokens
- ✅ Quick JWT format validation before expensive decode

### Best Practices

✅ **DO:**

- Use 64+ character random secret (`bin2hex(random_bytes(32))`)
- Use different secrets for dev/staging/production
- Set appropriate expiry (1-24 hours)
- Implement token refresh for long sessions
- Validate tokens on every protected request

❌ **DON'T:**

- Use weak secrets (`"secret"`, `"password"`, `"change-this"`)
- Share secrets between environments
- Set very long expiry (> 24 hours) without refresh
- Store JWT_SECRET in source code
- Commit `.env` to version control

---

## CORS Security

### Development Configuration

```env
APP_ENV=development
CORS_ALLOWED_ORIGINS=
```

**Empty = Allow all origins** (for local development only)

### Production Configuration

```env
APP_ENV=production
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com
```

**Comma-separated list** of allowed origins

### CORS Headers (v2.0.2)

```
Access-Control-Allow-Origin: <matched origin>
Access-Control-Allow-Credentials: true
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Response-Format, Accept, Origin
Access-Control-Max-Age: 86400
Vary: Origin
```

The `Vary: Origin` header ensures proper caching of CORS responses by proxies and CDNs.

### Best Practices

✅ **DO:**

- Specify exact domains in production
- Use HTTPS origins only
- Limit to necessary domains

❌ **DON'T:**

- Allow all origins in production
- Use wildcard (\*) in production
- Allow HTTP origins in production

---

## Input Validation

### Validation Rules

```php
protected function getValidationRules(): array
{
    return [
        'email' => 'required|email',
        'password' => 'required|min:8',
        'name' => 'required|string|max:255',
        'age' => 'required|numeric|min:18|max:120',
        'status' => 'required|in:active,inactive',
        'user_id' => 'required|exists:users,id',
        'tags' => 'array',              // NEW in v2.0.2
        'phone' => 'regex:/^\+[0-9]+$/' // NEW in v2.0.2
    ];
}
```

### Available Rules

| Rule                  | Example              | Description                     |
| --------------------- | -------------------- | ------------------------------- |
| `required`            | `required`           | Field must be present           |
| `email`               | `email`              | Must be valid email             |
| `numeric`             | `numeric`            | Must be number                  |
| `integer`             | `integer`            | Must be integer **(v2.0.2)**    |
| `min:n`               | `min:8`              | Minimum length (mb_strlen)      |
| `max:n`               | `max:255`            | Maximum length (mb_strlen)      |
| `in:a,b`              | `in:active,inactive` | Must be one of values           |
| `exists:table,column` | `exists:users,id`    | Must exist in table             |
| `unique:table,column` | `unique:users,email` | Must be unique                  |
| `confirmed`           | `confirmed`          | Must match \_confirmation field |
| `date`                | `date`               | Must be valid date              |
| `boolean`             | `boolean`            | Must be true/false/0/1          |
| `array`               | `array`              | Must be array **(v2.0.2)**      |
| `regex:pattern`       | `regex:/^[A-Z]+$/`   | Must match regex **(v2.0.2)**   |
| `nullable`            | `nullable`           | Allows null values **(v2.0.2)** |

### Best Practices

✅ **DO:**

- Validate **all** user input on every endpoint
- Use strict validation rules (`required`, `email`, `min`, `max`)
- Return clear, actionable validation error messages
- Use `unique` rule for fields that must be unique (email, username)

❌ **DON'T:**

- Trust user input without validation
- Skip validation on update endpoints
- Use overly permissive rules (e.g., only `required` for an email field)
- Expose internal field names in validation errors

---

## Database Security

### Connection Security (v2.0.2)

```php
// Automatically configured:
PDO::ATTR_EMULATE_PREPARES => false,  // Real prepared statements
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_STRINGIFY_FETCHES => false, // Preserve data types
```

### Strong Database Password

```env
DB_USER=api_user
DB_PASS=<strong-random-password>
```

### Database User Permissions

```sql
-- Create dedicated database user
CREATE USER 'api_user'@'localhost' IDENTIFIED BY 'strong_password';

-- Grant only necessary permissions
GRANT SELECT, INSERT, UPDATE, DELETE ON rest_api_db.* TO 'api_user'@'localhost';

-- Don't grant:
-- - DROP (can delete tables)
-- - CREATE (can create tables)
-- - ALTER (can modify schema)
```

### Best Practices

✅ **DO:**

- Use strong, randomly generated database password
- Create a dedicated database user for the application
- Grant only `SELECT, INSERT, UPDATE, DELETE` permissions
- Use different credentials for dev/staging/production
- Enable SSL for database connections in production

❌ **DON'T:**

- Use `root` user for the application
- Use weak or empty passwords
- Grant `ALL PRIVILEGES` or `DROP`/`CREATE`/`ALTER`
- Share database credentials across environments
- Expose database credentials in logs or error messages

---

## Directory Permissions (v2.0.2)

All directories created by the framework use restrictive permissions:

| Directory        | Permission | Description           |
| ---------------- | ---------- | --------------------- |
| `storage/cache/` | `0750`     | Cache files           |
| `storage/logs/`  | `0750`     | Log files             |
| `uploads/`       | `0750`     | Uploaded files        |
| SQLite DB dir    | `0750`     | SQLite database files |

> **Changed from `0777` to `0750`** — Only the owner can write, group can read/execute, others have no access.

---

## Rate Limiting

### Configuration

```env
RATE_LIMIT_MAX=60
RATE_LIMIT_WINDOW=60
```

- **60 requests per minute** per IP address
- Returns `429 Too Many Requests` when exceeded

### Best Practices

✅ **DO:**

- Set appropriate limits for your use case
- Monitor rate limit violations in logs
- Consider different limits for different endpoints (e.g., stricter for login)

❌ **DON'T:**

- Set limits too high (allows brute-force attacks)
- Set limits too low (frustrates legitimate users)
- Disable rate limiting in production

---

## Error Handling

### Production Error Handling

```env
APP_ENV=production
APP_DEBUG=false
```

**Never expose:**

- Stack traces
- Database errors
- File paths
- Internal implementation details

### Error Response Format

```json
{
  "success": false,
  "message": "An error occurred",
  "message_code": "INTERNAL_SERVER_ERROR"
}
```

### Best Practices

✅ **DO:**

- Log all errors server-side with `Logger::error()`
- Return generic error messages to clients in production
- Use appropriate HTTP status codes (400, 401, 403, 404, 422, 500)
- Monitor error logs regularly

❌ **DON'T:**

- Expose stack traces to end users
- Return raw database error messages
- Show file paths or internal class names
- Set `APP_DEBUG=true` in production

---

## Security Audit Checklist

### Application Security

- [ ] APP_ENV=production
- [ ] APP_DEBUG=false
- [ ] Strong JWT_SECRET (64+ chars)
- [ ] CORS configured (specific domains)
- [ ] HTTPS enabled
- [ ] Rate limiting active
- [ ] Input validation on all endpoints
- [ ] Error logging enabled
- [ ] File upload restrictions configured
- [ ] Directory permissions verified (0750)

### Database Security

- [ ] Strong database password
- [ ] Dedicated database user
- [ ] Minimum permissions
- [ ] Emulated prepares disabled
- [ ] Regular backups
- [ ] Connection encryption (SSL)

### Infrastructure Security

- [ ] Firewall configured
- [ ] SSH key authentication
- [ ] Regular security updates
- [ ] Monitoring enabled
- [ ] Backup strategy

---

## Next Steps

1. **Production Deployment** - [../04-deployment/PRODUCTION.md](../04-deployment/PRODUCTION.md)
2. **Frontend Integration** - [FRONTEND_INTEGRATION.md](FRONTEND_INTEGRATION.md)
3. **API Testing** - [API_TESTING.md](API_TESTING.md)

---

**Previous:** [← Frontend Integration](FRONTEND_INTEGRATION.md) | **Next:** [Production Deployment →](../04-deployment/PRODUCTION.md)
