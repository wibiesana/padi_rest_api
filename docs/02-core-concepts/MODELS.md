# 📦 Models Guide

**Padi REST API Framework v2.0**

---

## Overview

Models in Padi REST API extend `Core\ActiveRecord` and use a **Base/Concrete pattern** for automatic code generation while preserving custom logic.

---

## Model Structure

### Directory Organization

```
app/Models/
├── Base/
│   ├── User.php       # Auto-generated, always overwritten
│   ├── Product.php    # Auto-generated, always overwritten
│   └── Category.php   # Auto-generated, always overwritten
├── User.php           # Custom logic, never overwritten
├── Product.php        # Custom logic, never overwritten
└── Category.php       # Custom logic, never overwritten
```

### Base vs Concrete Models

| Type               | Location           | Purpose               | Overwritten?             |
| ------------------ | ------------------ | --------------------- | ------------------------ |
| **Base Model**     | `app/Models/Base/` | Auto-generated CRUD   | ✅ Yes (on regeneration) |
| **Concrete Model** | `app/Models/`      | Custom business logic | ❌ Never                 |

---

## Base Model Features

### Automatic CRUD Operations

All models automatically have these methods:

```php
use App\Models\Product;

$product = new Product();

// READ Operations
$all = $product->all();                    // Get all records
$one = $product->find($id);                // Find by ID
$filtered = $product->where(['status' => 1]); // Where conditions
$paginated = $product->paginate($page, $perPage); // Pagination
$searched = $product->search($keyword);    // Search

// CREATE
$new = $product->create([
    'name' => 'New Product',
    'price' => 99.99
]);

// UPDATE
$updated = $product->update($id, [
    'price' => 89.99
]);

// DELETE
$deleted = $product->delete($id);
```

---

## Creating Models

### Method 1: Auto-Generate from Database

```bash
# Generate model for existing table
php scripts/generate.php model products

# This creates:
# - app/Models/Base/Product.php (auto-generated)
# - app/Models/Product.php (if doesn't exist)
```

### Method 2: Manual Creation

**Step 1:** Create database table

```sql
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Step 2:** Generate model

```bash
php scripts/generate.php model products
```

**Step 3:** Customize concrete model

Edit `app/Models/Product.php`:

```php
<?php

namespace App\Models;

use App\Models\Base\Product as BaseProduct;

class Product extends BaseProduct
{
    // Add custom methods here

    public function getActiveProducts(): array
    {
        return $this->where(['status' => 'active'])->get();
    }

    public function getExpensiveProducts(float $minPrice): array
    {
        return $this->where(['price >=' => $minPrice])->get();
    }
}
```

---

## Model Properties

### Table Name

```php
protected string $table = 'products';
```

Auto-detected from class name (pluralized).

### Primary Key

```php
protected string $primaryKey = 'id';
```

Default: `id`

### Fillable Fields

```php
protected array $fillable = [
    'name',
    'price',
    'description',
    'status'
];
```

Fields that can be mass-assigned.

### Hidden Fields

```php
protected array $hidden = [
    'password',
    'api_key'
];
```

Fields excluded from JSON responses.

### Timestamps

```php
protected bool $timestamps = true;
```

Automatically manage `created_at` and `updated_at`.

---

## Query Methods

### Basic Queries

```php
// Get all records
$products = $product->all();

// Find by ID
$product = $product->find(1);

// Where conditions
$active = $product->where(['status' => 'active'])->get();

// Multiple conditions
$filtered = $product->where([
    'status' => 'active',
    'price >' => 50
])->get();

// Order by
$sorted = $product->orderBy('price', 'DESC')->get();

// Limit
$limited = $product->limit(10)->get();
```

### Advanced Queries

```php
// Pagination
$page = 1;
$perPage = 20;
$paginated = $product->paginate($page, $perPage);
// Returns: ['data' => [...], 'total' => 100, 'page' => 1, 'per_page' => 20]

// Search (searches all text fields)
$results = $product->search('laptop');

// Count
$count = $product->where(['status' => 'active'])->count();

// First record
$first = $product->where(['status' => 'active'])->first();

// Exists
$exists = $product->where(['name' => 'Product'])->exists();
```

### Query Builder Integration

```php
use Core\Query;

// Complex query with multiple conditions
$results = Query::table('products')
    ->select(['id', 'name', 'price'])
    ->where('status', '=', 'active')
    ->andWhere('price', '>', 50)  // Use andWhere() for additional conditions
    ->orderBy('price', 'DESC')
    ->limit(10)
    ->get();

// With OR conditions
$results = Query::table('products')
    ->where('status', '=', 'active')
    ->orWhere('is_featured', '=', 1)
    ->get();

// With joins
$results = Query::table('products')
    ->join('categories', 'products.category_id', '=', 'categories.id')
    ->select(['products.*', 'categories.name as category_name'])
    ->get();
```

See [QUERY_BUILDER.md](QUERY_BUILDER.md) for more details.

---

## Relationships

### One-to-Many

```php
// Product belongs to Category
class Product extends BaseProduct
{
    public function category(): ?array
    {
        $categoryModel = new Category();
        return $categoryModel->find($this->category_id);
    }
}

// Category has many Products
class Category extends BaseCategory
{
    public function products(): array
    {
        $productModel = new Product();
        return $productModel->where(['category_id' => $this->id])->get();
    }
}
```

### Many-to-Many

```php
// Product has many Tags through product_tags
class Product extends BaseProduct
{
    public function tags(): array
    {
        return Query::table('tags')
            ->join('product_tags', 'tags.id', '=', 'product_tags.tag_id')
            ->where('product_tags.product_id', '=', $this->id)
            ->get();
    }
}
```

---

## Model Lifecycle Hooks

### Available Hooks

```php
class Product extends BaseProduct
{
    // Before save (create or update)
    protected function beforeSave(array &$data): void
    {
        // Modify data before saving
        $data['slug'] = $this->generateSlug($data['name']);
    }

