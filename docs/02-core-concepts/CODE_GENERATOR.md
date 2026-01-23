# ⚡ Code Generator

**Padi REST API Framework v2.0**

---

## Overview

The Code Generator automatically creates Models, Controllers, and Routes from your database tables, saving hours of repetitive coding.

---

## Generator Commands

### List All Tables

```bash
php scripts/generate.php list
```

Shows all tables in your database.

### Generate Model Only

```bash
php scripts/generate.php model products
```

Creates:

- `app/Models/Base/Product.php` (auto-generated)
- `app/Models/Product.php` (if doesn't exist)

### Generate Controller Only

```bash
php scripts/generate.php controller Product
```

Creates:

- `app/Controllers/Base/ProductController.php` (auto-generated)
- `app/Controllers/ProductController.php` (if doesn't exist)

### Generate Complete CRUD

```bash
php scripts/generate.php crud products --write
```

Creates:

- Model (Base + Concrete)
- Controller (Base + Concrete)
- Updates `routes/api.php`
- **Postman Collection** (NEW! 🎉)

### Generate CRUD for All Tables

```bash
php scripts/generate.php crud-all --write --overwrite
```

Generates complete CRUD for every table in the database, including Postman collections.

---

## Command Options

### `--write`

Actually write files (without this, it's dry-run mode).

```bash
# Dry run (preview only)
php scripts/generate.php crud products

# Actually create files
php scripts/generate.php crud products --write
```

### `--overwrite`

Overwrite existing base files.

```bash
php scripts/generate.php crud products --write --overwrite
```

**⚠️ Warning:** This overwrites Base files only. Concrete files are never overwritten.

---

## 📮 Postman Collection (NEW!)

### Automatic Postman Collection Generation

When you run `crud` or `crud-all` commands, Postman collection JSON files are automatically created in the `postman/` folder.

```bash
# Generate CRUD + Postman Collection
php scripts/generate.php crud products --write

# Output:
# 1. Generating Model...
# 2. Generating Controller...
# 3. Generating Routes...
# 4. Generating Postman Collection...
#    ✓ Postman Collection created at postman/product_api_collection.json
```

### What's Included in Collection

Each generated collection contains:

- ✅ **GET** - Get All (Paginated) with page/per_page params
- ✅ **GET** - Search with keyword param
- ✅ **GET** - Get All (No Pagination)
- ✅ **GET** - Get Single by ID
- ✅ **POST** - Create (with sample data)
- ✅ **PUT** - Update (with sample data)
- ✅ **DELETE** - Delete by ID

### Sample Data Generation

The generator intelligently creates sample request bodies based on your database schema:

```json
{
  "name": "Sample Name",
  "email": "user@example.com",
  "description": "This is a sample description",
  "price": 99.99,
  "stock": 10,
  "status": "active"
}
```

### Using the Collection

1. **Import to Postman:**
   - Open Postman
   - Click **Import**
   - Select file from `postman/` folder

2. **Configure Variables:**
   - `{{base_url}}` - Your API URL (default: `http://localhost:8000`)
   - `{{token}}` - Auth token (empty by default)

3. **Get Auth Token:**
   - Run `POST /api/auth/login`
   - Copy token from response
   - Set `{{token}}` variable

4. **Test Endpoints:**
   - All requests are ready to use
   - Protected endpoints automatically include Bearer token

### File Structure

```
postman/
├── README.md                      # Complete usage guide
├── product_api_collection.json    # Product endpoints
├── user_api_collection.json       # User endpoints
└── category_api_collection.json   # Category endpoints
```

See [postman/README.md](../../postman/README.md) for detailed usage guide.

---

## What Gets Generated

### Model Files

**Base Model** (`app/Models/Base/Product.php`):

```php
<?php

namespace App\Models\Base;

use Core\ActiveRecord;

class Product extends ActiveRecord
{
    protected string $table = 'products';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'name',
        'price',
        'description',
        'category_id',
        'status'
    ];

    protected array $hidden = [];

    protected bool $timestamps = true;
}
```

**Concrete Model** (`app/Models/Product.php`):

```php
<?php

namespace App\Models;

use App\Models\Base\Product as BaseProduct;

class Product extends BaseProduct
{
    // Add custom methods here
}
```

### Controller Files

**Base Controller** (`app/Controllers/Base/ProductController.php`):

```php
<?php

namespace App\Controllers\Base;

use Core\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Product();
    }

    public function index(): void { /* ... */ }
    public function show(): void { /* ... */ }
    public function store(): void { /* ... */ }
    public function update(): void { /* ... */ }
    public function destroy(): void { /* ... */ }
}
```

**Concrete Controller** (`app/Controllers/ProductController.php`):

```php
<?php

namespace App\Controllers;

use App\Controllers\Base\ProductController as BaseProductController;

class ProductController extends BaseProductController
{
    // Add custom methods here
}
```

### Route Registration

Added to `routes/api.php`:

```php
// Products routes
$router->get('/products', [ProductController::class, 'index']);
$router->get('/products/{id}', [ProductController::class, 'show']);
$router->post('/products', [ProductController::class, 'store']);
$router->put('/products/{id}', [ProductController::class, 'update']);
$router->delete('/products/{id}', [ProductController::class, 'destroy']);
```

---

## Generated Endpoints

### Standard CRUD Endpoints

| Method | Endpoint         | Action      | Description        |
| ------ | ---------------- | ----------- | ------------------ |
| GET    | `/products`      | `index()`   | List all products  |
| GET    | `/products/{id}` | `show()`    | Get single product |
| POST   | `/products`      | `store()`   | Create product     |
| PUT    | `/products/{id}` | `update()`  | Update product     |
| DELETE | `/products/{id}` | `destroy()` | Delete product     |

### Query Parameters

**List with pagination:**

```
GET /products?page=1&per_page=20
```

**Search:**

```
GET /products?search=laptop
```

**Combined:**

```
GET /products?page=1&per_page=20&search=laptop
```

---

## Workflow Examples

### Example 1: Generate Single Resource

```bash
# 1. Create database table
mysql -u root -p rest_api_db

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    category_id INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

exit;

# 2. Generate CRUD + Postman Collection
php scripts/generate.php crud products --write

# 3. Import postman/product_api_collection.json to Postman

# 4. Test endpoints using Postman
```

### Example 2: Generate All Resources

```bash
# Generate CRUD for all tables
php scripts/generate.php crud-all --write --overwrite

# This creates Models, Controllers, and Routes for:
# - users
# - products
# - categories
# - orders
# - etc.
```

### Example 3: Regenerate After Schema Change

```bash
# After adding new columns to products table
php scripts/generate.php crud products --write --overwrite

# This updates:
# - app/Models/Base/Product.php (with new columns in $fillable)
# - app/Controllers/Base/ProductController.php (if needed)
# - Concrete files remain unchanged
```

---

## Customization After Generation

### Add Custom Methods to Model

Edit `app/Models/Product.php`:

```php
<?php

namespace App\Models;

use App\Models\Base\Product as BaseProduct;

class Product extends BaseProduct
{
    /**
     * Get active products
     */
    public function getActive(): array
    {
        return $this->where(['status' => 'active'])->get();
    }

    /**
     * Get products by category
     */
    public function getByCategory(int $categoryId): array
    {
        return $this->where(['category_id' => $categoryId])->get();
    }
}
```

### Add Custom Endpoints to Controller

Edit `app/Controllers/ProductController.php`:

```php
<?php

namespace App\Controllers;

use App\Controllers\Base\ProductController as BaseProductController;

class ProductController extends BaseProductController
{
    /**
     * GET /products/featured
     */
    public function featured(): void
    {
        $products = $this->model->where(['is_featured' => 1])
            ->limit(10)
            ->get();

        $this->jsonResponse($products);
    }
}
```

### Add Custom Routes

Edit `routes/api.php`:

```php
// Custom routes
$router->get('/products/featured', [ProductController::class, 'featured']);
$router->get('/products/category/{categoryId}', [ProductController::class, 'byCategory']);
```

---

## Advanced Usage

### Generate with Custom Table Prefix

If your tables have prefixes (e.g., `tbl_products`):

```bash
php scripts/generate.php crud tbl_products --write
```

This creates:

- Model: `TblProduct`
- Controller: `TblProductController`
- Routes: `/tbl_products`

### Skip Certain Tables

Edit `scripts/generate.php` to add skip logic:

```php
$skipTables = ['migrations', 'sessions', 'cache'];

if (in_array($tableName, $skipTables)) {
    continue;
}
```

---

## Generator Configuration

### Customize Templates

Generator templates are in `scripts/templates/`:

```
scripts/templates/
├── model_base.php.template
├── model_concrete.php.template
├── controller_base.php.template
└── controller_concrete.php.template
```

You can customize these templates to match your coding style.

---

## Best Practices

### 1. Always Use --write Flag

✅ **DO:**

```bash
# Preview first
php scripts/generate.php crud products

# Then write
php scripts/generate.php crud products --write
```

❌ **DON'T:**

```bash
# Forget --write and wonder why nothing happened
php scripts/generate.php crud products
```

### 2. Never Edit Base Files

✅ **DO:**

- Edit `app/Models/Product.php`
- Edit `app/Controllers/ProductController.php`

❌ **DON'T:**

- Edit `app/Models/Base/Product.php` (will be overwritten)
- Edit `app/Controllers/Base/ProductController.php` (will be overwritten)

### 3. Regenerate After Schema Changes

✅ **DO:**

```bash
# After adding columns to table
php scripts/generate.php crud products --write --overwrite
```

This updates `$fillable` array in base model.

### 4. Use Meaningful Table Names

✅ **DO:**

- `products`
- `categories`
- `user_profiles`

❌ **DON'T:**

- `tbl1`
- `data`
- `temp`

---

## Troubleshooting

### Common Issues

| Issue                  | Solution                            |
| ---------------------- | ----------------------------------- |
| Files not created      | Add `--write` flag                  |
| Base files not updated | Add `--overwrite` flag              |
| Table not found        | Check database connection in `.env` |
| Permission denied      | Check directory permissions         |

### Debug Mode

Enable debug output:

```bash
# Set in .env
APP_DEBUG=true

# Run generator
php scripts/generate.php crud products --write
```

---

## Complete Workflow

### From Database to Working API

```bash
# 1. Create database table
mysql -u root -p rest_api_db < database/schema.sql

# 2. Generate CRUD
php scripts/generate.php crud products --write

# 3. Customize model (optional)
# Edit app/Models/Product.php

# 4. Customize controller (optional)
# Edit app/Controllers/ProductController.php

# 5. Add custom routes (optional)
# Edit routes/api.php

# 6. Test API
curl http://localhost:8085/products

# 7. Done! 🎉
```

---

## Next Steps

1. **Models** - [MODELS.md](MODELS.md)
2. **Controllers** - [CONTROLLERS.md](CONTROLLERS.md)
3. **API Testing** - [../03-advanced/API_TESTING.md](../03-advanced/API_TESTING.md)

---

**Previous:** [← Controllers Guide](CONTROLLERS.md) | **Next:** [Query Builder →](QUERY_BUILDER.md)
