# 🛣️ Routing Guide

The Router is the entry point for all API requests. it maps URLs to specific Controller actions and manages Middleware execution.

---

## 📋 Table of Contents

- [Basic Routing](#basic-routing)
- [Route Parameters](#route-parameters)
- [Route Groups](#route-groups)
- [API Versioning](#api-versioning)
- [Middleware Assignment](#middleware-assignment)
- [Special Route Methods](#special-route-methods)

---

## 📍 Basic Routing

Routes are defined in `routes/api.php`. You can map URI patterns to Controller methods using standard HTTP verbs.

```php
use App\Controllers\ProductController;

// Simple route mapping: [ControllerClass, 'methodName']
$router->get('/products', [ProductController::class, 'index']);
$router->post('/products', [ProductController::class, 'store']);
$router->put('/products/{id}', [ProductController::class, 'update']);
$router->delete('/products/{id}', [ProductController::class, 'destroy']);

// String syntax is also supported: 'ControllerName@methodName'
$router->get('/health', 'SiteController@health');
```

---

## 🔢 Route Parameters

You can capture segments of the URI by wrapping them in curly braces `{}`. These are automatically passed to the `$this->request->param()` method in your controller.

```php
// Route definition
$router->get('/users/{id}', [UserController::class, 'show']);

// In UserController.php
public function show() {
    $id = $this->request->param('id');
    return $this->model->find($id);
}
```

---

## 📦 Route Groups

Groups allow you to share attributes, such as prefixes or middleware, across multiple routes.

```php
$router->group(['prefix' => 'admin', 'middleware' => 'AuthMiddleware'], function($router) {
    $router->get('/dashboard', 'AdminController@stats');
    $router->get('/settings', 'AdminController@settings');

    // Nested groups are supported
    $router->group(['prefix' => 'users'], function($router) {
        $router->get('/', 'UserController@index');
    });
});
```

---

## 🆕 API Versioning

Specifically designed for REST APIs, the `version` method provides a clean way to handle multiple API versions.

```php
// Automatically adds 'v1' prefix
$router->version('1', function($router) {
    $router->get('/status', 'InfoController@v1Status');
});

// Automatically adds 'v2' prefix
$router->version('2', function($router) {
    $router->get('/status', 'InfoController@v2Status');
});
```

---

## 🛡️ Middleware Assignment

Middleware can be assigned to individual routes or groups.

```php
// Single middleware
$router->get('/profile', 'AuthController@me')
    ->middleware('AuthMiddleware');

// Multiple middlewares
$router->post('/delete-account', 'AuthController@delete')
    ->middleware(['AuthMiddleware', 'RoleMiddleware:admin']);
```

---

## 🛠️ Special Route Methods

### 1. `any()`

Registers a route that responds to all HTTP verbs.

```php
$router->any('/webhook', 'WebhookController@handle');
```

### 2. `options()`

Useful for handling custom CORS logic if not handled globally.

```php
$router->options('/custom-endpoint', 'CorsController@handle');
```

---

## 💡 Best Practices

1. **Use Controller Classes**: Prefer `[Controller::class, 'method']` over strings for better IDE support and click-through navigation.
2. **Kebab-case Prefixes**: Follow REST standards by using kebab-case for URL prefixes (e.g., `/product-categories` instead of `/product_categories`).
3. **Group by Feature**: Keep `routes/api.php` organized by grouping related features together.
4. **Auth in Groups**: Place all protected routes inside a group with `AuthMiddleware` to avoid forgetting protection on new endpoints.

---
