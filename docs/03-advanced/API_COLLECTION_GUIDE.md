# 📮 Postman Collections Guide

Complete guide for using Postman Collections to test the Padi REST Framework API.

---

## 📦 How to Use

### 1. Generate Postman Collection

When you run the CRUD generator, a Postman collection will be automatically created:

```bash
php scripts/generate.php crud products --write
```

The output will show:

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

### 2. Import to Postman

1. Open the Postman application
2. Click **Import** in the top left corner
3. Select the `.json` file from the `api_collection/` folder:
   - **`auth_api_collection.json`** - Authentication endpoints (Login, Register, Get Me, Forgot/Reset Password)
   - **`*_api_collection.json`** - Resource endpoints (auto-generated)
4. The collection will appear in your Postman sidebar

### 3. Setup Environment Variables

The collections use 2 variables:

- `{{base_url}}` - The base URL of your application (default: `http://localhost:8085`)
- `{{token}}` - Bearer token for authentication (empty by default)

**How to set variables:**

1. In Postman, click the collection name
2. Select the **Variables** tab
3. Update the `base_url` value according to your server
4. Update the `token` value with the token returned after login

### 4. Testing API

Each collection contains standard CRUD endpoints:

✅ **GET** - Get All (Paginated) - `GET /resource?page=1&per_page=10`
✅ **GET** - Search - `GET /resource?search=keyword`
✅ **GET** - Get All (No Pagination) - `GET /resource/all`
✅ **GET** - Get Single - `GET /resource/1`
✅ **POST** - Create (Protected) - `POST /resource`
✅ **PUT** - Update (Protected) - `PUT /resource/1`
✅ **DELETE** - Delete (Protected) - `DELETE /resource/1`

Endpoints labeled **(Protected)** require an Authentication token.

**Authentication Collection:**

✅ **POST** - Register - `POST /auth/register`
✅ **POST** - Login - `POST /auth/login`
✅ **GET** - Get Me (Protected) - `GET /auth/me`
✅ **POST** - Logout (Protected) - `POST /auth/logout`
✅ **POST** - Forgot Password - `POST /auth/forgot-password`
✅ **POST** - Reset Password - `POST /auth/reset-password`

---

## 🔐 Getting the Authentication Token

**Automatic (Recommended):**

1. Import the `auth_api_collection.json` collection
2. Run the **Register** or **Login** request
3. The token will automatically be saved to the `{{token}}` variable (via Test Script)
4. Use it for subsequent requests to protected endpoints

**Manual:**

1. Run the **POST /auth/register** or **POST /auth/login** request
2. Copy the token from the response
3. Paste the token into the `{{token}}` variable in Collection Variables
4. The token will be automatically added to the headers of protected endpoints:
   ```
   Authorization: Bearer {{token}}
   ```

---

## 📝 Sample Request Body

Every POST/PUT request includes smart-generated sample data based on the database schema:

```json
{
  "name": "Sample Name",
  "email": "user@example.com",
  "description": "This is a sample description",
  "price": 99.99,
  "status": "active"
}
```

Edit this as needed for your tests.

---

## 🚀 Tips

1. **Generate for all tables at once:**

   ```bash
   php scripts/generate.php crud-all --write
   ```

   This will create a collection for every table in the database.

2. **Organize collections:**
   - Import all collections
   - Create Folders in Postman to group them
   - Use Workspaces for different projects

3. **Share with your team:**
   - Export the collection from Postman
   - Commit it to your Git repository
   - Your team can import it directly

4. **Update collection:**
   - If the schema changes, run the generator again
   - The file will be overwritten with the latest data
   - Re-import it into Postman

---

## 📁 File Naming Convention

Collection files use the following format:

```
{model_name}_api_collection.json
```

Example:

- `auth_api_collection.json` - Authentication endpoints (manual/provided)
- `product_api_collection.json` - Auto-generated
- `user_api_collection.json` - Auto-generated
- `category_api_collection.json` - Auto-generated

---

## 🎯 Example Workflow

```bash
# 1. Import Auth Collection
# File: api_collection/auth_api_collection.json

# 2. Register or Login
# Request: POST {{base_url}}/auth/login
# Token will be automatically saved to {{token}} variable

# 3. Test Get Me
# Request: GET {{base_url}}/auth/me
# Token is automatically sent via Authorization header

# 4. Generate CRUD + Postman Collection for resource
php scripts/generate.php crud products --write

# 5. Import api_collection/product_api_collection.json into Postman

# 6. Set base_url in Collection Variables (if different)
# base_url = http://localhost:8085

# 7. Test endpoint GET All Products
# Request: GET {{base_url}}/products

# 8. Test protected endpoint Create Product
# Request: POST {{base_url}}/products
# Authorization: Bearer {{token}} (automatic from variable)
```

