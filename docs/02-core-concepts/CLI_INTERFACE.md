# 🛠️ Padi Console CLI (Command Line Interface)

**Padi REST API Framework v2.0.1**

---

## 📖 Overview

**Padi CLI** adalah pusat kendali aplikasi Anda. Ia menggantikan skrip terpisah yang dulunya berada di folder `scripts/` menjadi satu antarmuka tunggal yang konsisten dan kuat. Menggunakan sistem **Console Core** yang modern, Padi CLI memudahkan proses pengembangan, migrasi database, hingga pembangkitan kode otomatis.

---

## 🚀 Penggunaan Dasar

Panggil perintah utama melalui terminal di root proyek Anda:

```bash
php padi <command> [arguments] [options]
```

Daftar perintah lengkap dapat dilihat dengan:

```bash
php padi help
```

---

## 🛠️ Daftar Perintah (Commands)

### 📂 Aplikasi (Application)

| Perintah         | Deskripsi                                                           |
| :--------------- | :------------------------------------------------------------------ |
| `php padi init`  | Menjalankan Setup Wizard interaktif (Konfigurasi .env, DB, & Keys). |
| `php padi serve` | Menjalankan development server lokal (Default port: 8085).          |

### 🔨 Pembuatan Kode (Make)

Digunakan untuk membuat file boilerplate baru secara cepat.

| Perintah          | Contoh                              | Luaran                                               |
| :---------------- | :---------------------------------- | :--------------------------------------------------- |
| `make:controller` | `php padi make:controller Product`  | Membuat controller di `app/Controllers/`.            |
| `make:model`      | `php padi make:model products`      | Membuat model ActiveRecord di `app/Models/`.         |
| `make:migration`  | `php padi make:migration add_stock` | Membuat file migrasi baru di `database/migrations/`. |

### 🗄️ Migrasi Database (Migrate)

Mengelola perubahan skema database Anda secara terstruktur.

| Perintah                    | Deskripsi                                        |
| :-------------------------- | :----------------------------------------------- |
| `php padi migrate`          | Menjalankan semua migrasi yang belum dieksekusi. |
| `php padi migrate:status`   | Melihat status migrasi yang sudah/belum jalan.   |
| `php padi migrate:rollback` | Membatalkan migrasi terakhir.                    |

### ⚡ Generator CRUD (Generate)

Otomasi penuh untuk membuat fitur API lengkap dari tabel database.

| Perintah                              | Deskripsi                                                          |
| :------------------------------------ | :----------------------------------------------------------------- |
| `php padi generate:crud <table_name>` | Membuat Model, Controller, Resource, Routes, & Postman Collection. |
| `php padi generate:crud-all`          | Membuat CRUD lengkap untuk **seluruh** tabel di database (Bulk).   |

---

## ⚙️ Opsi & Parameter (Flags)

Padi CLI mendukung berbagai flag fleksibel untuk menyesuaikan hasil eksekusi:

### 📄 Opsi Global

- `--write`: Wajib disertakan pada `generate:crud` agar file benar-benar ditulis ke disk (mencegah penulisan tidak sengaja).
- `--overwrite`: Mengizinkan penimpaan file **Base** yang sudah ada (sangat berguna setelah Anda mengubah skema database).
- `--force`: Memaksa regenerasi pada tabel yang dilindungi (seperti `users`).

### 🛡️ Opsi Keamanan

- `--protected=all`: Membuat seluruh rute yang dihasilkan otomatis memiliki middleware `Auth`.
- `--protected=none`: Semua rute publik (tanpa autentikasi).
- `--middleware=Auth,RoleMiddleware:admin`: Menambahkan middleware kustom ke rute yang dihasilkan.

### 🗄️ Opsi Database & Server

- `--tables=users,posts`: (Khusus `migrate`) Hanya menjalankan migrasi untuk tabel tertentu.
- `--step=2`: (Khusus `rollback`) Membatalkan migrasi sebanyak X langkah ke belakang.
- `--port=9000`: (Khusus `serve`) Menjalankan server di port tertentu.
- `--host=0.0.0.0`: (Khusus `serve`) Mengubah bind host server.

---

## 💡 Contoh Alur Kerja (Workflow)

### 1. Memulai Proyek Baru

```bash
composer install
php padi init
php padi serve
```

### 2. Membuat Fitur CRUD Terproteksi

Misalkan Anda baru saja membuat tabel `products` di database:

```bash
# Hasilkan kode lengkap dengan proteksi Login
php padi generate:crud products --write --protected=all
```

### 3. Mengelola Migrasi Spesifik

```bash
# Jalankan migrasi hanya untuk tabel transaksi
php padi migrate --tables=transactions,orders

# Rollback 3 langkah terakhir
php padi migrate:rollback --step=3
```

---

## 🔍 Tips & Trik

- **Dry Run**: Gunakan `generate:crud` tanpa `--write` untuk melihat pratinjau file apa saja yang akan dibuat tanpa benar-benar menyentuh disk.
- **Alias Cepat**: Anda bisa menggunakan `php padi g` untuk `generate:crud` dan `php padi ga` untuk `generate:crud-all`.

---

**Last Updated:** 2026-02-23  
**Version:** 2.0.1
