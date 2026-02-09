# API Response Structure & Flexible Formats

The Padi REST API Framework provides a powerful and flexible response system that allows developers to return data directly from controllers while maintaining a consistent API structure.

## 🌟 Key Features

- **Direct Return**: Simply return an array, object, or model from your controller.
- **Multiple Formats**: Support for `full`, `simple`, and `raw` response formats.
- **Dynamic Selection**: Switch formats via Environment Variables or HTTP Headers.
- **Auto-Detection**: Automatic status code and resource type detection.
- **FrankenPHP Ready**: Optimized for high-performance worker modes.

---

## 🚀 How It Works

### 1. Return Data Directly

In Padi, you don't need to call `$this->success()` for every response. The framework's router automatically captures your return value and formats it correctly.

```php
public function show($id)
{
    $user = $this->model->find($id);

    if (!$user) {
        throw new \Exception('User not found', 404);
    }

    return $user; // The framework handles the JSON wrapping
}
```

### 2. Supported Response Formats

#### **Full Format** (Default)

Standard framework structure with meta information.

```json
{
  "success": true,
  "message": "Success",
  "message_code": "SUCCESS",
  "item": { "id": 1, "name": "John Doe" }
}
```

#### **Simple Format**

A lightweight structure often used by modern frontend apps.

```json
{
  "status": "success",
  "code": "SUCCESS",
  "data": { "id": 1, "name": "John Doe" }
}
```

#### **Raw Format**

Direct data output without any wrapper.

```json
{
  "id": 1,
  "name": "John Doe"
}
```

---

## ⚙️ Configuration

### 1. Global Configuration (`.env`)

Set the default format for your entire application:

```env
RESPONSE_FORMAT=full    # options: full, simple, raw
```

### 2. Request-Based Switching (HTTP Header)

Client applications can request a specific format using the `X-Response-Format` header. This overrides the `.env` setting.

```http
X-Response-Format: simple
```

**Priority:** Header > Environment Variable > Default (full).

### 3. Per-Request Override (Controller)

You can force a specific format for a specific endpoint:

```php
public function export()
{
    $data = $this->model->all();
    return $this->raw($data); // Always returns raw data
}
```

---

## 🛠️ Controller Helper Methods

While returning data directly is recommended, these helpers provide extra control:

| Method               | Description                   | Example                         |
| :------------------- | :---------------------------- | :------------------------------ |
| `setStatusCode(int)` | Manually set HTTP status code | `$this->setStatusCode(201);`    |
| `created(any)`       | Return data with 201 status   | `return $this->created($item);` |
| `noContent()`        | Return empty 204 response     | `return $this->noContent();`    |
| `simple(data, ...)`  | Force simple format           | `return $this->simple($data);`  |
| `raw(data)`          | Force raw format              | `return $this->raw($data);`     |

---

## 🔍 Auto-Detection Features

The framework is smart enough to handle common scenarios automatically:

### 1. Status Code Detection

- If you return `null` or an empty result from a single-item query → Auto **404**.
- If you use `$this->created()` → Auto **201**.
- If you use `$this->noContent()` → Auto **204**.
- Unhandled exceptions → Auto **500** (or custom code from `Exception`).

### 2. Resource Type Detection

- **Collection**: If you return a numerical array or a paginated result, it's wrapped as a collection.
- **Single Item**: If you return an associative array or model, it's treated as a single object.

---

## 📝 Complete CRUD Example

```php
class ProductController extends Controller
{
    // GET /products - List with pagination
    public function index()
    {
        return $this->model->paginate(
            $this->request->query('page', 1),
            $this->request->query('per_page', 10)
        );
    }

    // POST /products - Create
    public function store()
    {
        $validated = $this->validate([
            'name' => 'required|string',
            'price' => 'required|numeric'
        ]);

        $id = $this->model->create($validated);
        return $this->created($this->model->find($id));
    }

    // GET /products/{id} - View
    public function show($id)
    {
        return $this->model->find($id); // Auto 404 if not found
    }

    // DELETE /products/{id} - Delete
    public function destroy($id)
    {
        $this->model->delete($id);
        return $this->noContent();
    }
}
```

---

## 🛑 Error Handling

Exceptions are automatically converted into standardized error responses:

#### **Validation Error (422)**

```json
{
  "success": false,
  "message": "Validation failed",
  "message_code": "VALIDATION_FAILED",
  "errors": {
    "email": ["The email field is required"]
  }
}
```

#### **Standard Error (400, 404, 500)**

```json
{
  "success": false,
  "message": "Resource not found",
  "message_code": "NOT_FOUND"
}
```

---

## 🏎️ FrankenPHP Worker Mode

This response system is fully architectural-compatible with **FrankenPHP Worker Mode**.

1. **No `exit()` calls**: The framework uses exceptions and returns instead of terminating the script.
2. **State Isolation**: Each response is handled by a fresh `Response` object even when the PHP process lives across multiple requests.
3. **Performance**: This non-blocking approach allows for 3-10x performance gains.

---

## 🔄 Migration Guide

If you are upgrading from an older version of Padi:

### Method Migration

| Old Way (Deprecated) ❌       | New Way ✅                          |
| :---------------------------- | :---------------------------------- |
| `$this->jsonResponse($data);` | `return $data;`                     |
| `$this->single($item);`       | `return $item;`                     |
| `$this->collection($items);`  | `return $items;`                    |
| `$this->notFound();`          | `throw new \Exception('...', 404);` |

---

## 🤖 Code Generator

The `php scripts/generate.php` tool has been updated to use this flexible format by default. Generated controllers will now use the "Direct Return" pattern and include try-catch blocks for robust error handling.

---

**Next:** [API Resources Guide →](RESOURCES.md)
