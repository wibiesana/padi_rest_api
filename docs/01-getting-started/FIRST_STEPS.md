# 🚀 First Steps

**Padi REST API Framework v1.0.0**

---

## Quick Start Checklist

Before you begin, make sure you've completed:

- ✅ [Installation](INSTALLATION.md)
- ✅ [Configuration](CONFIGURATION.md)
- ✅ Database setup
- ✅ Server is running

---

## Step 1: Test Installation

### Health Check

```bash
curl http://localhost:8085/

# Expected response:
{
  "success": true,
  "message": "Padi REST API is running",
  "version": "1.0.0"
}
```

✅ If you see this response, your API is working!

---

## Step 2: Register First User

### Create Admin Account

```bash
curl -X POST http://localhost:8085/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Admin User",
    "email": "admin@example.com",
    "password": "Admin123!",
    "password_confirmation": "Admin123!"
  }'
```

### Expected Response

```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "created_at": "2026-01-23 09:50:00"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }
}
```

**💡 Save the token!** You'll need it for authenticated requests.

---

## Step 3: Login

### Authenticate User

```bash
curl -X POST http://localhost:8085/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "Admin123!"
  }'
```

### Expected Response

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com"
    }
  }
}
```

---

## Step 4: Test Protected Route

### Get Current User Profile

```bash
curl -X GET http://localhost:8085/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Replace `YOUR_TOKEN_HERE` with the actual token from login response.**

### Expected Response

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "created_at": "2026-01-23 09:50:00"
  }
}
```

✅ If you see your user data, authentication is working!

---

## Step 5: Generate Your First CRUD

### List Available Tables

```bash
php scripts/generate.php list
```

### Generate CRUD for a Table

```bash
# Generate complete CRUD (Model + Controller + Routes)
php scripts/generate.php crud products --write

# This creates:
# - app/Models/Base/Product.php
# - app/Models/Product.php
# - app/Controllers/Base/ProductController.php
# - app/Controllers/ProductController.php
# - Updates routes/api.php
```

### Test Generated Endpoints

```bash
# Get all products
curl -X GET http://localhost:8085/products \
  -H "Authorization: Bearer YOUR_TOKEN"

# Create a product
curl -X POST http://localhost:8085/products \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Product",
    "price": 99.99,
    "description": "A test product"
  }'
```

---

## Common First Tasks

### 1. Create a New Resource

```bash
# 1. Create database table (migration or manual)
# 2. Generate CRUD
php scripts/generate.php crud table_name --write

# 3. Test endpoints
curl http://localhost:8085/table_name
```

### 2. Customize a Controller

Edit `app/Controllers/YourController.php`:

```php
<?php

namespace App\Controllers;

use App\Controllers\Base\YourController as BaseYourController;

class YourController extends BaseYourController
{
    // Add custom methods here
    public function customAction(): void
    {
        $this->jsonResponse([
            'message' => 'Custom action!'
        ]);
    }
}
```

### 3. Add Custom Validation

Edit your controller:

```php
protected function getValidationRules(): array
{
    return [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'price' => 'required|numeric|min:0',
        'status' => 'required|in:active,inactive'
    ];
}
```

---

## Testing Workflow

### 1. Using cURL

```bash
# Set token as variable
TOKEN="your_token_here"

# Test endpoints
curl -H "Authorization: Bearer $TOKEN" http://localhost:8085/products
```

### 2. Using Postman

1. Import collection: `api_collection/auth_api_collection.json`
2. Set environment variable: `API_URL = http://localhost:8085`
3. Set `access_token` after login
4. Test all endpoints

See [API_COLLECTION_GUIDE.md](../03-advanced/API_COLLECTION_GUIDE.md) for details.

### 3. Using Frontend

See [FRONTEND_INTEGRATION.md](../03-advanced/FRONTEND_INTEGRATION.md) for:

- Vue.js integration
- React integration
- Angular integration
- Next.js integration

---

## Password Requirements

When creating users, passwords must have:

- ✅ Minimum 8 characters
- ✅ At least 1 uppercase letter
- ✅ At least 1 lowercase letter
- ✅ At least 1 number
- ✅ At least 1 special character (@$!%\*?&#)

**Valid examples:**

- `Admin123!`
- `SecurePass@2024`
- `MyP@ssw0rd`

**Invalid examples:**

- `password` (no uppercase, number, special char)
- `PASSWORD123` (no lowercase, special char)
- `Pass1!` (too short)

---

## Quick Reference

### Authentication Endpoints

| Method | Endpoint         | Description       |
| ------ | ---------------- | ----------------- |
| POST   | `/auth/register` | Register new user |
| POST   | `/auth/login`    | Login user        |
| GET    | `/auth/me`       | Get current user  |
| POST   | `/auth/logout`   | Logout user       |
| POST   | `/auth/refresh`  | Refresh token     |

### CRUD Endpoints (Auto-generated)

| Method | Endpoint         | Description |
| ------ | ---------------- | ----------- |
| GET    | `/resource`      | List all    |
| GET    | `/resource/{id}` | Get one     |
| POST   | `/resource`      | Create      |
| PUT    | `/resource/{id}` | Update      |
| DELETE | `/resource/{id}` | Delete      |

---

## Next Steps

### Learn Core Concepts

1. **Authentication** - [../02-core-concepts/AUTHENTICATION.md](../02-core-concepts/AUTHENTICATION.md)
2. **Models** - [../02-core-concepts/MODELS.md](../02-core-concepts/MODELS.md)
3. **Controllers** - [../02-core-concepts/CONTROLLERS.md](../02-core-concepts/CONTROLLERS.md)
4. **Query Builder** - [../02-core-concepts/QUERY_BUILDER.md](../02-core-concepts/QUERY_BUILDER.md)

### Explore Advanced Topics

1. **Frontend Integration** - [../03-advanced/FRONTEND_INTEGRATION.md](../03-advanced/FRONTEND_INTEGRATION.md)
2. **API Testing** - [../03-advanced/API_TESTING.md](../03-advanced/API_TESTING.md)
3. **Multi-Database** - [../03-advanced/MULTI_DATABASE.md](../03-advanced/MULTI_DATABASE.md)

### Deploy to Production

1. **Production Deployment** - [../04-deployment/PRODUCTION.md](../04-deployment/PRODUCTION.md)
2. **FrankenPHP Setup** - [../04-deployment/FRANKENPHP_SETUP.md](../04-deployment/FRANKENPHP_SETUP.md)

---

## Troubleshooting

### Common Issues

| Issue                | Solution                                    |
| -------------------- | ------------------------------------------- |
| 401 Unauthorized     | Check if token is valid and not expired     |
| 422 Validation Error | Check request body matches validation rules |
| 404 Not Found        | Verify endpoint URL and route registration  |
| 500 Server Error     | Check logs and database connection          |

See [INIT_APP_TROUBLESHOOTING.md](INIT_APP_TROUBLESHOOTING.md) for more solutions.

---

**Previous:** [← Configuration Guide](CONFIGURATION.md) | **Next:** [Authentication Guide →](../02-core-concepts/AUTHENTICATION.md)
