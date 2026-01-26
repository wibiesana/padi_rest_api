# Flexible Response Format

Fitur ini memberikan kebebasan kepada developer untuk mengembalikan data dari controller dengan format yang fleksibel, tanpa harus menggunakan method `success()`, `collection()`, dll.

## Format Response yang Didukung

### 1. **FULL Format** (Default Framework)

```php
// Response:
{
  "success": true,
  "message": "Success",
  "message_code": "SUCCESS",
  "item": {...}
}
```

### 2. **SIMPLE Format** (Sederhana)

```php
// Response:
{
  "status": "success",
  "code": "SUCCESS",
  "item": {...}
}
```

### 3. **RAW Format** (Data Mentah)

```php
// Response: langsung data tanpa wrapper
{...} atau [...]
```

## Cara Penggunaan

### 1. **Direct Return dari Controller**

```php
public function actionIndex()
{
    // Langsung return data
    return $this->model->all();
}

public function actionView($id)
{
    $user = $this->model->find($id);
    if (!$user) {
        // Auto-handled as 404
        return null;
    }
    return $user;
}

public function actionCreate()
{
    $data = $this->request->all();
    $id = $this->model->create($data);

    // Auto status 201
    return $this->created($this->model->find($id));
}
```

### 2. **Helper Methods**

```php
// Raw data return
return $this->raw($data);

// Simple format
return $this->simple($data, 'success', 'USER_CREATED', 201);

// Set status code for auto-formatting
$this->setStatusCode(201);
return $userData;

// Created response (auto 201)
return $this->created($newUser);

// No content response (auto 204)
return $this->noContent();
```

### 3. **Custom Response Structure**

```php
public function customResponse()
{
    return [
        'status' => 'success',
        'code' => 'SUCCESS',
        'data' => $this->model->all(),
        'timestamp' => date('Y-m-d H:i:s'),
        'custom_field' => 'custom_value'
    ];
}
```

## Konfigurasi Format Response

### 1. **Via Environment Variable**

```env
# .env
RESPONSE_FORMAT=full    # full, simple, raw
```

### 2. **Via Request Header**

```http
X-Response-Format: simple
```

Priority: Header > Environment Variable > Default (full)

### 3. **Per Request**

```php
// Raw format untuk endpoint ini
public function downloadData()
{
    return $this->raw($this->model->exportData());
}
```

## Auto-Detection Features

### 1. **Status Code Detection**

- Return data dengan `created()` → Auto 201
- Return `null` atau tidak ada data → Auto 404
- Return dengan `setStatusCode()` → Custom status

### 2. **Collection vs Single Item**

- Array dengan index numerik → Collection format
- Object atau associative array → Single item format
- Empty array → Collection format

### 3. **Error Handling**

```php
public function actionView($id)
{
    $user = $this->model->find($id);

    // Auto 404 jika null
    if (!$user) {
        return null; // atau throw new NotFoundException()
    }

    return $user;
}
```

## Contoh Implementasi Complete CRUD

```php
class ProductController extends Controller
{
    protected $model;

    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->model = new Product();
    }

    // GET /products - List all
    public function index()
    {
        return $this->model->paginate(
            $this->request->query('page', 1),
            $this->request->query('per_page', 10)
        );
    }

    // GET /products/all - All without pagination
    public function all()
    {
        return $this->model->all();
    }

    // GET /products/{id} - Single item
    public function show()
    {
        $id = $this->request->param('id');
        return $this->findModel($id);
    }

    // POST /products - Create
    public function store()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric'
        ]);

        $id = $this->model->create($validated);
        return $this->created($this->findModel($id));
    }

    // PUT /products/{id} - Update
    public function update()
    {
        $id = $this->request->param('id');
        $product = $this->findModel($id);

        $validated = $this->validate([
            'name' => 'string|max:100',
            'price' => 'numeric'
        ]);

        $this->model->update($id, $validated);
        return $this->findModel($id);
    }

    // DELETE /products/{id} - Delete
    public function destroy()
    {
        $id = $this->request->param('id');
        $product = $this->findModel($id);

        $this->model->delete($id);
        return $this->noContent();
    }

    protected function findModel($id)
    {
        $model = $this->model->find($id);
        if (!$model) {
            throw new \Exception('Product not found', 404);
        }
        return $model;
    }
}
```

## Error Handling

### 1. **Automatic Null Handling**

```php
public function show()
{
    $user = $this->model->find($this->request->param('id'));
    // Return null otomatis jadi 404 response
    return $user;
}
```

### 2. **Exception Handling**

```php
public function create()
{
    try {
        $data = $this->validate([...]);
        $id = $this->model->create($data);
        return $this->created($this->model->find($id));
    } catch (\Exception $e) {
        // Auto-handled by framework
        throw $e;
    }
}
```

### 3. **Manual Error Response**

```php
public function customError()
{
    return $this->simple(
        ['errors' => ['Field is required']],
        'error',
        'VALIDATION_ERROR',
        422
    );
}
```

## Migration from Old Methods

Jika masih ada method lama yang digunakan, berikut cara migrasinya:

```php
// OLD WAY ❌
return $data;  // 'Success message' will be auto-generated by router
$this->collection($items, $meta, 'Success message');
throw new \Exception('Error message', 400);
$this->notFound('Resource not found');

// NEW WAY ✅
return $data; // Auto-format
return $this->simple($data, 'success', 'SUCCESS');
throw new \Exception('Error message', 400); // Auto-handled
throw new \Exception('Resource not found', 404);
```

## Response Examples

### Full Format

```json
{
  "success": true,
  "message": "Success",
  "message_code": "SUCCESS",
  "item": {
    "id": 1,
    "name": "John Doe"
  }
}
```

### Simple Format

```json
{
  "status": "success",
  "code": "SUCCESS",
  "item": {
    "id": 1,
    "name": "John Doe"
  }
}
```

### Raw Format

```json
{
  "id": 1,
  "name": "John Doe"
}
```

### Custom Format

```json
{
  "status": "success",
  "code": "SUCCESS",
  "data": {...},
  "timestamp": "2026-01-26 10:30:45",
  "version": "v1"
}
```

Fitur ini memberikan fleksibilitas penuh kepada developer untuk memilih format response yang sesuai dengan kebutuhan aplikasi mereka!

## Generator Updates

🎉 **Code Generator telah diperbarui** untuk menggunakan format response baru!

### Perubahan pada Generator:

- ✅ **Generated controllers** sekarang return data langsung
- ✅ **Exception handling** menggunakan `throw new \Exception()`
- ✅ **Database error handling** dengan try-catch blocks
- ✅ **New helper methods** seperti `created()`, `noContent()`
- ✅ **No more void return types** pada controller methods

### Testing Generator:

```bash
# Test generator dengan format baru
php scripts/test_generator.php

# Generate sample controller
php scripts/test_generator.php --save
```

### Generator Commands:

```bash
# Generate CRUD dengan format baru
php scripts/generate.php crud products --write

# Generate hanya controller
php scripts/generate.php controller Product

# Generate semua table
php scripts/generate.php crud-all --write
```

Semua generated code akan otomatis menggunakan flexible response format!
