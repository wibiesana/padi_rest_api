# 🔒 Security Best Practices

**Padi REST API Framework v1.0.0**

---

## Security Overview

### Security Score: 9.0/10 🛡️

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

| Feature                      | Status | Description                                 |
| ---------------------------- | ------ | ------------------------------------------- |
| **SQL Injection Protection** | ✅     | PDO prepared statements + column validation |
| **XSS Protection**           | ✅     | X-XSS-Protection header                     |
| **CSRF Protection**          | ✅     | Stateless JWT (no cookies)                  |
| **Clickjacking Protection**  | ✅     | X-Frame-Options: DENY                       |
| **MIME Sniffing Protection** | ✅     | X-Content-Type-Options: nosniff             |
| **Password Hashing**         | ✅     | Bcrypt with cost 10                         |
| **Rate Limiting**            | ✅     | 60 requests/minute per IP                   |
| **CORS Whitelist**           | ✅     | Environment-based configuration             |
| **HTTPS Enforcement**        | ✅     | HSTS header (production)                    |
| **Input Validation**         | ✅     | Required for all endpoints                  |

---

## SQL Injection Protection

### How It Works

1. **PDO Prepared Statements** - All queries use parameterized statements
2. **Column Name Validation** - Validates column names against table schema
3. **Input Sanitization** - Automatic sanitization of user input

### Example

```php
// ✅ SAFE - Uses prepared statements
$products = Query::table('products')
    ->where('status', '=', $userInput)
    ->get();

// ✅ SAFE - Validates column names
$products = Query::table('products')
    ->orderBy($userColumn, 'DESC')  // Validated against schema
    ->get();

// ❌ UNSAFE - Raw SQL (avoid)
$sql = "SELECT * FROM products WHERE status = '$userInput'";
```

---

## XSS (Cross-Site Scripting) Protection

### Security Headers

```
X-XSS-Protection: 1; mode=block
X-Content-Type-Options: nosniff
```

### Input Sanitization

```php
// Automatic HTML escaping in responses
$this->jsonResponse([
    'name' => htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8')
]);
```

### Best Practices

✅ **DO:**

- Validate and sanitize all user input
- Use JSON responses (automatic escaping)
- Set proper Content-Type headers

❌ **DON'T:**

- Trust user input
- Render HTML from user data without escaping
- Disable XSS protection headers

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

- Use bcrypt with cost 10+
- Enforce strong password requirements
- Implement password reset with email verification
- Rate limit login attempts

❌ **DON'T:**

- Store plain passwords
- Use weak hashing (MD5, SHA1)
- Allow weak passwords
- Log passwords

---

## JWT Security

### Strong JWT Secret

```bash
# Generate 64-character random secret
php -r "echo bin2hex(random_bytes(32));"

# Example output:
# 9a6d4f7ebe57a4ebd702e6108f4e5bd1722fa2812ae4b9ae696ce68739e06b18b
```

### JWT Configuration

```env
JWT_SECRET=<64-character-random-secret>
JWT_ALGORITHM=HS256
JWT_EXPIRY=3600
```

### Best Practices

✅ **DO:**

- Use 64+ character random secret
- Use different secrets for dev/staging/production
- Set appropriate expiry (1-24 hours)
- Implement token refresh
- Validate tokens on every request

❌ **DON'T:**

- Use weak secrets ("secret", "password")
- Share secrets between environments
- Set very long expiry (> 24 hours)
- Store JWT_SECRET in code
- Commit .env to version control

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

## Rate Limiting

### Configuration

```env
RATE_LIMIT_MAX=60
RATE_LIMIT_WINDOW=60
```

- **60 requests per minute** per IP address
- Returns `429 Too Many Requests` when exceeded

### Adjust for Production

```env
# Stricter limits for production
RATE_LIMIT_MAX=30
RATE_LIMIT_WINDOW=60

# Or per endpoint (custom implementation)
```

### Best Practices

✅ **DO:**

- Set appropriate limits for your use case
- Monitor rate limit violations
- Implement different limits for different endpoints

❌ **DON'T:**

- Set limits too high (allows abuse)
- Set limits too low (frustrates users)
- Disable rate limiting in production

---

## HTTPS Enforcement

### Enable HTTPS

```env
APP_ENV=production
APP_URL=https://api.yourdomain.com
```

