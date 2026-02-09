# 🔧 Troubleshooting Guide

**Padi REST API Framework v1.0.0**

---

## Common Issues

### Installation Issues

#### Issue: Composer install fails

**Symptoms:**

```
Could not find package...
```

**Solutions:**

```bash
# Update composer
composer self-update

# Clear cache
composer clear-cache

# Install with verbose output
composer install -vvv
```

#### Issue: PHP extensions missing

**Symptoms:**

```
Extension pdo_mysql is missing
```

**Solutions:**

```bash
# Ubuntu/Debian
sudo apt install php8.1-mysql php8.1-mbstring php8.1-xml

# Check installed extensions
php -m
```

---

### Database Issues

#### Issue: Database connection failed

**Symptoms:**

```json
{
  "success": false,
  "message": "Database connection failed"
}
```

**Solutions:**

1. Check `.env` configuration:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=rest_api_db
DB_USER=root
DB_PASS=your_password
```

2. Verify MySQL is running:

```bash
# Linux
sudo systemctl status mysql

# Windows
net start MySQL
```

3. Test connection:

```bash
mysql -u root -p -h localhost
```

#### Issue: Table doesn't exist

**Symptoms:**

```
Table 'rest_api_db.products' doesn't exist
```

**Solutions:**

1. Import schema:

```bash
mysql -u root -p rest_api_db < database/schema.sql
```

2. Run migrations:

```bash
php scripts/migrate.php migrate
```

3. Check table exists:

```bash
mysql -u root -p -e "SHOW TABLES FROM rest_api_db;"
```

---

### Authentication Issues

#### Issue: 401 Unauthorized

**Symptoms:**

```json
{
  "success": false,
  "message": "Unauthorized"
}
```

**Solutions:**

1. Check token is sent:

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8085/auth/me
```

2. Verify token is valid:
   - Token not expired
   - JWT_SECRET matches
   - Token format: `Bearer <token>`

3. Check JWT_SECRET in `.env`:

```env
JWT_SECRET=<64-character-secret>
```

#### Issue: Invalid JWT signature

**Symptoms:**

```json
{
  "success": false,
  "message": "Invalid token signature"
}
```

**Solutions:**

1. Verify JWT_SECRET matches between environments
2. Generate new token:

```bash
curl -X POST http://localhost:8085/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'
```

#### Issue: Token expired

**Symptoms:**

```json
{
  "success": false,
  "message": "Token has expired"
}
```

**Solutions:**

1. Login again to get new token
2. Increase JWT_EXPIRY in `.env`:

```env
JWT_EXPIRY=7200  # 2 hours
```

3. Implement token refresh:

```bash
curl -X POST http://localhost:8085/auth/refresh \
  -H "Authorization: Bearer OLD_TOKEN"
```

---

### Validation Issues

#### Issue: 422 Validation Error

**Symptoms:**

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required"]
  }
}
```

**Solutions:**

1. Check request body matches validation rules
2. Verify Content-Type header:

```bash
curl -X POST http://localhost:8085/products \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{"name":"Product","price":99.99}'
```

3. Review validation rules in controller:

```php
protected function getValidationRules(): array
{
    return [
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0'
    ];
}
```

---

### CORS Issues

#### Issue: CORS policy error

**Symptoms:**

```
Access to XMLHttpRequest blocked by CORS policy
```

**Solutions:**

1. **Development:** Allow all origins

```env
APP_ENV=development
CORS_ALLOWED_ORIGINS=
```

2. **Production:** Add frontend domain

```env
APP_ENV=production
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com
```

3. Verify frontend URL matches exactly (including protocol)

4. Check browser console for exact error

---

### Rate Limiting Issues

#### Issue: 429 Too Many Requests

**Symptoms:**

```json
{
  "success": false,
  "message": "Too many requests"
}
```

**Solutions:**

1. Wait for rate limit window to reset
2. Increase rate limit in `.env`:

```env
RATE_LIMIT_MAX=120
RATE_LIMIT_WINDOW=60
```

3. Check rate limit headers:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1706000000
```

---

### File Permission Issues

#### Issue: Permission denied

**Symptoms:**

```
Warning: file_put_contents(): Permission denied
```

**Solutions:**

**Linux/Unix:**

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/api

# Set permissions
sudo chmod -R 755 /var/www/api
sudo chmod -R 775 /var/www/api/storage
```

**Windows:**

```powershell
# Grant IIS_IUSRS permissions
icacls "D:\inetpub\api\storage" /grant IIS_IUSRS:(OI)(CI)F /T
```

---

### Code Generator Issues

#### Issue: Files not created

**Symptoms:**

```
Generated successfully
# But files don't exist
```

**Solutions:**

1. Add `--write` flag:

```bash
php scripts/generate.php crud products --write
```

2. Check file permissions:

```bash
ls -la app/Models/
ls -la app/Controllers/
```

#### Issue: Base files not updated

**Symptoms:**

```
# New columns not in $fillable
```

**Solutions:**

1. Add `--overwrite` flag:

```bash
php scripts/generate.php crud products --write --overwrite
```

2. Manually update base model if needed

---

### Server Issues

#### Issue: 500 Internal Server Error

**Symptoms:**

```
500 Internal Server Error
```

**Solutions:**

1. Check error logs:

```bash
# Apache
tail -f /var/log/apache2/error.log

