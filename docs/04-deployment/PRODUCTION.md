# 🚀 Production Deployment

**Padi REST API Framework v2.0**

---

## Pre-Deployment Checklist

### 1. Environment Configuration

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Set `DEBUG_SHOW_QUERIES=false`
- [ ] Update `APP_URL` to production domain
- [ ] Configure `CORS_ALLOWED_ORIGINS`

### 2. Security Hardening

- [ ] Generate new `JWT_SECRET` (64+ characters)
- [ ] Use strong database password
- [ ] Enable HTTPS (SSL/TLS)
- [ ] Set appropriate rate limits
- [ ] Review file permissions

### 3. Performance Optimization

- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Enable response compression
- [ ] Configure caching
- [ ] Optimize database queries

### 4. Database Setup

- [ ] Create production database
- [ ] Run migrations
- [ ] Import initial data
- [ ] Create database backups

---

## Server Requirements

### Minimum Requirements

- **PHP 8.1+**
- **Composer**
- **MySQL 5.7+** / **MariaDB 10.3+**
- **Web Server** (Apache, NGINX, or FrankenPHP)
- **SSL/TLS Certificate**

### Recommended Server Specs

- **CPU:** 2+ cores
- **RAM:** 2GB+ (4GB recommended)
- **Storage:** 20GB+ SSD
- **Bandwidth:** 100Mbps+

---

## Deployment Methods

### Method 1: Traditional Server (Apache/NGINX)

#### Apache Configuration

**File:** `/etc/apache2/sites-available/api.conf`

```apache
<VirtualHost *:80>
    ServerName api.yourdomain.com
    DocumentRoot /var/www/api/public

    <Directory /var/www/api/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/api-error.log
    CustomLog ${APACHE_LOG_DIR}/api-access.log combined

    # Redirect to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}$1 [R=301,L]
</VirtualHost>

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

    ErrorLog ${APACHE_LOG_DIR}/api-error.log
    CustomLog ${APACHE_LOG_DIR}/api-access.log combined
</VirtualHost>
```

**Enable site:**

```bash
sudo a2ensite api.conf
sudo a2enmod rewrite ssl
sudo systemctl restart apache2
```

#### NGINX Configuration

**File:** `/etc/nginx/sites-available/api`

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

**Enable site:**

```bash
sudo ln -s /etc/nginx/sites-available/api /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Method 2: FrankenPHP (Recommended for Performance)

**3-10x faster than traditional PHP-FPM!**

See [FRANKENPHP_SETUP.md](FRANKENPHP_SETUP.md) for complete guide.

**Quick start:**

```bash
# Download FrankenPHP
curl -L https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-linux-x86_64 -o frankenphp
chmod +x frankenphp

# Run in worker mode
./frankenphp php-server --worker public/frankenphp-worker.php --listen :8085
```

---

## SSL/TLS Setup

### Using Let's Encrypt (Free)

```bash
# Install Certbot
sudo apt update
sudo apt install certbot python3-certbot-apache

# Get certificate
sudo certbot --apache -d api.yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

### Using Cloudflare (Free)

1. Add domain to Cloudflare
2. Set DNS record: `api.yourdomain.com` → Your server IP
3. Enable "Full (strict)" SSL mode
4. Enable "Always Use HTTPS"

---

## Environment Configuration

### Production .env

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
DB_PASS=<strong-random-password>

# Security
JWT_SECRET=<64-character-random-secret>
JWT_ALGORITHM=HS256
JWT_EXPIRY=3600

# CORS (specify exact domains)
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com

# Rate Limiting
RATE_LIMIT_MAX=60
RATE_LIMIT_WINDOW=60
```

---

## File Permissions

### Linux/Unix

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/api

# Set directory permissions
sudo find /var/www/api -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/api -type f -exec chmod 644 {} \;

# Storage directory (writable)
sudo chmod -R 775 /var/www/api/storage
sudo chown -R www-data:www-data /var/www/api/storage
```

### Windows (IIS)

```powershell
# Grant IIS_IUSRS read/write to storage
icacls "D:\inetpub\api\storage" /grant IIS_IUSRS:(OI)(CI)F /T
```

---

## Database Setup

### Create Production Database

```bash
mysql -u root -p

CREATE DATABASE rest_api_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'api_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON rest_api_db.* TO 'api_user'@'localhost';
FLUSH PRIVILEGES;

exit;
```

### Import Schema

```bash
mysql -u api_user -p rest_api_db < database/schema.sql
```

### Run Migrations

```bash
php scripts/migrate.php migrate
```

---

## Composer Optimization

### Install Production Dependencies

