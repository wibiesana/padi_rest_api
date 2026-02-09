# 🛡️ Middleware Guide

**Padi REST API Framework v1.0.0**

Middleware provides a convenient mechanism for inspecting and filtering HTTP requests entering your application.

---

## 📋 Table of Contents

- [Overview](#overview)
- [Defining Middleware](#defining-middleware)
- [Registering Middleware](#registering-middleware)
- [Middleware Parameters](#middleware-parameters)
- [Built-in Middleware](#built-in-middleware)
- [FrankenPHP Worker Mode Compatibility](#frankenphp-worker-mode-compatibility)

---

## 📖 Overview

Middleware acts as a bridge between a request and a route. It can be used for authentication, logging, rate limiting, and more. If a middleware determines that a request should not proceed, it can send a response and stop the execution flow.

---

## 🛠️ Defining Middleware

Middlewares are located in the `app/Middleware` directory. A middleware is a simple PHP class with a `handle` method.

```php
namespace App\Middleware;

use Core\Request;
use Core\Response;

class MyCustomMiddleware
{
    /**
     * Handle the incoming request.
     */
    public function handle(Request $request): void
    {
        // Perform check
        if ($request->header('X-Custom-Header') !== 'secret-value') {
            $response = new Response();
            $response->json([
                'success' => false,
                'message' => 'Forbidden - Invalid header',
                'message_code' => 'INVALID_HEADER'
            ], 403);

            // Note: In traditional mode, $response->json() calls exit.
            // In FrankenPHP worker mode, you should ensure logic doesn't continue.
        }
    }
}
```

---

## 📍 Registering Middleware

Middlewares are assigned to routes in `routes/api.php`.

### 1. Individual Routes

```php
$router->get('/profile', 'UserController@profile')
    ->middleware('AuthMiddleware');
```

### 2. Multiple Middlewares

```php
$router->get('/admin/logs', 'AdminController@logs')
    ->middleware(['AuthMiddleware', 'RoleMiddleware:admin']);
```

### 3. Route Groups

```php
$router->group(['prefix' => 'v1', 'middleware' => 'AuthMiddleware'], function($router) {
    $router->get('/user', 'UserController@show');
    $router->get('/settings', 'UserController@settings');
});
```

---

## 🔢 Middleware Parameters

You can pass parameters to middleware by separateing them with a colon `:`.

```php
// Route
$router->get('/products', 'ProductController@index')
    ->middleware('RoleMiddleware:manager');

// In RoleMiddleware.php
public function handle(Request $request, string $role = null): void
{
    if ($request->user->role !== $role) {
        // Forbidden...
    }
}
```

---

## 📦 Built-in Middleware

The framework comes with several pre-built middlewares:

1.  **AuthMiddleware**: Verifies JWT tokens and attaches the decoded user object to `$request->user`.
2.  **RoleMiddleware**: Checks if the authenticated user has a specific role (e.g., `admin`, `manager`).
3.  **RateLimitMiddleware**: Implements API rate limiting based on IP address.
4.  **CorsMiddleware**: Handles Cross-Origin Resource Sharing (though CORS is now primarily handled in `public/index.php`).

---

## 🚀 FrankenPHP Worker Mode Compatibility

The Padi REST API middleware system is designed to work seamlessly with **FrankenPHP Worker Mode**.

### ⚠️ Critical Note on Execution Flow

In traditional PHP (FPM/Apache), `Response::json()` calls `exit;`, which immediately stops the script.

In **Worker Mode**, `exit;` is avoided to keep the process alive. Therefore, when a middleware sends a response, it should be the last thing it does. The framework's `Router` is designed to detect if a response has been initiated and will skip the subsequent handler execution.

---

**Last Updated:** 2026-02-09  
**Version:** 1.0.0