# NGINX
tail -f /var/log/nginx/error.log

# PHP
tail -f /var/log/php8.1-fpm.log
```

2. Enable debug mode temporarily:

```env
APP_DEBUG=true
DEBUG_SHOW_QUERIES=true
```

3. Check `.htaccess` or NGINX config

#### Issue: 404 Not Found

**Symptoms:**

```
404 Not Found
```

**Solutions:**

1. Verify route exists in `routes/api.php`:

```php
$router->get('/products', [ProductController::class, 'index']);
```

2. Check URL is correct:

```bash
# Correct
curl http://localhost:8085/products

# Incorrect
curl http://localhost:8085/api/products
```

3. Verify mod_rewrite is enabled (Apache):

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

### Performance Issues

#### Issue: Slow API responses

**Symptoms:**

```
Response time > 1 second
```

**Solutions:**

1. Enable query debugging:

```env
DEBUG_SHOW_QUERIES=true
```

2. Add database indexes:

```sql
CREATE INDEX idx_status ON products(status);
```

3. Use FrankenPHP worker mode:

```bash
./frankenphp php-server --worker public/frankenphp-worker.php
```

See [FRANKENPHP_SETUP.md](FRANKENPHP_SETUP.md)

4. Enable OPcache:

```ini
opcache.enable=1
opcache.memory_consumption=128
```

---

### SSL/HTTPS Issues

#### Issue: SSL certificate error

**Symptoms:**

```
NET::ERR_CERT_AUTHORITY_INVALID
```

**Solutions:**

1. Renew Let's Encrypt certificate:

```bash
sudo certbot renew
```

2. Verify certificate is valid:

```bash
sudo certbot certificates
```

3. Check certificate files exist:

```bash
ls -la /etc/letsencrypt/live/api.yourdomain.com/
```

---

## Debugging Tools

### Enable Debug Mode

```env
APP_ENV=development
APP_DEBUG=true
DEBUG_SHOW_QUERIES=true
```

**⚠️ Never enable in production!**

### Check PHP Configuration

```bash
php -i | grep -E "pdo|mbstring|openssl"
```

### Test Database Connection

```bash
php -r "
\$pdo = new PDO('mysql:host=localhost;dbname=rest_api_db', 'root', 'password');
echo 'Connected successfully';
"
```

### Test JWT Generation

```bash
php -r "
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
\$payload = ['user_id' => 1];
\$token = JWT::encode(\$payload, 'secret', 'HS256');
echo \$token;
"
```

---

## Getting Help

### 1. Check Documentation

- [README.md](../README.md) - Complete reference
- [INDEX.md](../INDEX.md) - Documentation index
- Specific guides in `docs/` folder

### 2. Check Logs

```bash
# Application logs
tail -f storage/logs/app.log

# Web server logs
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log

# PHP logs
tail -f /var/log/php8.1-fpm.log
```

### 3. Enable Verbose Output

```bash
# Composer
composer install -vvv

# cURL
curl -v http://localhost:8085/products
```

---

## Quick Fixes

### Reset Everything

```bash
# 1. Drop and recreate database
mysql -u root -p -e "DROP DATABASE rest_api_db; CREATE DATABASE rest_api_db;"

# 2. Import schema
mysql -u root -p rest_api_db < database/schema.sql

# 3. Clear cache
rm -rf storage/cache/*

# 4. Reinstall dependencies
rm -rf vendor
composer install

# 5. Restart server
sudo systemctl restart apache2
# or
sudo systemctl restart nginx php8.1-fpm
```

### Clear Cache

```bash
# Clear all cache
rm -rf storage/cache/*

# Clear specific cache
rm -rf storage/cache/ratelimit/*
```

---

## Prevention Tips

### 1. Always Use Version Control

```bash
git init
git add .
git commit -m "Initial commit"
```

### 2. Keep Backups

```bash
# Database backup
mysqldump -u root -p rest_api_db > backup.sql

# File backup
tar -czf backup.tar.gz /var/www/api
```

### 3. Test Before Deploying

```bash
# Run tests
php scripts/test.php

# Check syntax
php -l app/Controllers/ProductController.php
```

### 4. Monitor Logs

```bash
# Watch logs in real-time
tail -f /var/log/apache2/error.log
```

---

## Next Steps

1. **Production Deployment** - [PRODUCTION.md](PRODUCTION.md)
2. **Security Best Practices** - [../03-advanced/SECURITY.md](../03-advanced/SECURITY.md)
3. **Complete Reference** - [../README.md](../README.md)

---

**Previous:** [← Production Deployment](PRODUCTION.md) | **Next:** [Complete Reference →](../README.md)
