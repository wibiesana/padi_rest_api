# 📦 API Resources Guide

**Padi REST API Framework v1.0.0**

---

## Overview

API Resources (Transformers) act as a middleware layer between your **Models** and the **JSON Response**. They allow you to transform your data, hide sensitive fields, and format relationships before sending them to the client.

---

## Resource Structure

### Directory Organization

```text
app/Resources/
├── ProductResource.php
├── UserResource.php
└── CategoryResource.php
```

Unlike Controllers and Models, Resources do not typically use a Base/Concrete pattern as they are simple transformation classes.

---

## Creating Resources

### Method 1: Auto-Generate

You can generate a resource using the CLI generator:

```bash
# Generate resource for a specific model
php scripts/generate.php resource Product
```

### Method 2: Manual Creation

Create a class in `app/Resources/` that extends `Core\Resource`:

```php
<?php

namespace App\Resources;

use Core\Resource;

class ProductResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param mixed $resource The model or array to transform
     * @return array
     */
    public function toArray($resource): array
    {
        return [
            'id' => (int) $this->id,
            'name' => strtoupper($this->name),
            'price_formatted' => 'IDR ' . number_format($this->price, 0, ',', '.'),
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => date('d-m-Y', strtotime($this->created_at)),

            // Conditional relationship
            'category' => $this->whenLoaded('category'),
        ];
    }
}
```

---

## Core Methods

### `toArray($resource)`

This is the main method where you define the output structure. You can access properties of the resource directly using `$this->property` because the base class uses the `__get` magic method.

### `make($data)`

Used for transforming a single item (object or array).

```php
$user = User::find(1);
return UserResource::make($user);
```

### `collection($data)`

Used for transforming a list of items. It automatically detects if the data is a standard array or a paginated result from `paginate()`.

```php
$users = User::all();
return UserResource::collection($users);

// Paginated result
$paginatedUsers = User::paginate(1, 10);
return UserResource::collection($paginatedUsers);
```

---

## Helper Methods

### `whenLoaded(string $key, $default = null)`

This method helps you safely include relationships that might not have been eager-loaded, preventing "Undefined index" errors.

```php
public function toArray($resource): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'author' => $this->whenLoaded('author'),
        'comments' => $this->whenLoaded('comments', []), // Default to empty array if not loaded
    ];
}
```

---

## Usage in Controllers

To use a resource in your controller, simply return the resource instance or collection. The `Router` will automatically detect the resource and call `resolve()` to get the transformed array.

```php
namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\Product;
use App\Resources\ProductResource;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->get();

        // Return a collection of resources
        return ProductResource::collection($products);
    }

    public function show()
    {
        $id = $this->request->param('id');
        $product = Product::find($id);

        if (!$product) {
            throw new \Exception("Product not found", 404);
        }

        // Return a single resource
        return ProductResource::make($product);
    }
}
```

---

## Best Practices

### 1. Separation of Concerns

Keep your models focused on data access and your resources focused on data presentation. Don't put complex business logic inside `toArray()`.

### 2. Type Casting

Use type casting in your resource to ensure the API consumer receives the correct data types (e.g., integers for IDs, booleans for flags).

```php
'id' => (int) $this->id,
'is_active' => (bool) $this->is_active,
```

### 3. Handle Empty States

Resources safely handle `null` values passed to `make()`, returning an empty array by default.

---

## Next Steps

1. **Controllers Guide** - [CONTROLLERS.md](CONTROLLERS.md)
2. **Code Generator** - [CODE_GENERATOR.md](CODE_GENERATOR.md)
3. **API Reference** - [../05-examples/API_REFERENCE.md](../05-examples/API_REFERENCE.md)

---

**Previous:** [← Response Structure](RESPONSE_STRUCTURE.md) | **Next:** [Code Generator →](CODE_GENERATOR.md)
