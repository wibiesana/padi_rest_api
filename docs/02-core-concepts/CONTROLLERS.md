# 🎮 Controllers Guide

**Padi REST API Framework v2.0.2**

Controllers in Padi REST API handle HTTP requests, perform business logic, and return responses. They serve as the "brain" of your application's endpoints.

---

## 📋 Table of Contents

- [Controller Structure](#controller-structure)
- [Base Controller Methods](#base-controller-methods)
- [Request Handling](#request-handling)
- [Response Methods](#response-methods)
- [Validation](#validation)
- [Role-Based Access (RBAC)](#role-based-access-rbac)
- [Overriding Base Methods](#overriding-base-methods)

---

## 🏗️ Controller Structure

The framework uses a **Base/Concrete pattern**. Base controllers are auto-generated from your database schema, while concrete controllers are where you add your custom logic.

### Directory Organization

```text
app/Controllers/
├── Base/
│   ├── ProductController.php  # ⚠️ Auto-generated, DO NOT EDIT
│   └── UserController.php     # ⚠️ Auto-generated, DO NOT EDIT
├── ProductController.php      # ✅ Custom logic, EDIT HERE
└── UserController.php         # ✅ Custom logic, EDIT HERE
```

### Standard CRUD Actions

Auto-generated Base controllers include:

- `index()`: List all (paginated)
- `all()`: List all (without pagination)
- `show()`: View single record
- `store()`: Create new record
- `update()`: Modify existing record
- `destroy()`: Delete record

---

## 📥 Request Handling

The `$this->request` object provides easy access to all incoming data.

### 1. Request Data

```php
// Get all input data (JSON, POST, or Query)
$data = $this->request->all();

// Get specific input with default value
$search = $this->request->input('search', '');

// Get query parameters only
$page = $this->request->query('page', 1);
```

### 2. Route Parameters

Parameters defined in the route (e.g., `/products/{id}`) are accessed via:

```php
$id = $this->request->param('id');
```

### 3. Authenticated User

If the route is protected by `AuthMiddleware`:

```php
$user = $this->request->user;
$userId = $user->user_id;
$role = $user->role;
```

---

## 📤 Response Methods

Thanks to the [Flexible Response Format](RESPONSE_STRUCTURE.md), you can return data directly. However, the controller provides helpers for specific needs.

### 1. Basic JSON

```php
public function custom() {
    return $this->json(['status' => 'ok']);
}
```

### 2. Standardized Helpers

These helpers automatically set the correct HTTP status code.

```php
// Return 201 Created
return $this->created($newRecord);

// Return 204 No Content
return $this->noContent();

// Return raw data (no wrapper)
return $this->raw($data);

// Database error with debug info
return $this->databaseError('Could not save data', $exception);
```

### 3. Status Code Control

```php
$this->setStatusCode(202); // Accepted
return ['message' => 'Processing...'];
```

---

## ✅ Validation

Validation is built directly into the controller and uses the framework's `Validator` engine.

```php
public function store()
{
    // Automatically uses $this->request->all()
    $validated = $this->validate([
        'name' => 'required|max:100',
        'price' => 'required|numeric',
        'category_id' => 'required|integer|exists:categories,id',
        'tags' => 'nullable|array',        // NEW in v2.0.2
        'sku' => 'regex:/^[A-Z0-9-]+$/'    // NEW in v2.0.2
    ]);

    // Code execution only reaches here if validation passes.
    // If it fails, a 422 JSON response is automatically sent.
}
```

---

## 🔐 Role-Based Access (RBAC)

The base controller provides several helper methods to enforce authorization rules quickly.

```php
// Boolean checks
if ($this->isAdmin()) { ... }
if ($this->isOwner($userId)) { ... }
if ($this->hasRole('teacher')) { ... }

// Enforcement (Throws 403 Exception if fails)
$this->requireRole('admin');
$this->requireAnyRole(['admin', 'teacher']);
$this->requireAdminOrOwner($resourceUserId);
```

See [RBAC Guide](RBAC.md) for more details.

---

## 🔄 Overriding Base Methods

Since your concrete controllers extend the base controllers, you can easily override any CRUD method to add custom logic.

```php
class ProductController extends Base\ProductController
{
    public function store()
    {
        // 1. Custom validation
        $data = $this->validate([
            'name' => 'required|slug_unique:products',
            'price' => 'numeric|min:0'
        ]);

        // 2. Inject current user
        $data['created_by'] = $this->request->user->user_id;

        // 3. Create using model
        $id = $this->model->create($data);

        return $this->created($this->model->find($id));
    }
}
```

---

## 💡 Best Practices

1. **Keep it Thin**: Controllers should only handle requests and responses. Business logic belongs in the Model or Service layers.
2. **Use Helpers**: Use `$this->created()` and `$this->noContent()` instead of manual status code setting for cleaner code.
3. **Return directly**: Leverage the auto-formatting system by returning arrays or objects directly from your methods.
4. **Never Edit Base**: Never modify files in `app/Controllers/Base/`. Always override them in your concrete controller.

---

**Last Updated:** 2026-02-26  
**Version:** 2.0.2
