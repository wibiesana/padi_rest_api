# 🐳 Docker Deployment Guide

**Padi REST API Framework with FrankenPHP & Redis**

Complete guide for deploying Padi REST API Framework using Docker with support for multiple deployment modes (Standard, Worker, and Full Stack with Nginx).

---

## 🏗️ Architecture & Modes

Tersedia **3 mode deployment** yang sudah dikonfigurasi untuk memenuhi berbagai kebutuhan skenario:

| Mode           | Compose File                  | Best For                     | Performance           | Feature                            |
| :------------- | :---------------------------- | :--------------------------- | :-------------------- | :--------------------------------- |
| **Standard**   | `docker-compose.standard.yml` | Development / Testing        | Normal                | Auto-reload on code change         |
| **Worker**     | `docker-compose.worker.yml`   | **Production (Recommended)** | ⚡ **10-100x Faster** | High concurrency, memory efficient |
| **Full Stack** | `docker-compose.nginx.yml`    | Production with SSL/TLS      | ⚡ **10-100x Faster** | Nginx reverse proxy + SSL          |

🎯 **Redis selalu terinstall** dan dikonfigurasi secara default di semua mode deployment!

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

### 2. Pilih dan Jalankan Mode

#### 🔹 Standard Mode (Development)

```bash
docker compose -f docker-compose.standard.yml up -d
```

- **API:** http://localhost:8085
- **Gunakan:** Saat mengembangkan fitur baru (mendukung auto-reload).

#### ⚡ Worker Mode (Production)

```bash
docker compose -f docker-compose.worker.yml up -d
```

- **API:** http://localhost:8085
- **Gunakan:** Untuk performa maksimal di lingkungan produksi.
- **Catatan:** Lakukan restart container setelah perubahan kode (`docker compose restart padi_worker`).

#### 🔒 Full Stack Mode (Nginx + SSL)

```bash
# 1. Letakkan sertifikat di docker/nginx/ssl/ (fullchain.pem & privkey.pem)
# 2. Update server_name di docker/nginx/nginx.conf
docker compose -f docker-compose.nginx.yml up -d
```

- **API:** http://localhost (HTTP) atau https://localhost (HTTPS)
- **Gunakan:** Deployment produksi dengan SSL dan Reverse Proxy.

---

## � Configuration

### Redis Cache (Default Enabled)

Redis secara otomatis dikonfigurasi sebagai cache driver utama.

```env
CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

**Test Redis Connection:**

```bash
# Sesuaikan nama file compose dengan mode yang Anda gunakan
docker compose -f docker-compose.worker.yml exec padi_worker php scripts/test_redis.php
```

### Database Management

Jika menggunakan container MySQL di Docker:

```bash
# Run migrations
docker compose exec padi_app php scripts/migrate.php migrate

# Export Database
docker compose exec mysql mysqldump -u root -p rest_api_db > backup.sql
```

---

## 🔄 Management Commands

| Action             | Command (Gunakan `-f <file>` sesuai mode) |
| :----------------- | :---------------------------------------- |
| **Start Services** | `docker compose up -d`                    |
| **Stop Services**  | `docker compose down`                     |
| **View Logs**      | `docker compose logs -f <service_name>`   |
| **Check Status**   | `docker compose ps`                       |
| **Rebuild Image**  | `docker compose build --no-cache`         |
| **Shell Access**   | `docker compose exec <service_name> bash` |

---

## 📊 Performance & Scaling

### FrankenPHP Worker Settings

Ubah jumlah worker di `Dockerfile` untuk menangani traffic tinggi:

```dockerfile
# Default: 4 workers
CMD ["frankenphp", "php-server", "--worker", "public/frankenphp-worker.php", "--workers", "10"]
```

### Horizontal Scaling

```bash
# Memperbanyak instance app (hanya di mode yang mendukung load balancer)
docker compose up -d --scale padi_app=3
```

---

## �️ Security Best Practices

- ✅ **Disable Debug:** Pastikan `APP_DEBUG=false` di `.env` produksi.
- ✅ **Strong Secret:** Gunakan `JWT_SECRET` minimal 64 karakter.
- ✅ **File Permissions:**
  ```bash
  docker compose exec padi_app chown -R www-data:www-data storage
  docker compose exec padi_app chmod -R 775 storage
  ```
- ✅ **SSL/TLS:** Selalu gunakan HTTPS di lingkungan produksi.
- ✅ **Firewall:** Jangan ekspose port database (3306) ke publik.

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

## � Troubleshooting

### 1. Redis Connection Failed

- **Cek Status:** `docker compose ps redis`
- **Solusi:** Pastikan `REDIS_HOST=redis` di `.env`.

### 2. Kode Tidak Berubah (Worker Mode)

- **Sebab:** Worker mode meng-cache kode di memory.
- **Solusi:** Restart container: `docker compose restart padi_worker`.

### 3. Memory Usage Increases Over Time

- **Sebab:** Meskipun framework sudah diproteksi dengan auto-reset state per request, aplikasi yang mengelola ribuan data statis sendiri mungkin mengalami memory leak.
- **Solusi:** Gunakan `MAX_REQUESTS` di konfigurasi FrankenPHP untuk merestart worker secara periodik secara otomatis.

### 3. Permission Denied di Folder Storage

- **Solusi:** Jalankan command `chown` dan `chmod` di dalam container (lihat bagian Security).

### 4. Port 8085 Sudah Digunakan

- **Solusi:** Ubah port mapping di file `docker-compose.yml` yang Anda gunakan.

---

## 📚 Resources

- [FrankenPHP Docs](https://frankenphp.dev/)
- [Redis Documentation](https://redis.io/)
- [Docker Compose Guide](https://docs.docker.com/compose/)
- [Nginx Documentation](https://nginx.org/)

---

**Last Updated:** 2026-02-23  
**Version:** 2.0.1 (Stable Worker Mode) 🐳🚀