```bash
# Remove dev dependencies
composer install --no-dev --optimize-autoloader

# Update autoloader
composer dump-autoload --optimize --no-dev
```

---

## Caching Configuration

### Enable OPcache

**File:** `/etc/php/8.1/fpm/php.ini`

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.1-fpm
```

---

## Monitoring

### Application Monitoring

```bash
# Check logs
tail -f /var/log/apache2/api-error.log
tail -f /var/log/nginx/error.log

# Monitor PHP-FPM
sudo systemctl status php8.1-fpm
```

### Database Monitoring

```bash
# MySQL process list
mysql -u root -p -e "SHOW PROCESSLIST;"

# Slow query log
sudo tail -f /var/log/mysql/slow-query.log
```

### Server Monitoring

```bash
# CPU and memory
htop

# Disk usage
df -h

# Network
netstat -tulpn
```

---

## Backup Strategy

### Database Backups

```bash
# Daily backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/database"
DB_NAME="rest_api_db"
DB_USER="api_user"
DB_PASS="password"

mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete
```

**Add to crontab:**

```bash
crontab -e

# Daily backup at 2 AM
0 2 * * * /path/to/backup.sh
```

### File Backups

```bash
# Backup entire application
tar -czf /backups/api_$(date +%Y%m%d).tar.gz /var/www/api

# Exclude cache and logs
tar -czf /backups/api_$(date +%Y%m%d).tar.gz \
  --exclude='/var/www/api/storage/cache' \
  --exclude='/var/www/api/storage/logs' \
  /var/www/api
```

---

## Deployment Workflow

### Step-by-Step Deployment

```bash
# 1. Clone repository
cd /var/www
git clone https://github.com/yourusername/api.git
cd api

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Configure environment
cp .env.example .env
nano .env  # Edit configuration

# 4. Set permissions
sudo chown -R www-data:www-data /var/www/api
sudo chmod -R 755 /var/www/api
sudo chmod -R 775 /var/www/api/storage

# 5. Create database
mysql -u root -p < database/schema.sql

# 6. Run migrations
php scripts/migrate.php migrate

# 7. Configure web server
sudo nano /etc/nginx/sites-available/api
sudo ln -s /etc/nginx/sites-available/api /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx

# 8. Get SSL certificate
sudo certbot --nginx -d api.yourdomain.com

# 9. Test API
curl https://api.yourdomain.com/

# 10. Done! 🎉
```

---

## Continuous Deployment

### Using Git Hooks

**File:** `/var/www/api/.git/hooks/post-receive`

```bash
#!/bin/bash
cd /var/www/api
git pull origin main
composer install --no-dev --optimize-autoloader
php scripts/migrate.php migrate
sudo systemctl restart php8.1-fpm
```

Make executable:

```bash
chmod +x .git/hooks/post-receive
```

---

## Troubleshooting

### Common Issues

| Issue                      | Solution                                           |
| -------------------------- | -------------------------------------------------- |
| 500 Internal Server Error  | Check error logs, verify .env configuration        |
| Database connection failed | Verify DB credentials, check MySQL service         |
| Permission denied          | Fix file permissions (755 for dirs, 644 for files) |
| CORS errors                | Add frontend domain to CORS_ALLOWED_ORIGINS        |
| SSL certificate error      | Renew certificate with certbot                     |

### Debug Mode (Temporary)

```env
# Enable debug temporarily
APP_DEBUG=true
DEBUG_SHOW_QUERIES=true

# Remember to disable after debugging!
APP_DEBUG=false
DEBUG_SHOW_QUERIES=false
```

---

## Performance Optimization

### 1. Enable Compression

```env
ENABLE_COMPRESSION=true
```

### 2. Use FrankenPHP Worker Mode

See [FRANKENPHP_SETUP.md](FRANKENPHP_SETUP.md)

**3-10x performance improvement!**

### 3. Database Indexing

```sql
-- Add indexes to frequently queried columns
CREATE INDEX idx_status ON products(status);
CREATE INDEX idx_category_id ON products(category_id);
CREATE INDEX idx_created_at ON products(created_at);
```

### 4. Query Caching

Framework automatically caches COUNT(\*) queries.

---

## Next Steps

1. **FrankenPHP Setup** - [FRANKENPHP_SETUP.md](FRANKENPHP_SETUP.md)
2. **Security Best Practices** - [../03-advanced/SECURITY.md](../03-advanced/SECURITY.md)
3. **Troubleshooting** - [TROUBLESHOOTING.md](TROUBLESHOOTING.md)

---

**Previous:** [← Security Best Practices](../03-advanced/SECURITY.md) | **Next:** [FrankenPHP Setup →](FRANKENPHP_SETUP.md)
