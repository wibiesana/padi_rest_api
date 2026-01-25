# 🐳 Docker Deployment Guide

**Padi REST API Framework with FrankenPHP**

---

## 📋 Prerequisites

- Docker 20.10+
- Docker Compose 2.0+

---

## 🚀 Quick Start (Development)

### 1. Start Services

```bash
# Start all services (FrankenPHP + MySQL + phpMyAdmin)
docker-compose up -d

# View logs
docker-compose logs -f app

# Check status
docker-compose ps
```

### 2. Access Services

- **API:** http://localhost:8085
- **phpMyAdmin:** http://localhost:8080
  - Server: `mysql`
  - Username: `root`
  - Password: `root_password`

### 3. Test API

```bash
# Health check
curl http://localhost:8085/

# Register user
curl -X POST http://localhost:8085/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "Test123!",
    "password_confirmation": "Test123!"
  }'
```

### 4. Stop Services

```bash
# Stop services
docker-compose down

# Stop and remove volumes (WARNING: deletes database!)
docker-compose down -v
```

---

## 🏭 Production Deployment

### 1. Prepare Environment

```bash
# Copy environment template
cp .env.docker .env.production

# Edit .env.production and set:
# - DB_PASSWORD (strong password)
# - MYSQL_ROOT_PASSWORD (strong password)
# - JWT_SECRET (64 characters, use: openssl rand -hex 32)
# - APP_URL (your domain)
# - CORS_ALLOWED_ORIGINS (your frontend domains)
```

### 2. Prepare SSL Certificates

```bash
# Create SSL directory
mkdir -p docker/nginx/ssl

# Copy your SSL certificates
cp /path/to/fullchain.pem docker/nginx/ssl/
cp /path/to/privkey.pem docker/nginx/ssl/

# Or use Let's Encrypt (recommended)
# See: https://letsencrypt.org/getting-started/
```

### 3. Update NGINX Configuration

Edit `docker/nginx/nginx.conf`:

```nginx
server_name api.yourdomain.com;  # Change to your domain
```

### 4. Build and Deploy

```bash
# Build production image
docker-compose -f docker-compose.prod.yml build

# Start production services
docker-compose -f docker-compose.prod.yml --env-file .env.production up -d

# View logs
docker-compose -f docker-compose.prod.yml logs -f

# Check health
curl https://api.yourdomain.com/
```

---

## 🛠️ Docker Commands

### Container Management

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# Restart services
docker-compose restart

# View logs
docker-compose logs -f app
docker-compose logs -f mysql

# Execute commands in container
docker-compose exec app bash
docker-compose exec mysql mysql -u root -p
```

### Database Management

```bash
# Import SQL file
docker-compose exec -T mysql mysql -u root -proot_password rest_api_db < backup.sql

# Export database
docker-compose exec mysql mysqldump -u root -proot_password rest_api_db > backup.sql

# Access MySQL CLI
docker-compose exec mysql mysql -u root -proot_password rest_api_db
```

### Application Commands

```bash
# Run migrations
docker-compose exec app php scripts/migrate.php migrate

# Generate CRUD
docker-compose exec app php scripts/generate.php crud products --write

