# 🚀 Production Deployment Guide

**Padi REST API Framework v1.0.0**

A comprehensive guide and checklist for deploying Padi REST API Framework to production safely, securely, and with maximum performance.

---

## 📋 Pre-Deployment Checklist

Before going live, ensure every item on this list is completed:

### 1. Environment & Configuration

- [ ] `APP_ENV=production` set in `.env`
- [ ] `APP_DEBUG=false` (Critical for security)
- [ ] `DEBUG_SHOW_QUERIES=false`
- [ ] `APP_URL` updated to production domain (e.g., `https://api.yourdomain.com`)
- [ ] `JWT_SECRET` regenerated with 64+ random characters
- [ ] `CORS_ALLOWED_ORIGINS` configured with specific production domains
- [ ] `.env` file secured with appropriate permissions (`chmod 600`)

### 2. Security

- [ ] SSL/TLS certificate installed and HTTPS forced
- [ ] Security headers (X-Frame-Options, X-Content-Type-Options, etc.) configured
- [ ] Rate limiting enabled and tested (e.g., 30-60 req/min)
- [ ] Database user has minimal necessary privileges (avoid using `root`)
- [ ] Sensitive files protected from public access (via `.htaccess` or server config)

### 3. Database

- [ ] Production database created with `utf8mb4_unicode_ci` collation
- [ ] All migrations run successfully
- [ ] Automated daily database backups configured and tested
- [ ] Database indexes added to frequently queried columns

### 4. Performance & Optimization

- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] PHP OPcache enabled and configured
- [ ] Redis configured for caching (if using high traffic)
- [ ] Response compression (`ENABLE_COMPRESSION=true`) enabled

---

## Server Requirements

- **PHP 8.1+** (PHP 8.2+ recommended)
- **Composer** (Latest stable)
- **MySQL 5.7+** / **MariaDB 10.3+**
- **Web Server** (Apache, NGINX, or [FrankenPHP](FRANKENPHP_SETUP.md))
- **SSL/TLS Certificate** (Let's Encrypt, Cloudflare, etc.)

---

## Deployment Methods

### 1. FrankenPHP (Recommended)

**3-10x faster than traditional PHP-FPM.** FrankenPHP's Worker mode keeps the application in memory, delivering incredible performance.

See the [FrankenPHP Setup Guide](FRANKENPHP_SETUP.md) for full details.

### 2. NGINX + PHP-FPM

**Example configuration:** `/etc/nginx/sites-available/api`

```nginx
server {
    listen 80;
    server_name api.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name api.yourdomain.com;
    root /var/www/api/public;

    ssl_certificate /etc/letsencrypt/live/api.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.yourdomain.com/privkey.pem;

    index index.php;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 3. Apache

**Example Configuration:**

```apache
<VirtualHost *:443>
    ServerName api.yourdomain.com
    DocumentRoot /var/www/api/public

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/api.yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/api.yourdomain.com/privkey.pem

    <Directory /var/www/api/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Force secure cookies
    Header edit Set-Cookie ^(.*)$ $1;HttpOnly;Secure
</VirtualHost>
```

---

## Environment Configuration

### Production `.env` Snippet

```env
# Application
APP_NAME="Padi REST API"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

# Database
DB_HOST=localhost
DB_NAME=production_db
DB_USER=api_user
DB_PASS=strong_random_password

# Security
JWT_SECRET=your_generated_64_char_secret
CORS_ALLOWED_ORIGINS=https://app.yourdomain.com
RATE_LIMIT_MAX=60
RATE_LIMIT_WINDOW=60
```

---

## Security Hardening

### Generate JWT Secret

Always generate a unique, cryptographically strong secret for production:

```bash
php -r "echo bin2hex(random_bytes(32));"
```

### File Permissions (Linux)

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/api

# Standard permissions
find /var/www/api -type d -exec chmod 755 {} \;
find /var/www/api -type f -exec chmod 644 {} \;

# Writable storage folders
chmod -R 775 storage/
```

### SSL with Let's Encrypt

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d api.yourdomain.com
```

---

## Database & Backups

### Restrictive DB User

Don't use the root user. Create a dedicated user with limited permissions:

```sql
CREATE USER 'api_user'@'localhost' IDENTIFIED BY 'strong_pass';
GRANT SELECT, INSERT, UPDATE, DELETE ON rest_api_db.* TO 'api_user'@'localhost';
```

### Automated Backups

Create a simple cron job to back up your database daily:

```bash
# crontab -e
0 2 * * * mysqldump -u api_user -p'pass' db_name | gzip > /backups/db_$(date +\%F).sql.gz
```

---

## Monitoring & Health Checks

### Health Check Endpoint

Add a simple health check to your `routes/api.php` for uptime monitoring:

```php
$router->get('/health', function () {
    try {
        \Core\Database::getInstance()->getConnection()->query('SELECT 1');
        return ['status' => 'healthy', 'database' => 'connected'];
    } catch (\Exception $e) {
        return ['status' => 'unhealthy', 'error' => $e->getMessage()];
    }
});
```

### Logging

Ensure PHP is configured to log errors to a file:

```ini
; php.ini
log_errors = On
error_log = /var/www/api/storage/logs/php_error.log
```

---

## Performance Optimization

### PHP OPcache

Crucial for production speed. In `php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### Response Compression

Enable Gzip compression in your `.env`:

```env
ENABLE_COMPRESSION=true
```

---

## Troubleshooting

| Issue                     | Solution                                                               |
| ------------------------- | ---------------------------------------------------------------------- |
| **500 Error**             | Check `storage/logs/php_error.log` or server error logs.               |
| **CORS Errors**           | Verify `CORS_ALLOWED_ORIGINS` in `.env` matches your frontend URL.     |
| **DB Connection Refused** | Verify credentials and ensure the DB service is running.               |
| **Permissions Denied**    | Ensure the web server user (`www-data`) owns the `storage/` directory. |

---

## Maintenance Schedule

- **Daily**: Monitor error logs and check health status.
- **Weekly**: Review disk usage and verify backup files exist.
- **Monthly**: Run `composer update` (test in staging first) and apply security patches.

---

**Next Steps:**

1. [FrankenPHP Setup Guide](FRANKENPHP_SETUP.md)
2. [Security Best Practices](../03-advanced/SECURITY.md)
3. [Troubleshooting Guide](TROUBLESHOOTING.md)
