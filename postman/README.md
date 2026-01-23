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
3. Pilih file `.json` dari folder `postman/`
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

✅ **GET** - Get All (Paginated) - `GET /api/resource?page=1&per_page=10`
✅ **GET** - Search - `GET /api/resource?search=keyword`
✅ **GET** - Get All (No Pagination) - `GET /api/resource/all`
✅ **GET** - Get Single - `GET /api/resource/1`
✅ **POST** - Create (Protected) - `POST /api/resource`
✅ **PUT** - Update (Protected) - `PUT /api/resource/1`
✅ **DELETE** - Delete (Protected) - `DELETE /api/resource/1`

Endpoint dengan label **(Protected)** memerlukan Authentication token.

## 🔐 Mendapatkan Authentication Token

1. Jalankan request **POST /api/auth/register** atau **POST /api/auth/login**
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

- `product_api_collection.json`
- `user_api_collection.json`
- `category_api_collection.json`

## 🎯 Contoh Workflow

```bash
# 1. Generate CRUD + Postman Collection
php scripts/generate.php crud products --write

# 2. Import file postman/product_api_collection.json ke Postman

# 3. Set base_url di Collection Variables
# base_url = http://localhost:8000

# 4. Test endpoint GET All Products
# Request: GET {{base_url}}/api/products

# 5. Login untuk mendapatkan token
# Request: POST {{base_url}}/api/auth/login

# 6. Copy token dan paste ke Collection Variable {{token}}

# 7. Test protected endpoint Create Product
# Request: POST {{base_url}}/api/products
# Authorization: Bearer {{token}}
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
