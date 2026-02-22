# 🗃️ ActiveRecord Guide

**Padi REST API Framework v2.0.0**

The `ActiveRecord` class is the heart of the framework's data layer. It provides a powerful, fluent interface for database interactions, relationship management, and automated auditing.

---

## 📋 Table of Contents

- [Core Concepts](#core-concepts)
- [Basic CRUD](#basic-crud)
- [Pagination](#pagination)
- [Advanced Operations](#advanced-operations)
- [Relationships & Eager Loading](#relationships--eager-loading)
- [Model Security (Hidden Fields)](#model-security-hidden-fields)
- [Automatic Auditing](#automatic-auditing)
- [Default Ordering](#default-ordering)
- [Lifecycle Hooks (Yii Style)](#lifecycle-hooks)
- [Database Connection Switching](#database-connection-switching)

---

## 🛠️ Core Concepts

### Model Definition

Every model in the system should extend `Core\ActiveRecord`. The base class handles the heavy lifting of mapping your class to a database table.

```php
namespace App\Models;

use Core\ActiveRecord;

class Product extends ActiveRecord
{
    protected string $table = 'products';
    protected string|array $primaryKey = 'id';
    protected array $fillable = ['name', 'price', 'description'];
    protected array $hidden = ['deleted_at'];
}
```

---

## 📦 Basic CRUD

### Retrieving Data

```php
// Find by ID
$product = (new Product())->find(1);

// Get all records
$all = (new Product())->all();

// Filtered results
$active = (new Product())->where(['status' => 'active']);
```

### Writing Data

```php
// Create
$id = (new Product())->create([
    'name' => 'Premium Coffee',
    'price' => 15.00
]);

// Update
(new Product())->update($id, ['price' => 14.50]);

// Delete
(new Product())->delete($id);
```

---

## 📄 Pagination

The `paginate()` method automatically handles offset calculations, record counting, and returns a structured metadata object compatible with most frontend tables.

```php
// Get page from request, default to 1
$page = (int)$this->request->query('page', 1);
$perPage = (int)$this->request->query('per_page', 15);

// 1. Simple pagination
$results = (new Product())->paginate($page, $perPage);

// 2. Pagination with conditions and custom ordering
$results = (new Product())->paginate(
    $page,
    $perPage,
    ['status' => 'active'],
    'created_at DESC'
);
```

### Response Structure

The framework returns a standardized object:

```json
{
  "data": [ ... ],
  "meta": {
    "total": 50,
    "per_page": 15,
    "current_page": 1,
    "last_page": 4,
    "from": 1,
    "to": 15
  }
}
```

---

## 🚀 Advanced Operations

### Batch Operations

Perform high-performance bulk inserts or updates.

```php
// Batch Insert
(new Product())->batchInsert([
    ['name' => 'Item A', 'price' => 10],
    ['name' => 'Item B', 'price' => 20],
]);

// Update All matching conditions
$affectedRows = (new Product())->updateAll(
    ['status' => 'discontinued'],
    ['stock' => 0]
);
```

### Composite Primary Keys

The framework supports composite keys for complex database schemas.

```php
protected string|array $primaryKey = ['user_id', 'role_id'];

// Finding a record with composite keys
$record = $model->find(['user_id' => 1, 'role_id' => 2]);
// Or using underscore notation
$record = $model->find("1_2");
```

### Raw Queries

When the builder isn't enough, execute raw SQL safely.

```php
$results = $model->query("SELECT * FROM products WHERE price > ?", [100]);
```

---

## 🔗 Relationships & Eager Loading

### Defining Relations

```php
class Category extends ActiveRecord {
    public function products() {
        return $this->hasMany(Product::class, 'category_id');
    }
}

class Product extends ActiveRecord {
    public function category() {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function tags() {
        return $this->belongsToMany(
            Tag::class,
            'product_tags', // Pivot table
            'product_id',   // Foreign key for this model
            'tag_id'        // Foreign key for related model
        );
    }
}
```

### Eager Loading (N+1 Solution)

Use `with()` to load relationships efficiently.

```php
// Basic eager loading
$products = (new Product())->with('category')->all();

// Nested eager loading (dot notation)
$products = (new Product())->with('category.parent')->all();

// Specific columns (colon notation)
$products = (new Product())->with('category:id,name')->all();
```

---

## 🔒 Model Security (Hidden Fields)

Prevent sensitive data from leaking into your API responses by defining the `$hidden` property. These fields are automatically removed during `find()`, `all()`, `where()`, and `paginate()`.

```php
class User extends ActiveRecord {
    protected array $hidden = [
        'password',
        'remember_token',
        'api_key'
    ];
}
```

---

## 🛡️ Automatic Auditing

Enabled by default (`$useAudit = true`), the framework automatically populates audit fields.

| Field        | Description                                    |
| :----------- | :--------------------------------------------- |
| `created_at` | Timestamp when record is created.              |
| `updated_at` | Timestamp when record is modified.             |
| `created_by` | User ID from `Auth::userId()` on creation.     |
| `updated_by` | User ID from `Auth::userId()` on modification. |

### Configuration

```php
class MyModel extends ActiveRecord {
    protected bool $useAudit = true;
    protected string $timestampFormat = 'unix'; // or 'datetime'

    // Custom field mapping
    protected array $auditFields = [
        'created_at' => 'created_on',
        'created_by' => 'author_id'
    ];
}
```

---

## 🔢 Default Ordering

You can define a global default order for your model so you don't have to specify it in every query.

```php
class Product extends ActiveRecord {
    // Automatically applies "ORDER BY sort_order ASC" to all queries
    protected ?string $defaultOrder = 'sort_order ASC';
}
```

---

## 🔄 Lifecycle Hooks

Inspired by **Yii Framework**, these hooks allow you to intercept data at various stages.

| Hook           | Execution Time            | Purpose                                     |
| :------------- | :------------------------ | :------------------------------------------ |
| `afterLoad`    | After fetching from DB    | Data transformation (e.g. JSON to Array).   |
| `beforeSave`   | Before INSERT/UPDATE      | Validation, hashing, calculations.          |
| `afterSave`    | After successful save     | Logging, emails, secondary updates.         |
| `beforeDelete` | Before record deletion    | Dependency checks, blocking system records. |
| `afterDelete`  | After successful deletion | File cleanup, log archival.                 |

### Example: Password Hashing

```php
protected function beforeSave(array &$data, bool $insert): bool
{
    if (isset($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
    }
    return true; // Return false to abort save
}
```

### Example: Post-Load Processing

```php
public function afterLoad(array &$items): void
{
    foreach ($items as &$item) {
        $item['full_url'] = "https://cdn.com/" . $item['path'];
    }
}
```

---

## 🌐 Database Connection Switching

Support for multi-database architectures.

```php
class ExternalModel extends ActiveRecord {
    // Uses the 'pgsql' connection defined in config/database.php
    protected ?string $connection = 'pgsql';
}
```

---

## 💡 Best Practices

1. **Use findQuery()**: For complex chaining, use `$model->findQuery()` which returns a `Query` builder instance.
2. **Hide Sensitive Data**: Always add `password`, `token`, etc. to the `$hidden` array.
3. **Reference in beforeSave**: The `$data` parameter is passed by reference (`&$data`). Use it to modify values before they hit the database.
4. **Fail Fast**: Return `false` in `beforeDelete` if a record has active dependencies to maintain data integrity.

---

**Last Updated:** 2026-02-22  
**Version:** 2.0.0
