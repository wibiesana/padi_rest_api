# 🐳 Docker Deployment Guide

**Padi REST API Framework with FrankenPHP & Redis**

Complete guide for deploying Padi REST API Framework using Docker with support for multiple deployment modes (Standard, Worker, and Full Stack with Nginx).

---

## 🏗️ Architecture & Modes

There are **3 deployment modes** pre-configured for various scenarios:

| Mode           | Compose File                  | Best For                     | Performance           | Feature                            |
| :------------- | :---------------------------- | :--------------------------- | :-------------------- | :--------------------------------- |
| **Standard**   | `docker-compose.standard.yml` | Development / Testing        | Normal                | Auto-reload on code change         |
| **Worker**     | `docker-compose.worker.yml`   | **Production (Recommended)** | ⚡ **10-100x Faster** | High concurrency, memory efficient |
| **Full Stack** | `docker-compose.nginx.yml`    | Production with SSL/TLS      | ⚡ **10-100x Faster** | Nginx reverse proxy + SSL          |

🎯 **Redis** is always installed and configured by default in all deployment modes!

---

## 🚀 Quick Start

### 1. Setup Environment

```bash
# Copy environment template
cp .env.docker .env

# Generate JWT Secret
php -r "echo bin2hex(random_bytes(32));"
# Paste result into .env: JWT_SECRET=...
```

### 2. Choose and Run Mode

#### 🔹 Standard Mode (Development)

```bash
docker compose -f docker-compose.standard.yml up -d
```

- **API:** http://localhost:8085
- **Use for:** Developing new features (supports auto-reload).

#### ⚡ Worker Mode (Production)

```bash
docker compose -f docker-compose.worker.yml up -d
```

- **API:** http://localhost:8085
- **Use for:** Maximum performance in production environments.
- **Note:** Restart the container after code changes (`docker compose restart padi_worker`).

#### 🔒 Full Stack Mode (Nginx + SSL)

```bash
# 1. Place certificates in docker/nginx/ssl/ (fullchain.pem & privkey.pem)
# 2. Update server_name in docker/nginx/nginx.conf
docker compose -f docker-compose.nginx.yml up -d
```

- **API:** http://localhost (HTTP) or https://localhost (HTTPS)
- **Use for:** Production deployment with SSL and Reverse Proxy.

---

## ⚙️ Configuration

### Redis Cache (Default Enabled)

Redis is automatically configured as the primary cache driver.

```env
CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

**Test Redis Connection:**

```bash
# Adjust the compose file name according to your mode
docker compose -f docker-compose.worker.yml exec padi_worker php scripts/test_redis.php
```

### Database Management

If using the MySQL container in Docker:

```bash
# Run migrations
docker compose exec padi_app php scripts/migrate.php migrate

# Export Database
docker compose exec mysql mysqldump -u root -p rest_api_db > backup.sql
```

---

## 🔄 Management Commands

| Action             | Command (Use `-f <file>` matching your mode) |
| :----------------- | :------------------------------------------- |
| **Start Services** | `docker compose up -d`                       |
| **Stop Services**  | `docker compose down`                        |
| **View Logs**      | `docker compose logs -f <service_name>`      |
| **Check Status**   | `docker compose ps`                          |
| **Rebuild Image**  | `docker compose build --no-cache`            |
| **Shell Access**   | `docker compose exec <service_name> bash`    |

---

## 📊 Performance & Scaling

### FrankenPHP Worker Settings

Change the number of workers in `Dockerfile` to handle high traffic:

```dockerfile
# Default: 4 workers
CMD ["frankenphp", "php-server", "--worker", "public/frankenphp-worker.php", "--workers", "10"]
```

### Horizontal Scaling

```bash
# Scale up app instances (only in modes supporting a load balancer)
docker compose up -d --scale padi_app=3
```

---

## 🛡️ Security Best Practices

- ✅ **Disable Debug:** Ensure `APP_DEBUG=false` in your production `.env`.
- ✅ **Strong Secret:** Use a `JWT_SECRET` of at least 64 characters.
- ✅ **File Permissions:**
  ```bash
  docker compose exec padi_app chown -R www-data:www-data storage
  docker compose exec padi_app chmod -R 775 storage
  ```
- ✅ **SSL/TLS:** Always use HTTPS in production environments.
- ✅ **Firewall:** Do not expose the database port (3306) to the public.

---

## 📦 Backup & Restore

### Database Backup

```bash
docker compose exec mysql mysqldump -u root -proot_password rest_api_db | gzip > backup_$(date +%Y%m%d).sql.gz
```

### Database Restore

```bash
gunzip < backup_file.sql.gz | docker compose exec -T mysql mysql -u root -proot_password rest_api_db
```

---

## 🔧 Troubleshooting

### 1. Redis Connection Failed

- **Check Status:** `docker compose ps redis`
- **Solution:** Ensure `REDIS_HOST=redis` in `.env`.

### 2. Code Doesn't Change (Worker Mode)

- **Cause:** Worker mode caches code in memory.
- **Solution:** Restart the container: `docker compose restart padi_worker`.

### 3. Memory Usage Increases Over Time

- **Cause:** Even though the framework resets state per request, applications managing large static data themselves might leak memory.
- **Solution:** Rely on `MAX_REQUESTS` in FrankenPHP configuration to automatically restart the worker periodically.

### 4. Permission Denied in Storage Folder

- **Solution:** Run the `chown` and `chmod` commands inside the container (see Security section).

### 5. Port 8085 is in Use

- **Solution:** Change the port mapping in the `docker-compose.yml` file you are using.

---

## 📚 Resources

- [FrankenPHP Docs](https://frankenphp.dev/)
- [Redis Documentation](https://redis.io/)
- [Docker Compose Guide](https://docs.docker.com/compose/)
- [Nginx Documentation](https://nginx.org/)

---

**Last Updated:** 2026-02-23  
**Version:** 2.0.1 (Stable Worker Mode) 🐳🚀
