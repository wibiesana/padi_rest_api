# Role-Based Access Control (RBAC)

Complete guide to implementing role-based authorization in Padi REST API Framework.

## Table of Contents

- [Overview](#overview)
- [Quick Start](#quick-start)
- [RoleMiddleware](#rolemiddleware)
- [Controller Helper Methods](#controller-helper-methods)
- [Implementation Patterns](#implementation-patterns)
- [Error Responses](#error-responses)
- [Best Practices](#best-practices)
- [Testing](#testing)

---

## Overview

Implement role-based authorization to control access to resources based on user roles. This ensures that users can only perform actions and access data that they are authorized for.

Padi REST API provides built-in support for RBAC with:

- ✅ **RoleMiddleware** for route-level protection.
- ✅ **Controller helper methods** for granular permission checks.
- ✅ **Owner-based access** for resource ownership validation.
- ✅ **Simplified Patterns** for Admin-only, Multi-role, and Self-access.

---

## Quick Start

The framework includes ready-to-use RBAC examples at `/rbac/*` endpoints. These demonstrate common authorization patterns and serve as templates for your own implementations.

| Endpoint           | Method  | Required Role      | Purpose                                    |
| :----------------- | :------ | :----------------- | :----------------------------------------- |
| `/rbac/stats`      | GET     | `admin`            | System statistics and administrative data. |
| `/rbac/users`      | GET     | `admin`, `teacher` | List users (Teacher sees only students).   |
| `/rbac/students`   | POST    | `admin`, `teacher` | Create new student accounts.               |
| `/rbac/my-profile` | GET/PUT | Authenticated      | View or update own profile.                |

---

## RoleMiddleware

Protect routes at the middleware level using `RoleMiddleware`. This is the first line of defense for your API endpoints.

### Basic Usage

```php
// routes/api.php
use Core\Router;

$router = new Router();

// 1. Require authentication only
$router->get('/profile', [UserController::class, 'getProfile'])
    ->middleware(['AuthMiddleware']);

// 2. Require admin role
$router->get('/admin/dashboard', [AdminController::class, 'index'])
    ->middleware(['AuthMiddleware', 'RoleMiddleware:admin']);

// 3. Require either admin or teacher role (comma-separated list)
$router->get('/reports', [ReportController::class, 'index'])
    ->middleware(['AuthMiddleware', 'RoleMiddleware:admin,teacher']);
```

---

## Controller Helper Methods

Use built-in helper methods for fine-grained authorization within your controllers.

### 1. Verification Methods (Return Boolean)

- `$this->hasRole(string $role)`: Check if user has a specific role.
- `$this->hasAnyRole(array $roles)`: Check if user has any of the specified roles.
- `$this->isOwner(int $resourceUserId)`: Check if current user owns the resource.
- `$this->isAdmin()`: Quick check for admin role.

### 2. Enforcement Methods (Throw Exception if Failed)

- `$this->requireRole(string $role, ?string $message = null)`: Throws 403 if user doesn't have the role.
- `$this->requireAnyRole(array $roles, ?string $message = null)`: Throws 403 if user doesn't have any of the roles.
- `$this->requireAdminOrOwner(int $resourceUserId, ?string $message = null)`: Throws 403 if user is not an admin and not the owner.

---

## Implementation Patterns

### 1. Admin-Only Pattern

Used for sensitive operations that only system administrators should perform.

```php
public function getStats()
{
    $this->requireRole('admin');

    return [
        'total_users' => $this->model::findQuery()->count(),
        'active_sessions' => $this->activeSessions(),
    ];
}
```

### 2. Multi-Role & Filtered Data Pattern

Used when different roles can access the same endpoint but see different data.

```php
public function listUsers()
{
    $this->requireAnyRole(['admin', 'teacher']);

    $query = $this->model::findQuery();

    // 1. Filter query based on role
    if ($this->hasRole('teacher')) {
        $query->where('role = :role', ['role' => 'student']);
    }

    $users = $query->all();

    // 2. Remove sensitive records for non-admins
    if (!$this->isAdmin()) {
       $users = $this->resource::collection($users);
    }

    return $users;
}
```

### 3. Self-Access (Owner) Pattern

Ensures users can only access or modify their own data.

```php
public function getMyProfile()
{
    $userId = $this->request->user->user_id ?? null;

    // AuthMiddleware usually handles this, but we can be explicit
    if (!$userId) throw new \Exception('Unauthorized', 401);

    return $this->model->find($userId);
}

public function updateProfile()
{
    $id = (int)$this->request->param('id');

    // Enforce that only the owner (or admin) can update
    $this->requireAdminOrOwner($id, 'You can only update your own profile');

    $validated = $this->validate(['email' => 'required|email']);
    $this->model->update($id, $validated);

    return $this->model->find($id);
}
```

---

## Error Responses

RBAC errors return standardized message codes for easy frontend handling.

| Status  | Message                             | Message Code   | Scenario                               |
| :------ | :---------------------------------- | :------------- | :------------------------------------- |
| **401** | `Authentication required`           | `UNAUTHORIZED` | Missing or invalid JWT token.          |
| **403** | `Permission denied`                 | `FORBIDDEN`    | User role does not match requirements. |
| **403** | `You can only update your own data` | `FORBIDDEN`    | Owner check failed.                    |
| **404** | `Resource not found`                | `NOT_FOUND`    | Record does not exist.                 |

---

## Best Practices

1. **Fail Secure**: Always default to denying access. Use `$this->requireRole()` early in your methods.
2. **Middleware First**: Use `RoleMiddleware` for coarse-grained access control to save processing time.
3. **Existence Check Before Auth**: Check if a resource exists (`404`) before checking if the user is authorized to see it (`403`). This prevents leaking information about resource IDs.
4. **Different Data per Role**: Use API Resources to hide sensitive fields from non-admin users.
5. **Ownership Check**: Never trust the ID passed in the URL for sensitive actions. Always verify `$this->isOwner($id)` or `$this->requireAdminOrOwner($id)`.

---

## Testing

### Manual Testing with cURL

```bash
# Test as Student (Unauthorized for admin routes)
curl -H "Authorization: Bearer STUDENT_TOKEN" http://localhost:8085/rbac/stats
# Response: 403 Forbidden

# Test as Admin
curl -H "Authorization: Bearer ADMIN_TOKEN" http://localhost:8085/rbac/stats
# Response: 200 OK
```

### Integration Testing

Check the `tests/RBACTest.php` for examples of automated authorization testing.

---

## Related Documentation

- [Authentication Guide](AUTHENTICATION.md)
- [Controllers Guide](CONTROLLERS.md)
- [API Resources](RESOURCES.md)
- [Error Handling](../03-advanced/ERROR_HANDLING.md)

---

**Last Updated:** 2026-02-09  
**Version:** 1.0.0
