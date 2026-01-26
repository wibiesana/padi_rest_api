# Flexible Response Format Guide

This document explains the new flexible response system in Padi REST API Framework that allows controllers to return data directly without worrying about response formatting.

## Overview

The framework automatically formats controller return values into consistent API responses. Controllers can return data directly, and the framework handles response formatting based on the configured format.

## How It Works

### 1. Return Data Directly

Controllers simply return the data they want to send:

```php
public function show()
{
    $user = $this->model->find($id);

    if (!$user) {
        throw new \Exception('User not found', 404);
    }

    return $user; // Framework handles the rest
}
```

### 2. Auto-Formatting

The Router captures controller return values and automatically formats them based on the `RESPONSE_FORMAT` environment variable:

- **`full`** (default): Standard framework response with `success`, `message`, `data`
- **`simple`**: Minimal response with `status`, `message`, `data`
- **`raw`**: Direct data return without wrapper

### 3. Exception Handling

Throw exceptions for errors - they're automatically converted to proper error responses:

```php
public function update()
{
    $user = $this->model->find($id);

    if (!$user) {
        throw new \Exception('User not found', 404);
    }

    if (!$this->canEdit($user)) {
        throw new \Exception('Permission denied', 403);
    }

    $this->model->update($id, $data);
    return $this->model->find($id);
}
```

## Response Formats

### Full Format (Default)

When `RESPONSE_FORMAT=full` in `.env`:

**Collection:**

```json
{
  "success": true,
  "message": "Success",
  "message_code": "SUCCESS",
  "item": [
    { "id": 1, "name": "Item 1" },
    { "id": 2, "name": "Item 2" }
  ],
  "meta": {
    "total": 25,
    "per_page": 10,
    "current_page": 1
  }
}
```

**Single Item:**

```json
{
  "success": true,
  "message": "Success",
  "message_code": "SUCCESS",
  "item": {
    "id": 1,
    "name": "Item 1",
    "created_at": "2026-01-26"
  }
}
```

### Simple Format

When `RESPONSE_FORMAT=simple` in `.env`:

```json
{
  "status": "success",
  "message": "Success",
  "data": {
    "id": 1,
    "name": "Item 1"
  }
}
```

### Raw Format

When `RESPONSE_FORMAT=raw` in `.env`:

```json
{
  "id": 1,
  "name": "Item 1",
  "created_at": "2026-01-26"
}
```

## Controller Helper Methods

While the main approach is returning data directly, some helper methods are available for specific cases:

### Status Code Control

```php
public function store()
{
    $id = $this->model->create($data);
    $item = $this->model->find($id);

    $this->setStatusCode(201); // Set status before return
    return $item;
}

// Or use helper
public function store()
{
    $id = $this->model->create($data);
    return $this->created($this->model->find($id)); // Auto 201 status
}
```

### Empty Responses

```php
public function destroy()
{
    $this->model->delete($id);

    $this->setStatusCode(204); // No content
    return null; // or return nothing
}

// Or use helper
public function destroy()
{
    $this->model->delete($id);
    return $this->noContent(); // Auto 204 status
}
```

### Format-Specific Responses

```php
public function getFormatted()
{
    $data = $this->model->find($id);

    // Force simple format regardless of env setting
    return $this->simple($data, 'success', 'USER_FOUND');

    // Force raw format
    return $this->raw($data);
}
```

## Complete Controller Examples

### Basic CRUD Controller

```php
class ProductController extends Controller
{
    // GET /products
    public function index()
    {
        $page = (int)$this->request->query('page', 1);
        $perPage = (int)$this->request->query('per_page', 10);

        return $this->model->paginate($page, $perPage);
    }

    // GET /products/all
    public function all()
    {
        return $this->model->all();
    }

    // GET /products/{id}
    public function show()
    {
        $product = $this->model->find($this->request->param('id'));

        if (!$product) {
            throw new \Exception('Product not found', 404);
        }

        return $product;
    }

    // POST /products
    public function store()
    {
        $validated = $this->validate([
            'name' => 'required|max:100',
            'price' => 'required|numeric',
            'stock' => 'required|integer'
        ]);

        $id = $this->model->create($validated);

        $this->setStatusCode(201);
        return $this->model->find($id);
    }

    // PUT /products/{id}
    public function update()
    {
        $id = $this->request->param('id');
        $product = $this->model->find($id);

        if (!$product) {
            throw new \Exception('Product not found', 404);
        }

        $validated = $this->validate([
            'name' => 'max:100',
            'price' => 'numeric',
            'stock' => 'integer'
        ]);

        $this->model->update($id, $validated);
        return $this->model->find($id);
    }

    // DELETE /products/{id}
    public function destroy()
    {
        $product = $this->model->find($this->request->param('id'));

        if (!$product) {
            throw new \Exception('Product not found', 404);
        }

        $this->model->delete($product['id']);

        $this->setStatusCode(204);
        return null;
    }
}
```

## Error Handling

All exceptions are automatically converted to proper error responses:

### Exception Response Format

**404 Not Found:**

```json
{
  "success": false,
  "message": "Product not found",
  "data": null,
  "status_code": 404
}
```

**403 Forbidden:**

```json
{
  "success": false,
  "message": "Permission denied",
  "data": null,
  "status_code": 403
}
```

**422 Validation Error:**

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required"],
    "price": ["The price must be numeric"]
  },
  "status_code": 422
}
```

## Best Practices

### ✅ DO

```php
// Return data directly
return $products;

// Throw exceptions for errors
throw new \Exception('Not found', 404);

// Use helpers for specific status codes
return $this->created($newProduct);
return $this->noContent();

// Set status when needed
$this->setStatusCode(201);
return $product;
```

### ❌ DON'T

```php
// Don't use old methods (deprecated)
$this->success($data);     // ❌ Use return $data
$this->single($item);      // ❌ Use return $item
$this->collection($items); // ❌ Use return $items
$this->notFound('Error');  // ❌ Use throw new \Exception('Error', 404)
$this->forbidden();        // ❌ Use throw new \Exception('Error', 403)

// Don't return wrapped data unnecessarily
return ['data' => $products]; // ❌ Use return $products
```

## Environment Configuration

Control response format via environment variables:

```env
# .env
RESPONSE_FORMAT=full    # Default: full framework response
RESPONSE_FORMAT=simple  # Minimal response format
RESPONSE_FORMAT=raw     # Direct data output
```

## Migration from Old Format

If you have existing controllers using old methods, update them:

```php
// OLD (deprecated)
public function index(): void
{
    $products = $this->model->all();
    $this->collection($products);
}

// NEW (recommended)
public function index()
{
    return $this->model->all();
}

// OLD (deprecated)
public function show(): void
{
    $product = $this->model->find($id);
    if (!$product) {
        $this->notFound('Product not found');
    }
    $this->single($product);
}

// NEW (recommended)
public function show()
{
    $product = $this->model->find($id);
    if (!$product) {
        throw new \Exception('Product not found', 404);
    }
    return $product;
}
```

The new approach is simpler, more intuitive, and provides better flexibility for different response formats!