---

## 🔧 Customization

If you want to customize the collection generation, edit the `generatePostmanCollection()` method in:

```
core/Generator.php
```

You can modify:

- Sample data generation
- Endpoint structure
- Variable names
- Test scripts

---

## ⚙️ Advanced: Generate All Collections

```bash
# Generate CRUD for all tables + Postman collections
php scripts/generate.php crud-all --write

# Results:
# - Model, Controller, Routes for all tables
# - Postman collection for each table in api_collection/ folder
```

---

## 🎨 Collection Features

### Auto-Save Token

Login and Register endpoints include a Test Script that automatically saves the token:

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

Endpoints that require authentication automatically include the Bearer token in the header:

```
Authorization: Bearer {{token}}
```

### Sample Data

All POST/PUT requests include sample data that is automatically generated based on:

- Column names (email, phone, name, etc)
- Data types (int, varchar, decimal, etc)
- Database constraints

---

## 📖 Collection Structure

```
api_collection/
├── README.md                              # Complete guide (moved to docs/)
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

- If already registered, log in directly
- The token will be auto-saved

### Step 3: Create Resources

Run these requests sequentially:

1. **Create Tag** → Saves tag_id
2. **Create Post** → Saves post_id
3. **Link Post to Tag** → Uses saved IDs
4. **Create Comment** → Attach to post
5. **Create Nested Comment** → Reply to comment

### Step 4: Test GET Endpoints

- Get All Posts
- Get Post by ID
- Get All Comments
- etc.

### Step 5: Test UPDATE/DELETE

- Update Post
- Delete Comment
- Unlink Tag
- etc.

---

## 🔑 Environment Variables

Collections use automatic variables:

| Variable     | Description              | Auto-Saved? |
| ------------ | ------------------------ | ----------- |
| `base_url`   | API URL (localhost:8085) | Manual      |
| `auth_token` | JWT Token                | ✅ Yes      |
| `user_id`    | Current user ID          | ✅ Yes      |
| `post_id`    | Last created post        | ✅ Yes      |
| `tag_id`     | Last created tag         | ✅ Yes      |
| `comment_id` | Last created comment     | ✅ Yes      |

**Note:** Variables are auto-saved upon a successful create response (201)!

---

## 📝 Test Scripts Included

Collections come with **test scripts** that automatically run in Postman:

### Register/Login

```javascript
// Auto-save token after login
if (pm.response.code === 201) {
  const response = pm.response.json();
  pm.collectionVariables.set("auth_token", response.data.token);
  pm.collectionVariables.set("user_id", response.data.user.id);
}
```

### Create Post/Tag/Comment

```javascript
// Auto-save ID after create
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
3. View all variables

### Edit Base URL

1. Variables tab
2. Find `base_url`
3. Change to your URL (e.g., `https://api.yourdomain.com`)

### Manual Token Input

If the token doesn't auto-save:

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

**Problem:** Request needs auth but token is missing

**Solution:**

1. Run **Register** or **Login** request first
2. Check `auth_token` variable is set
3. Check request has Authorization header

### Variables Not Saved

**Problem:** IDs don't auto-save after create

**Solution:**

1. Check response code = 201
2. Check test script exists
3. Manual save: Variables tab → paste ID

### 404 Not Found

**Problem:** Endpoint not found

**Solution:**

1. Check server running: `php -S localhost:8085 -t public`
2. Check `base_url` variable is correct
3. Check endpoint path

### Invalid JSON

**Problem:** Incorrect request body format

**Solution:**

1. Check JSON syntax (commas, brackets)
2. Use Postman's JSON validator
3. Copy from examples in the collection

---

## 📚 Additional Tips

### 1. Run Collection with Runner

1. Click collection → **Run**
2. Select requests
3. Click **Run Collection**
4. All requests are run sequentially!

### 2. Export Results

- Runner → Export Results
- Share test results

### 3. Create Environment

- Recommended: Create environments for dev/staging/prod
- Move collection variables to the environment
- Switch environment as needed

### 4. Use Pre-request Scripts

Add logic before a request:

```javascript
// Pre-request Script
const timestamp = Date.now();
pm.collectionVariables.set("timestamp", timestamp);
```

---

## ✅ Checklist

Before testing:

- [ ] Server running (`php -S localhost:8085 -t public`)
- [ ] Database migrated (`php scripts/migrate.php migrate`)
- [ ] Collection imported to Postman
- [ ] `base_url` variable correct

Workflow:

- [ ] Register/Login to get token
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
