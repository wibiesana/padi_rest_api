# 📦 Models Guide

**Padi REST API Framework v2.0.0**

---

## Overview

Models in Padi REST API extend `Wibiesana\Padi\Core\ActiveRecord`. The framework uses a simplified model structure where each table is represented by a single model file in `app/Models/`.

---

## Model Structure

### Directory Organization

```
app/Models/
├── Base/              # Auto-generated base classes (do not edit)
│   ├── User.php
│   ├── Product.php
│   └── Category.php
├── User.php           # Concrete class (custom logic goes here)
├── Product.php        # Concrete class
└── Category.php       # Concrete class
```

The framework uses a **Base/Concrete** pattern:

1. **Base Models**: Generated in `app/Models/Base/`. These files are overwritten whenever you regenerate the model. They contain all the auto-detected fields, relationships, and search logic.
2. **Concrete Models**: Created in `app/Models/`. These files are only created if they don't exist. This is where you should add your custom logic, validation hooks, and extra methods.

---

## Model Features

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
$searched = $product->searchPaginate($keyword);    // Search with pagination

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
php scripts/generate.php model products --write

# This creates:
# - app/Models/Base/Product.php (Always overwritten)
# - app/Models/Product.php      (Created only if missing)
```

The generator will check if the file already exists. Use the `--force` flag if you want to overwrite an existing model:

```bash
php scripts/generate.php model products --write --force
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
php scripts/generate.php model products --write
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
protected string|array $primaryKey = 'id';
```

Default: `id`. Supports composite keys as an array.

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
    'remember_token'
];
```

Fields excluded from JSON responses.

---

## Audit Fields (created_by / updated_by)

When your table contains audit columns, the framework can automatically populate
who created/updated a record and when. This is handled by `Wibiesana\Padi\Core\ActiveRecord`
and enabled by default.

### Behavior

- If the table has any of these columns they will be auto-populated: `created_at`, `updated_at`, `created_by`, `updated_by`.
- `created_at`/`updated_at` receive the current server timestamp.
- `created_by`/`updated_by` are set using `Wibiesana\Padi\Core\Auth::userId()` when a user is authenticated.
- Automatic detection uses PDO column metadata and is cached per table.

---

## Query Methods

### Important: ActiveRecord vs Query Builder

**ActiveRecord methods (`$model->where()`):**

- Return **array** directly.
- Cannot be chained with `->get()`, `->orderBy()`, `->limit()`.
- Simple and convenient for basic queries.

**Query Builder (`Wibiesana\Padi\Core\Query`):**

- Return **Query instance** for method chaining.
- Accessed via `Product::findQuery()`.

```php
// ✅ ActiveRecord - returns array directly
$products = $product->where(['status' => 'active']);

// ✅ Query Builder - for chaining
$products = Product::findQuery()
    ->where('status = :status', ['status' => 'active'])
    ->orderBy('price DESC')
    ->limit(10)
    ->all();
```

---

### Defining Relationships

Relationships are defined in the model using `belongsTo`, `hasMany`, or `hasOne`.

```php
namespace App\Models;

use Wibiesana\Padi\Core\ActiveRecord;

class Product extends ActiveRecord
{
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
```

### Automatic Generation ⚡

When you use the [Code Generator](CODE_GENERATOR.md), the framework automatically detects relationships based on your database foreign keys:

- **Direct Relation**: Creates `belongsTo` for table columns ending in `_id`.
- **Inverse Relation**: Automatically scans other tables for foreign keys pointing to your model and creates `hasMany()` or `hasOne()` (if a unique index exists).
- **Naming**: Pluralizes `hasMany` relationships automatically (e.g., `user->posts()`).

---

## Best Practices

### 1. Protect Sensitive Data

Always use the `$hidden` property to exclude sensitive fields like passwords or internal tokens from API responses.

### 2. Use Transactions for multiple writes

When performing multiple database operations that depend on each other, use `Wibiesana\Padi\Core\Database::transaction()`.

### 3. Let Generator do the Heavy Lifting

Use the built-in generator to create models, controllers, and even Postman collections to speed up your development process.
