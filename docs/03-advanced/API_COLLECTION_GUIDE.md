# 📮 Postman Collections Guide

Panduan lengkap penggunaan Postman Collections untuk testing API Padi REST Framework.

---

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
✓ Postman Collection created at /path/to/api_collection/product_api_collection.json
  Import this file to Postman to test the API endpoints
```

### 2. Import ke Postman

1. Buka aplikasi Postman
2. Klik **Import** di pojok kiri atas
3. Pilih file `.json` dari folder `api_collection/`:
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

---

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

---

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

---

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

---

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

---

## 🎯 Contoh Workflow

```bash
# 1. Import Auth Collection
# File: api_collection/auth_api_collection.json

# 2. Register atau Login
# Request: POST {{base_url}}/auth/login
# Token akan otomatis tersimpan di {{token}} variable

# 3. Test Get Me
# Request: GET {{base_url}}/auth/me
# Token otomatis terkirim via Authorization header

# 4. Generate CRUD + Postman Collection untuk resource
php scripts/generate.php crud products --write

# 5. Import file api_collection/product_api_collection.json ke Postman

# 6. Set base_url di Collection Variables (jika berbeda)
# base_url = http://localhost:8000

# 7. Test endpoint GET All Products
# Request: GET {{base_url}}/products

# 8. Test protected endpoint Create Product
# Request: POST {{base_url}}/products
# Authorization: Bearer {{token}} (otomatis dari variable)
```

---

## 🔧 Customization

Jika ingin customize collection, edit method `generatePostmanCollection()` di file:

```
core/Generator.php
```

Anda bisa mengubah:

- Sample data generation
- Endpoint structure
- Variable names
- Test scripts

---

## ⚙️ Advanced: Generate All Collections

```bash
# Generate CRUD untuk semua table + Postman collections
php scripts/generate.php crud-all --write

# Hasilnya:
# - Model, Controller, Routes untuk semua table
# - Postman collection untuk setiap table di folder api_collection/
```

---

## 🎨 Collection Features

### Auto-Save Token

Login dan Register endpoints dilengkapi dengan Test Script yang otomatis menyimpan token:

```javascript
// Auto-save token from response
if (pm.response.code === 200) {
  var jsonData = pm.response.json();
  if (jsonData.data && jsonData.data.token) {
    pm.collectionVariables.set("token", jsonData.data.token);
    console.log("Token saved:", jsonData.data.token);
  }
}
```

### Protected Endpoints

Endpoint yang memerlukan authentication otomatis include Bearer token di header:

```
Authorization: Bearer {{token}}
```

### Sample Data

Semua POST/PUT requests sudah include sample data yang smart-generated berdasarkan:

- Column names (email, phone, name, etc)
- Data types (int, varchar, decimal, etc)
- Database constraints

---

## 📖 Collection Structure

```
api_collection/
├── README.md                              # Panduan lengkap (moved to docs/)
├── auth_api_collection.json              # Authentication endpoints
├── example_product_api_collection.json   # Example Product API
└── *_api_collection.json                 # Auto-generated collections
```

---

## 🔗 Related Documentation

- [Code Generator Guide](../02-core-concepts/CODE_GENERATOR.md) - Generate CRUD + Collections
- [API Testing](API_TESTING.md) - Complete API testing guide
- [Authentication](../02-core-concepts/AUTHENTICATION.md) - Auth implementation details
- [Password Reset](PASSWORD_RESET.md) - Forgot/Reset password feature

---

**Happy Testing! 🎉**

### Step 2: Login (Optional)

```
POST /auth/login
```

- Jika sudah register, bisa langsung pakai
- Token akan auto-saved

### Step 3: Create Resources

Jalankan requests ini berurutan:

1. **Create Tag** → Saves tag_id
2. **Create Post** → Saves post_id
3. **Link Post to Tag** → Uses saved IDs
4. **Create Comment** → Attach to post
5. **Create Nested Comment** → Reply to comment

### Step 4: Test GET Endpoints

- Get All Posts
- Get Post by ID
- Get All Comments
- dll.

### Step 5: Test UPDATE/DELETE

- Update Post
- Delete Comment
- Unlink Tag
- dll.

---

## 🔑 Environment Variables

Collection menggunakan variables otomatis:

| Variable     | Description              | Auto-Saved? |
| ------------ | ------------------------ | ----------- |
| `base_url`   | API URL (localhost:8085) | Manual      |
| `auth_token` | JWT Token                | ✅ Yes      |
| `user_id`    | Current user ID          | ✅ Yes      |
| `post_id`    | Last created post        | ✅ Yes      |
| `tag_id`     | Last created tag         | ✅ Yes      |
| `comment_id` | Last created comment     | ✅ Yes      |

**Note:** Variables di-save otomatis setelah sukses create!

---

## 📝 Test Scripts Included

Collection sudah include **test scripts** yang otomatis:

### Register/Login

```javascript
// Auto-save token setelah login
if (pm.response.code === 201) {
  const response = pm.response.json();
  pm.collectionVariables.set("auth_token", response.data.token);
  pm.collectionVariables.set("user_id", response.data.user.id);
}
```

### Create Post/Tag/Comment

```javascript
// Auto-save ID setelah create
if (pm.response.code === 201) {
  const response = pm.response.json();
  pm.collectionVariables.set("post_id", response.data.id);
}
```

---

## 🎨 Request Examples

### 1. Authentication

#### Register

```json
POST /auth/register
{
    "name": "Test User",
    "email": "test@example.com",
    "password": "Test123!@#",
    "password_confirmation": "Test123!@#"
}
```

#### Login

```json
POST /auth/login
{
    "email": "test@example.com",
    "password": "Test123!@#"
}
```

---

### 2. Create Post

```json
POST /posts
Headers: Authorization: Bearer {{auth_token}}