# Clear cache
docker-compose exec app rm -rf storage/cache/*

# Check PHP version
docker-compose exec app php -v

# Install composer dependencies
docker-compose exec app composer install
```

---

## 📊 Performance Tuning

### FrankenPHP Worker Configuration

Edit `Dockerfile` to adjust worker settings:

```dockerfile
# Increase worker count for high traffic
CMD ["frankenphp", "php-server", \
     "--worker", "public/frankenphp-worker.php", \
     "--listen", ":8085", \
     "--workers", "4"]
```

### MySQL Optimization

Edit `docker-compose.yml`:

```yaml
mysql:
  command: --max_connections=200 --innodb_buffer_pool_size=512M
```

### NGINX Rate Limiting

Edit `docker/nginx/nginx.conf`:

```nginx
# Adjust rate limit
limit_req_zone $binary_remote_addr zone=api_limit:10m rate=20r/s;
```

---

## 🔍 Monitoring

### Health Checks

```bash
# Check container health
docker-compose ps

# API health
curl http://localhost:8085/

# MySQL health
docker-compose exec mysql mysqladmin ping -h localhost -u root -proot_password
```

### Resource Usage

```bash
# Container stats
docker stats

# Disk usage
docker system df

# Logs size
docker-compose logs --tail=100 app
```

---

## 🐛 Troubleshooting

### Container Won't Start

```bash
# Check logs
docker-compose logs app

# Check configuration
docker-compose config

# Rebuild image
docker-compose build --no-cache app
```

### Database Connection Failed

```bash
# Check MySQL is running
docker-compose ps mysql

# Check MySQL logs
docker-compose logs mysql

# Test connection
docker-compose exec app php -r "new PDO('mysql:host=mysql;dbname=rest_api_db', 'api_user', 'secret_password');"
```

### Permission Issues

```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 storage
docker-compose exec app chown -R www-data:www-data storage
```

### FrankenPHP Worker Issues

```bash
# Check worker is running
docker-compose exec app ps aux | grep frankenphp

# Restart worker
docker-compose restart app

# Check worker logs
docker-compose logs -f app
```

---

## 🔒 Security Best Practices

### Production Checklist

- [ ] Use strong passwords (DB_PASSWORD, MYSQL_ROOT_PASSWORD)
- [ ] Generate new JWT_SECRET (64+ characters)
- [ ] Set APP_ENV=production and APP_DEBUG=false
- [ ] Configure CORS_ALLOWED_ORIGINS with specific domains
- [ ] Use SSL/TLS certificates (Let's Encrypt recommended)
- [ ] Enable NGINX rate limiting
- [ ] Restrict database access (don't expose port 3306)
- [ ] Use Docker secrets for sensitive data
- [ ] Regular backups of database
- [ ] Monitor container logs

### Using Docker Secrets (Recommended)

```yaml
# docker-compose.prod.yml
services:
  app:
    secrets:
      - jwt_secret
      - db_password
    environment:
      - JWT_SECRET_FILE=/run/secrets/jwt_secret
      - DB_PASS_FILE=/run/secrets/db_password

secrets:
  jwt_secret:
    file: ./secrets/jwt_secret.txt
  db_password:
    file: ./secrets/db_password.txt
```

---

## 📦 Backup & Restore

### Backup

```bash
# Backup database
docker-compose exec mysql mysqldump -u root -proot_password rest_api_db | gzip > backup_$(date +%Y%m%d).sql.gz

# Backup storage
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/

# Backup everything
docker-compose exec mysql mysqldump -u root -proot_password rest_api_db > backup.sql
tar -czf full_backup_$(date +%Y%m%d).tar.gz backup.sql storage/ .env
```

### Restore

```bash
# Restore database
gunzip < backup_20260123.sql.gz | docker-compose exec -T mysql mysql -u root -proot_password rest_api_db

# Restore storage
tar -xzf storage_backup_20260123.tar.gz
```

---

## 🚀 Scaling

### Horizontal Scaling

```bash
# Scale app containers
docker-compose up -d --scale app=3

# Use load balancer (NGINX)
# Update nginx.conf with multiple upstream servers
```

### Vertical Scaling

```yaml
# docker-compose.yml
services:
  app:
    deploy:
      resources:
        limits:
          cpus: "2"
          memory: 2G
        reservations:
          cpus: "1"
          memory: 1G
```

---

## 📚 Additional Resources

- [FrankenPHP Documentation](https://frankenphp.dev/)
- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [NGINX Documentation](https://nginx.org/en/docs/)

---

## 🆘 Getting Help

1. Check logs: `docker-compose logs -f`
2. Check container status: `docker-compose ps`
3. Verify configuration: `docker-compose config`
4. See [TROUBLESHOOTING.md](docs/04-deployment/TROUBLESHOOTING.md)

---

**Happy Deploying!** 🐳🚀