    // After save
    protected function afterSave(array $data, $id): void
    {
        // Perform actions after saving
        $this->clearCache();
    }

    // Before delete
    protected function beforeDelete($id): void
    {
        // Check if can delete
        if ($this->hasOrders($id)) {
            throw new \Exception('Cannot delete product with orders');
        }
    }

    // After delete
    protected function afterDelete($id): void
    {
        // Cleanup after deletion
        $this->deleteRelatedImages($id);
    }
}
```

See [ACTIVE_RECORD_LIFECYCLE.md](ACTIVE_RECORD_LIFECYCLE.md) for details.

---

## Validation

### Define Validation Rules

```php
class Product extends BaseProduct
{
    protected function getValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'string',
            'status' => 'required|in:active,inactive',
            'category_id' => 'required|exists:categories,id'
        ];
    }
}
```

### Validation Rules Reference

| Rule                  | Example              | Description           |
| --------------------- | -------------------- | --------------------- |
| `required`            | `required`           | Field must be present |
| `string`              | `string`             | Must be string        |
| `numeric`             | `numeric`            | Must be number        |
| `email`               | `email`              | Must be valid email   |
| `min:n`               | `min:8`              | Minimum length/value  |
| `max:n`               | `max:255`            | Maximum length/value  |
| `in:a,b,c`            | `in:active,inactive` | Must be one of values |
| `exists:table,column` | `exists:users,id`    | Must exist in table   |
| `unique:table,column` | `unique:users,email` | Must be unique        |

---

## Custom Methods

### Example: Product Model

```php
<?php

namespace App\Models;

use App\Models\Base\Product as BaseProduct;
use Core\Query;

class Product extends BaseProduct
{
    /**
     * Get active products only
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

    /**
     * Get products in price range
     */
    public function getByPriceRange(float $min, float $max): array
    {
        return Query::table($this->table)
            ->where('price', '>=', $min)
            ->where('price', '<=', $max)
            ->get();
    }

    /**
     * Get featured products
     */
    public function getFeatured(int $limit = 10): array
    {
        return $this->where(['is_featured' => 1])
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Search products with filters
     */
    public function searchWithFilters(string $keyword, array $filters = []): array
    {
        $query = Query::table($this->table);

        // Search keyword
        if (!empty($keyword)) {
            $query->where('name', 'LIKE', "%{$keyword}%");
        }

        // Apply filters
        if (!empty($filters['category_id'])) {
            $query->where('category_id', '=', $filters['category_id']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        return $query->get();
    }
}
```

---

## Best Practices

### 1. Use Base/Concrete Pattern

✅ **DO:**

- Put custom logic in concrete model (`app/Models/Product.php`)
- Let generator handle base model (`app/Models/Base/Product.php`)

❌ **DON'T:**

- Edit base models (they will be overwritten)
- Put business logic in controllers

### 2. Use Fillable/Hidden

✅ **DO:**

```php
protected array $fillable = ['name', 'email', 'phone'];
protected array $hidden = ['password', 'api_key'];
```

❌ **DON'T:**

```php
// Allow mass assignment of sensitive fields
protected array $fillable = ['*'];
```

### 3. Use Lifecycle Hooks

✅ **DO:**

```php
protected function beforeSave(array &$data): void
{
    $data['slug'] = $this->generateSlug($data['name']);
}
```

❌ **DON'T:**

```php
// Duplicate logic in controller
$data['slug'] = $this->generateSlug($data['name']);
$product->create($data);
```

### 4. Use Query Builder for Complex Queries

✅ **DO:**

```php
use Core\Query;

$results = Query::table('products')
    ->join('categories', 'products.category_id', '=', 'categories.id')
    ->where('products.status', '=', 'active')
    ->get();
```

❌ **DON'T:**

```php
// Raw SQL queries (unless absolutely necessary)
$sql = "SELECT * FROM products WHERE status = 'active'";
```

---

## Examples

### Complete CRUD Example

```php
use App\Models\Product;

$product = new Product();

// CREATE
$newProduct = $product->create([
    'name' => 'Laptop',
    'price' => 999.99,
    'description' => 'High-performance laptop',
    'status' => 'active'
]);

// READ
$allProducts = $product->all();
$oneProduct = $product->find(1);
$activeProducts = $product->where(['status' => 'active'])->get();

// UPDATE
$updated = $product->update(1, [
    'price' => 899.99
]);

// DELETE
$deleted = $product->delete(1);

// PAGINATION
$page1 = $product->paginate(1, 20);

// SEARCH
$results = $product->search('laptop');
```

---

## Troubleshooting

### Common Issues

| Issue                     | Solution                                        |
| ------------------------- | ----------------------------------------------- |
| Model not found           | Run `php scripts/generate.php model table_name` |
| Mass assignment error     | Add field to `$fillable` array                  |
| Validation fails          | Check validation rules in model                 |
| Relationship returns null | Verify foreign key exists                       |

---

## Next Steps

1. **Controllers** - [CONTROLLERS.md](CONTROLLERS.md)
2. **Query Builder** - [QUERY_BUILDER.md](QUERY_BUILDER.md)
3. **Database Transactions** - [DATABASE_TRANSACTIONS.md](DATABASE_TRANSACTIONS.md)
4. **Active Record Lifecycle** - [ACTIVE_RECORD_LIFECYCLE.md](ACTIVE_RECORD_LIFECYCLE.md)

---

**Previous:** [← Authentication](AUTHENTICATION.md) | **Next:** [Controllers Guide →](CONTROLLERS.md)
