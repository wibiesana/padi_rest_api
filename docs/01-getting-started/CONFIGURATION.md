# ⚙️ Configuration Guide

**Padi REST API Framework v1.0.0**

---

## Environment Variables (.env)

The `.env` file contains all configuration settings for your application.

---

## Development Configuration

### Complete Development .env

```env
# Application
APP_NAME="Padi REST API"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8085

# Debug Options
DEBUG_SHOW_QUERIES=true
ENABLE_COMPRESSION=true

# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=rest_api_db
DB_USER=root
DB_PASS=your_password

# Security
JWT_SECRET=<your-64-char-random-secret>
JWT_ALGORITHM=HS256
JWT_EXPIRY=3600

# CORS (leave empty for development = allow all)
CORS_ALLOWED_ORIGINS=

# Rate Limiting
RATE_LIMIT_MAX=60
RATE_LIMIT_WINDOW=60
```

### Development Settings Explained

| Variable               | Value         | Description                        |
| ---------------------- | ------------- | ---------------------------------- |
| `APP_ENV`              | `development` | Enables detailed error messages    |
| `APP_DEBUG`            | `true`        | Shows stack traces and debug info  |
| `DEBUG_SHOW_QUERIES`   | `true`        | Logs SQL queries in response       |
| `CORS_ALLOWED_ORIGINS` | _(empty)_     | Allows all origins (for local dev) |

---

## Production Configuration

### Complete Production .env

```env
# Application
APP_NAME="Padi REST API"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

# Debug Options
DEBUG_SHOW_QUERIES=false
ENABLE_COMPRESSION=true

# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=rest_api_db
DB_USER=api_user
DB_PASS=strong_random_password_here

# Security
JWT_SECRET=<different-secret-for-production>
JWT_ALGORITHM=HS256
JWT_EXPIRY=3600

# CORS (specify exact domains)
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com

# Rate Limiting
RATE_LIMIT_MAX=60
RATE_LIMIT_WINDOW=60
```

### Production Settings Explained

| Variable               | Value            | Description                      |
| ---------------------- | ---------------- | -------------------------------- |
| `APP_ENV`              | `production`     | Disables detailed error messages |
| `APP_DEBUG`            | `false`          | Hides stack traces from users    |
| `DEBUG_SHOW_QUERIES`   | `false`          | Prevents query exposure          |
| `CORS_ALLOWED_ORIGINS` | Specific domains | Restricts API access             |

---

## Configuration Sections

### 1. Application Settings

```env
APP_NAME="Padi REST API"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8085
```

- **APP_NAME**: Application name (used in responses)
- **APP_ENV**: Environment (`development` or `production`)
- **APP_DEBUG**: Enable/disable debug mode
- **APP_URL**: Base URL of your API

### 2. Debug Options

```env
DEBUG_SHOW_QUERIES=true
ENABLE_COMPRESSION=true
```

- **DEBUG_SHOW_QUERIES**: Show SQL queries in API responses
- **ENABLE_COMPRESSION**: Enable Gzip compression

### 3. Database Configuration

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=rest_api_db
DB_USER=root
DB_PASS=your_password
```

- **DB_HOST**: Database server hostname
- **DB_PORT**: Database port (3306 for MySQL)
- **DB_NAME**: Database name
- **DB_USER**: Database username
- **DB_PASS**: Database password

### 4. JWT Security

```env
JWT_SECRET=<your-64-char-random-secret>
JWT_ALGORITHM=HS256
JWT_EXPIRY=3600
```

- **JWT_SECRET**: Secret key for JWT signing (64 characters)
- **JWT_ALGORITHM**: Signing algorithm (HS256 recommended)
- **JWT_EXPIRY**: Token expiry time in seconds (3600 = 1 hour)

### 5. CORS Configuration

```env
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com
```

- **Empty**: Allow all origins (development only)
- **Comma-separated**: Specific allowed origins (production)

### 6. Rate Limiting

```env
RATE_LIMIT_MAX=60
RATE_LIMIT_WINDOW=60
```

- **RATE_LIMIT_MAX**: Maximum requests allowed
- **RATE_LIMIT_WINDOW**: Time window in seconds

---

## Generate Strong JWT Secret

### Method 1: PHP Command

```bash
php -r "echo bin2hex(random_bytes(32));"
```

### Method 2: OpenSSL

```bash
openssl rand -hex 32
```

### Method 3: Online Generator

Use a secure random string generator (64 characters minimum)

**⚠️ Important:**

- Never use the same secret in development and production
- Never commit `.env` to version control
- Store production secrets securely

---

## Environment-Specific Best Practices

### Development

✅ **DO:**

- Use `APP_DEBUG=true` for detailed errors
- Leave `CORS_ALLOWED_ORIGINS` empty
- Use `DEBUG_SHOW_QUERIES=true` for debugging

❌ **DON'T:**

- Use production database
- Use production JWT secret
- Commit `.env` file

### Production

✅ **DO:**

- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Specify exact CORS origins
- Use strong database password
- Generate new JWT secret

❌ **DON'T:**

- Enable debug mode
- Allow all CORS origins
- Use weak passwords
- Expose SQL queries

---

## Configuration Validation

### Check Current Configuration

```bash
# View current environment
php -r "echo getenv('APP_ENV');"

# Test database connection
php scripts/test_db.php
```

### Common Configuration Issues

| Issue                      | Solution                                    |
| -------------------------- | ------------------------------------------- |
| Database connection failed | Check DB credentials in `.env`              |
| JWT token invalid          | Verify JWT_SECRET is set                    |
| CORS errors                | Add frontend domain to CORS_ALLOWED_ORIGINS |
| Rate limit too strict      | Increase RATE_LIMIT_MAX                     |

---

## Next Steps

1. **First Steps** - See [FIRST_STEPS.md](FIRST_STEPS.md)
2. **Database Setup** - See [../02-core-concepts/DATABASE_SETUP.md](../02-core-concepts/DATABASE_SETUP.md)
3. **Security Best Practices** - See [../03-advanced/SECURITY.md](../03-advanced/SECURITY.md)

---

**Previous:** [← Installation Guide](INSTALLATION.md) | **Next:** [First Steps →](FIRST_STEPS.md)