{
    "user_id": 1,
    "title": "My First Blog Post",
    "slug": "my-first-blog-post",
    "content": "This is the content of my first blog post.",
    "excerpt": "A brief introduction",
    "status": "published",
    "published_at": "2026-01-22 10:00:00"
}
```

---

### 3. Create Tag

```json
POST /tags
Headers: Authorization: Bearer {{auth_token}}

{
    "name": "Technology",
    "slug": "technology",
    "description": "Posts about technology"
}
```

---

### 4. Link Post to Tag

```json
POST /post-tags
Headers: Authorization: Bearer {{auth_token}}

{
    "post_id": {{post_id}},   // Auto-filled!
    "tag_id": {{tag_id}}      // Auto-filled!
}
```

---

### 5. Create Comment

```json
POST /comments
Headers: Authorization: Bearer {{auth_token}}

{
    "post_id": {{post_id}},
    "user_id": {{user_id}},
    "content": "Great article!",
    "status": "approved"
}
```

---

### 6. Create Nested Comment (Reply)

```json
POST /comments
Headers: Authorization: Bearer {{auth_token}}

{
    "post_id": {{post_id}},
    "user_id": {{user_id}},
    "parent_id": {{comment_id}},  // Reply to comment!
    "content": "Thank you!",
    "status": "approved"
}
```

---

## 🔧 Modify Collection Variables

### View Variables

1. Click collection name
2. Click **Variables** tab
3. Lihat semua variables

### Edit Base URL

1. Variables tab
2. Find `base_url`
3. Change to your URL (e.g., `https://api.yourdomain.com`)

### Manual Token Input

Jika token tidak auto-save:

1. Variables tab
2. Find `auth_token`
3. Paste token manually

---

## 🎯 Testing Scenarios

### Scenario 1: Complete Blog Post Flow

```
1. Register User ✓
2. Create Tag "Technology" ✓
3. Create Post "My Tech Post" ✓
4. Link Post to Tag ✓
5. Create Comment on Post ✓
6. Create Reply to Comment ✓
7. Get All Posts → See your post
8. Get Post by ID → See with tags & comments
```

### Scenario 2: Update & Delete Flow

```
1. Create Post ✓
2. Update Post ✓
3. Get Post → See changes
4. Delete Post ✓
5. Get Post → 404 Not Found
```

### Scenario 3: Many-to-Many Relationship

```
1. Create Multiple Tags (Tech, News, Tutorial)
2. Create One Post
3. Link Post to all 3 Tags
4. Get Post → See all attached tags
5. Unlink one Tag
6. Get Post → See remaining tags
```

---

## 🐛 Troubleshooting

### "Unauthorized" Error

**Problem:** Request butuh auth tapi token tidak ada

**Solution:**

1. Run **Register** or **Login** request first
2. Check `auth_token` variable is set
3. Check request has Authorization header

### Variables Not Saved

**Problem:** IDs tidak auto-save setelah create

**Solution:**

1. Check response code = 201
2. Check test script ada
3. Manual save: Variables tab → paste ID

### 404 Not Found

**Problem:** Endpoint tidak ditemukan

**Solution:**

1. Check server running: `php -S localhost:8085 -t public`
2. Check `base_url` variable correct
3. Check endpoint path

### Invalid JSON

**Problem:** Request body format salah

**Solution:**

1. Check JSON syntax (commas, brackets)
2. Use Postman's JSON validator
3. Copy from examples di collection

---

## 📚 Additional Tips

### 1. Run Collection with Runner

1. Click collection → **Run**
2. Select requests
3. Click **Run Collection**
4. Semua requests dijalankan otomatis!

### 2. Export Results

- Runner → Export Results
- Share hasil testing

### 3. Create Environment

- Lebih baik: Buat env untuk dev/staging/prod
- Duplik collection variables ke environment
- Switch environment sesuai kebutuhan

### 4. Use Pre-request Scripts

Tambahkan logic sebelum request:

```javascript
// Pre-request Script
const timestamp = Date.now();
pm.collectionVariables.set("timestamp", timestamp);
```

---

## ✅ Checklist

Sebelum testing:

- [ ] Server running (`php -S localhost:8085 -t public`)
- [ ] Database migrated (`php scripts/migrate.php migrate`)
- [ ] Collection imported to Postman
- [ ] `base_url` variable correct

Workflow:

- [ ] Register/Login untuk get token
- [ ] Create resources (Post, Tag, etc.)
- [ ] Test GET endpoints
- [ ] Test UPDATE endpoints
- [ ] Test DELETE endpoints
- [ ] Verify cascade deletes work

---

## 🎉 Ready to Test!

1. **Import** `postman_collection.json`
2. **Run** Register request
3. **Start** creating resources
4. **Test** all endpoints!

**Happy Testing! 🚀**

---

## 📖 Documentation

- **API Docs:** http://localhost:8085/docs
- **Complete Guide:** [docs/README.md](docs/README.md)
- **API Testing:** [docs/API_TESTING.md](docs/API_TESTING.md)