### HSTS Header

```
Strict-Transport-Security: max-age=31536000; includeSubDomains
```

Forces browsers to use HTTPS for 1 year.

### Best Practices

✅ **DO:**

- Use HTTPS in production
- Enable HSTS header
- Use valid SSL/TLS certificate
- Redirect HTTP to HTTPS

❌ **DON'T:**

- Use HTTP in production
- Use self-signed certificates in production
- Allow mixed content (HTTP + HTTPS)

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
        'user_id' => 'required|exists:users,id'
    ];
}
```

### Available Rules

| Rule                  | Example              | Description           |
| --------------------- | -------------------- | --------------------- |
| `required`            | `required`           | Field must be present |
| `email`               | `email`              | Must be valid email   |
| `numeric`             | `numeric`            | Must be number        |
| `string`              | `string`             | Must be string        |
| `min:n`               | `min:8`              | Minimum length/value  |
| `max:n`               | `max:255`            | Maximum length/value  |
| `in:a,b`              | `in:active,inactive` | Must be one of values |
| `exists:table,column` | `exists:users,id`    | Must exist in table   |
| `unique:table,column` | `unique:users,email` | Must be unique        |

### Best Practices

✅ **DO:**

- Validate all user input
- Use strict validation rules
- Sanitize input before processing
- Return clear validation errors

❌ **DON'T:**

- Trust user input
- Skip validation
- Use weak validation rules

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
  "errors": {
    "field": ["Validation error"]
  }
}
```

### Best Practices

✅ **DO:**

- Log errors server-side
- Return generic error messages
- Use appropriate HTTP status codes
- Monitor error logs

❌ **DON'T:**

- Expose stack traces to users
- Return database errors
- Show file paths
- Ignore errors

---

## Database Security

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

- Use strong database password
- Create dedicated database user
- Grant minimum necessary permissions
- Use different credentials for dev/production

❌ **DON'T:**

- Use root user
- Use weak passwords
- Grant all permissions
- Share database credentials

---

## File Upload Security

### Validation

```php
// Validate file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($_FILES['file']['type'], $allowedTypes)) {
    throw new Exception('Invalid file type');
}

// Validate file size (5MB max)
$maxSize = 5 * 1024 * 1024;
if ($_FILES['file']['size'] > $maxSize) {
    throw new Exception('File too large');
}

// Generate unique filename
$filename = bin2hex(random_bytes(16)) . '.' . $extension;
```

### Best Practices

✅ **DO:**

- Validate file type and size
- Generate unique filenames
- Store files outside web root
- Scan for malware

❌ **DON'T:**

- Trust file extensions
- Use original filenames
- Store in public directory
- Allow executable files

---

## API Key Security

### Environment Variables

```env
# Never hardcode API keys
STRIPE_API_KEY=sk_live_...
SENDGRID_API_KEY=SG...
```

### Best Practices

✅ **DO:**

- Store API keys in .env
- Use different keys for dev/production
- Rotate keys regularly
- Monitor API key usage

❌ **DON'T:**

- Hardcode API keys
- Commit keys to version control
- Share keys
- Use same keys across environments

---

## Security Headers

### Automatically Added Headers

```
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000; includeSubDomains
```

### Additional Headers (Optional)

```php
// Add in middleware
header('Content-Security-Policy: default-src \'self\'');
header('Referrer-Policy: no-referrer');
header('Permissions-Policy: geolocation=(), microphone=()');
```

---

## Security Monitoring

### What to Monitor

- Failed login attempts
- Rate limit violations
- Validation errors
- Database errors
- Unusual API usage patterns

### Logging

```php
// Log security events
error_log("Failed login attempt: " . $email);
error_log("Rate limit exceeded: " . $ip);
error_log("Validation failed: " . json_encode($errors));
```

---

## Security Audit Checklist

### Application Security

- [ ] APP_ENV=production
- [ ] APP_DEBUG=false
- [ ] Strong JWT_SECRET
- [ ] CORS configured
- [ ] HTTPS enabled
- [ ] Rate limiting active
- [ ] Input validation on all endpoints
- [ ] Error logging enabled

### Database Security

- [ ] Strong database password
- [ ] Dedicated database user
- [ ] Minimum permissions
- [ ] Regular backups
- [ ] Connection encryption

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
