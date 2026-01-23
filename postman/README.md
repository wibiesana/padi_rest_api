# Postman Collections

Folder ini berisi Postman Collection files yang di-generate secara otomatis saat Anda menjalankan code generator.

## 📦 Cara Menggunakan

### 1. Generate Postman Collection

Saat Anda menjalankan generate CRUD, Postman collection akan otomatis dibuat:

```bash
php scripts/generate.php crud products --write
```

Output akan menampilkan:

```
1. Generating Model...
✓ Base ActiveRecord Product created/updated
✓ ActiveRecord Product created successfully

2. Generating Controller...
✓ Base Controller ProductController created/updated
✓ Controller ProductController created successfully

3. Generating Routes...
✓ Routes for 'products' automatically appended to routes/api.php

4. Generating Postman Collection...
✓ Postman Collection created at /path/to/postman/product_api_collection.json
  Import this file to Postman to test the API endpoints
```

### 2. Import ke Postman

1. Buka aplikasi Postman
2. Klik **Import** di pojok kiri atas
3. Pilih file `.json` dari folder `postman/`:
   - **`auth_api_collection.json`** - Authentication endpoints (Login, Register, Get Me, Forgot/Reset Password)
   - **`*_api_collection.json`** - Resource endpoints (auto-generated)
4. Collection akan muncul di sidebar Postman Anda

### 3. Setup Environment Variables

Collection menggunakan 2 variable:

- `{{base_url}}` - URL base aplikasi Anda (default: `http://localhost:8000`)
- `{{token}}` - Bearer token untuk autentikasi (kosong secara default)

**Cara set variable:**

1. Di Postman, klik nama collection
2. Pilih tab **Variables**
3. Update nilai `base_url` sesuai server Anda
4. Update nilai `token` dengan token hasil login

### 4. Testing API

Setiap collection berisi endpoint standar CRUD:

✅ **GET** - Get All (Paginated) - `GET /resource?page=1&per_page=10`
✅ **GET** - Search - `GET /resource?search=keyword`
✅ **GET** - Get All (No Pagination) - `GET /resource/all`
✅ **GET** - Get Single - `GET /resource/1`
✅ **POST** - Create (Protected) - `POST /resource`
✅ **PUT** - Update (Protected) - `PUT /resource/1`
✅ **DELETE** - Delete (Protected) - `DELETE /resource/1`

Endpoint dengan label **(Protected)** memerlukan Authentication token.

**Authentication Collection:**

✅ **POST** - Register - `POST /auth/register`
✅ **POST** - Login - `POST /auth/login`
✅ **GET** - Get Me (Protected) - `GET /auth/me`
✅ **POST** - Logout (Protected) - `POST /auth/logout`
✅ **POST** - Forgot Password - `POST /auth/forgot-password`
✅ **POST** - Reset Password - `POST /auth/reset-password`

## 🔐 Mendapatkan Authentication Token

**Otomatis (Recommended):**

1. Import collection `auth_api_collection.json`
2. Jalankan request **Register** atau **Login**
3. Token akan otomatis disimpan ke variable `{{token}}` (via Test Script)
4. Gunakan untuk request protected endpoints

**Manual:**

1. Jalankan request **POST /auth/register** atau **POST /auth/login**
2. Copy token dari response
3. Paste token ke variable `{{token}}` di Collection Variables
4. Token akan otomatis ditambahkan ke header protected endpoints:
   ```
   Authorization: Bearer {{token}}
   ```

## 📝 Sample Request Body

Setiap request POST/PUT sudah dilengkapi dengan sample data berdasarkan schema database:

```json
{
  "name": "Sample Name",
  "email": "user@example.com",
  "description": "This is a sample description",
  "price": 99.99,
  "status": "active"
}
```

Edit sesuai kebutuhan Anda.

## 🚀 Tips

1. **Generate untuk semua table sekaligus:**

   ```bash
   php scripts/generate.php crud-all --write
   ```

   Ini akan membuat collection untuk semua table di database.

2. **Organize collections:**
   - Import semua collections
   - Buat Folder di Postman untuk mengelompokkan
   - Gunakan Workspace untuk project berbeda

3. **Share dengan team:**
   - Export collection dari Postman
   - Commit ke Git repository
   - Team bisa import langsung

4. **Update collection:**
   - Jika schema berubah, jalankan generate ulang
   - File akan di-overwrite dengan data terbaru
   - Import ulang ke Postman

## 📁 File Naming Convention

File collection menggunakan format:

```
{model_name}_api_collection.json
```

Contoh:

- `auth_api_collection.json` - Authentication endpoints (manual/provided)
- `product_api_collection.json` - Auto-generated
- `user_api_collection.json` - Auto-generated
- `category_api_collection.json` - Auto-generated

## 🎯 Contoh Workflow

```bash
# 1. Import Auth Collection
# File: postman/auth_api_collection.json

# 2. Register atau Login
# Request: POST {{base_url}}/auth/login
# Token akan otomatis tersimpan di {{token}} variable

# 3. Test Get Me
# Request: GET {{base_url}}/auth/me
# Token otomatis terkirim via Authorization header

# 4. Generate CRUD + Postman Collection untuk resource
php scripts/generate.php crud products --write

# 5. Import file postman/product_api_collection.json ke Postman

# 6. Set base_url di Collection Variables (jika berbeda)
# base_url = http://localhost:8000

# 7. Test endpoint GET All Products
# Request: GET {{base_url}}/products

# 8. Test protected endpoint Create Product
# Request: POST {{base_url}}/products
# Authorization: Bearer {{token}} (otomatis dari variable)
```

## 🔧 Customization

Jika ingin customize collection, edit method `generatePostmanCollection()` di file:

```
core/Generator.php
```

## ⚙️ Advanced: Generate All Collections

```bash
# Generate CRUD untuk semua table + Postman collections
php scripts/generate.php crud-all --write

# Hasilnya:
# - Model, Controller, Routes untuk semua table
# - Postman collection untuk setiap table di folder postman/
```

---

**Happy Testing! 🎉**

Jika ada pertanyaan atau issue, silakan buka issue di repository.
