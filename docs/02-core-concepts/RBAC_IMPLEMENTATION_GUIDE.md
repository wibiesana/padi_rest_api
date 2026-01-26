# RBAC Implementation Guide

## Quick Start

The framework includes ready-to-use RBAC examples at `/rbac/*` endpoints. These demonstrate common authorization patterns and serve as templates for your own implementations.

## Available Endpoints

### 🔴 Admin-only Access

```
GET /rbac/stats
```

- **Purpose**: System statistics and administrative data
- **Required Role**: `admin`
- **Returns**: User counts, system metrics

**Example Response:**

```json
{
  "total_users": 150,
  "active_users": 120,
  "total_teachers": 25,
  "total_students": 100
}
```

### 🟡 Admin or Teacher Access

```
GET /rbac/users
POST /rbac/students
```

**GET /rbac/users**

- **Purpose**: List users (filtered by role)
- **Required Roles**: `admin` or `teacher`
- **Behavior**:
  - Admin sees all users
  - Teacher sees only students
- **Returns**: Array of user objects (sensitive data removed for non-admins)

**POST /rbac/students**

- **Purpose**: Create new student accounts
- **Required Roles**: `admin` or `teacher`
- **Body**: `{ "username", "email", "password" }`
- **Returns**: Created student object

### 🟢 Self-access (Any Authenticated User)

```
GET /rbac/my-profile
PUT /rbac/my-profile
```

**GET /rbac/my-profile**

- **Purpose**: View own profile
- **Required**: Valid authentication token
- **Returns**: Current user's profile (without sensitive data)

**PUT /rbac/my-profile**

- **Purpose**: Update own profile
- **Required**: Valid authentication token
- **Body**: `{ "username", "email" }` (role/status changes restricted)
- **Returns**: Updated profile

## Implementation Patterns

### 1. Admin-only Pattern

```php
public function getStats()
{
    $this->requireRole('admin');

    return [
        'total_users' => $this->model::findQuery()->count(),
        // ... other stats
    ];
}
```

### 2. Multi-role Pattern

```php
public function listUsers()
{
    $this->requireAnyRole(['admin', 'teacher']);

    $query = $this->model::findQuery();

    // Filter data based on role
    if ($this->hasRole('teacher')) {
        $query->where('role = :role', ['role' => 'student']);
    }

    return $query->all();
}
```

### 3. Self-access Pattern

```php
public function getMyProfile()
{
    $userId = $this->request->user->user_id ?? null;

    if (!$userId) {
        throw new \Exception('Authentication required', 401);
    }

    $user = $this->model->find($userId);

    if (!$user) {
        throw new \Exception('User not found', 404);
    }

    return $user;
}
```

## Testing with Postman

1. **Authenticate first**: Use `/auth/login` to get a token
2. **Add Authorization header**: `Bearer {your-token}`
3. **Test different roles**: Login with admin/teacher/student accounts
4. **Verify permissions**: Try accessing endpoints with different roles

## Error Responses

All RBAC endpoints return standardized error responses:

**401 Unauthorized** (Missing/invalid token):

```json
{
  "success": false,
  "message": "Authentication required",
  "data": null,
  "status_code": 401
}
```

**403 Forbidden** (Insufficient permissions):

```json
{
  "success": false,
  "message": "You must have admin role to access this resource",
  "data": null,
  "status_code": 403
}
```

**404 Not Found** (Resource not found):

```json
{
  "success": false,
  "message": "User not found",
  "data": null,
  "status_code": 404
}
```

## Best Practices

1. **Use helper methods**: `$this->requireRole()`, `$this->requireAnyRole()`
2. **Filter data by role**: Remove sensitive information for non-admins
3. **Validate ownership**: Use `$this->isOwner()` for resource ownership checks
4. **Throw exceptions**: Use `throw new \Exception('message', code)` for errors
5. **Return data directly**: Let the framework handle response formatting

## Related Documentation

- [Authentication Guide](AUTHENTICATION.md)
- [Controller Helpers](CONTROLLERS.md)
- [Response Structure](RESPONSE_STRUCTURE.md)
