# Role-Based Access Control (RBAC)

Complete guide to implementing role-based authorization in Padi REST API Framework.

## Table of Contents

- [Overview](#overview)
- [RoleMiddleware](#rolemiddleware)
- [Controller Helper Methods](#controller-helper-methods)
- [Real-World Examples](#real-world-examples)
- [Error Responses](#error-responses)
- [Best Practices](#best-practices)
- [Testing](#testing)

## Overview

Implement role-based authorization to control access to resources based on user roles.

Padi REST API provides built-in support for role-based access control with:

- ✅ **RoleMiddleware** for route-level protection
- ✅ **Controller helper methods** for granular permission checks
- ✅ **Owner-based access** for resource ownership validation
- ✅ **Exception-based error handling** with automatic response formatting
- ✅ **Flexible return values** - return data directly, throw exceptions for errors

### Common Use Cases

1. **Admin**: Full access to all resources
2. **Teacher**: Can view/manage students, cannot manage other teachers
3. **Student**: Can only view/update own data

### Example Routes

The framework includes simplified RBAC examples at `/rbac/*` endpoints:

```php
// GET /rbac/stats - Admin only
// GET /rbac/users - Admin or Teacher
// POST /rbac/students - Admin or Teacher
// GET /rbac/my-profile - Self-access (any authenticated user)
// PUT /rbac/my-profile - Self-access (any authenticated user)
```

## RoleMiddleware

Protect routes at the middleware level using `RoleMiddleware`.

### Basic Usage

```php
// routes/api.php
use Core\Router;

$router = new Router();

// Require authentication only
$router->get('/profile', [UserController::class, 'getProfile'])
    ->middleware(['AuthMiddleware']);

// Require admin role
$router->get('/admin/dashboard', [AdminController::class, 'index'])
    ->middleware(['AuthMiddleware', 'RoleMiddleware:admin']);

// Require either admin or teacher role
$router->get('/reports', [ReportController::class, 'index'])
    ->middleware(['AuthMiddleware', 'RoleMiddleware:admin,teacher']);

// Multiple roles with comma-separated list
$router->post('/students', [StudentController::class, 'create'])
    ->middleware(['AuthMiddleware', 'RoleMiddleware:admin,teacher']);
```

### Middleware Features

- ✅ Automatically checks authentication
- ✅ Supports single or multiple roles (comma-separated)
- ✅ Returns standardized error responses
- ✅ Integrates with JWT authentication

## Controller Helper Methods

Use built-in helper methods for fine-grained authorization within your controllers.

### 1. `hasRole(string $role): bool`

Check if user has specific role:

```php
public function someMethod()
{
    if ($this->hasRole('admin')) {
        // Admin-specific logic
        $data = $this->getFullData();
    }

    if ($this->hasRole('teacher')) {
        // Teacher-specific logic
        $data = $this->getTeacherData();
    }

    return $data;
}
```

### 2. `hasAnyRole(array $roles): bool`

Check if user has any of the specified roles:

```php
public function someMethod()
{
    if ($this->hasAnyRole(['admin', 'teacher'])) {
        // Logic for admin or teacher
        $data = $this->getAllStudents();
    } else {
        // Logic for other roles
        $data = $this->getOwnData();
    }

    return $data;
}
```

### 3. `requireRole(string $role, ?string $message = null): void`

Require specific role or throw 403 error:

```php
public function adminDashboard()
{
    // This will throw 403 if user is not admin
    $this->requireRole('admin');

    // Only admins reach here
    return [
        'total_users' => $this->model::findQuery()->count(),
        'active_users' => $this->model::findQuery()->where('status = :status', ['status' => 'active'])->count(),
    ];
}

// With custom message
public function deleteUser()
{
    $this->requireRole('admin', 'Only administrators can delete users');

    // Delete logic here
    $this->model->delete($userId);

    $this->setStatusCode(204); // No content
    return null;
}
```

### 4. `requireAnyRole(array $roles, ?string $message = null): void`

Require any of specified roles:

```php
public function viewReports()
{
    $this->requireAnyRole(['admin', 'teacher'], 'Only admin and teachers can view reports');

    // Admins and teachers reach here
    $reports = $this->getReports();
    return $reports;
}
```

### 5. `isOwner(int $resourceUserId): bool`

Check if current user owns the resource:

```php
public function updateProfile()
{
    $userId = (int)$this->request->param('id');
    $user = $this->model->find($userId);

    if (!$user) {
        throw new \Exception('User not found', 404);
    }

    if (!$this->isOwner($userId)) {
        throw new \Exception('You can only update your own profile', 403);
    }

    // Update logic
    $validated = $this->validate([...]);
    $this->model->update($userId, $validated);
    return $this->model->find($userId);
}
```

### 6. `isAdmin(): bool`

Quick check for admin role:

```php
public function getUserList()
{
    $users = $this->model->all();

    // Filter sensitive data based on role
    if ($this->isAdmin()) {
        // Show all data including sensitive info
        return $users;
    } else {
        // Remove sensitive data for non-admins
        $users = array_map(function ($user) {
            unset($user['password'], $user['remember_token']);
            return $user;
        }, $users);
        return $users;
    }
}
```

### 7. `requireAdminOrOwner(int $resourceUserId, ?string $message = null): void`

Require admin role or resource ownership:

```php
public function update()
{
    $userId = (int)$this->request->param('id');
    $user = $this->model->find($userId);

    if (!$user) {
        throw new \Exception('User not found', 404);
    }

    // Only admin or the user themselves can update
    $this->requireAdminOrOwner((int)$user['id'], 'You can only update your own profile');

    // Update logic
    $validated = $this->validate([...]);
    $this->model->update($userId, $validated);
    return $this->model->find($userId);
}
```

## Real-World Examples

### Example 1: Student Can Only View Own Data

```php
public function viewStudent()
{
    $studentId = (int)$this->request->param('id');
    $student = $this->model->find($studentId);

    if (!$student || $student['role'] !== 'student') {
        throw new \Exception('Student not found', 404);
    }

    // Permission check
    if ($this->isAdmin()) {
        // Admin can view any student
    } elseif ($this->hasRole('teacher')) {
        // Teacher can view their students
        // (You would check teacher_id relationship here)
    } elseif ($this->hasRole('student')) {
        // Student can only view their own data
        if (!$this->isOwner($studentId)) {
            throw new \Exception('Students can only view their own data', 403);
        }
    } else {
        throw new \Exception('You do not have permission to view student data', 403);
    }

    return $student;
}
```

### Example 2: Teacher Cannot Update Student, Only Student or Admin Can

```php
public function updateStudent(): void
{
    $studentId = (int)$this->request->param('id');
    $student = $this->model->find($studentId);

    if (!$student || $student['role'] !== 'student') {
        $this->notFound('Student not found');
    }

    // Permission check
    if ($this->hasRole('student')) {
        // Student can only update their own data
        if (!$this->isOwner($studentId)) {
            $this->forbidden('You can only update your own data');
        }
    } elseif ($this->hasRole('teacher')) {
        // Teacher CANNOT update student data
        $this->forbidden('Teachers cannot update student data');
    } elseif (!$this->isAdmin()) {
        // Must be admin or the student themselves
        $this->forbidden('You do not have permission to update student data');
    }

    // Validation and update logic
    $validated = $this->validate([
        'username' => 'max:50|unique:users,username,' . $studentId,
        'email' => 'max:255|email|unique:users,email,' . $studentId,
        'password' => 'max:255',
    ]);

    // Students cannot change their role
    if ($this->hasRole('student')) {
        unset($validated['role'], $validated['status']);
    }

    // Remove password if empty
    if (empty($validated['password'])) {
        unset($validated['password']);
    }

    $this->model->update($studentId, $validated);
    return $this->model->find($studentId);
}
```

### Example 2.1: Student Self-Update (route + controller)

```php
// Route: protect with authentication only. Controller enforces admin/owner check.
$router->put('/students/:id', [StudentController::class, 'update'])
    ->middleware(['AuthMiddleware']);
```

Short explanation:

- Protect the route with `AuthMiddleware` so only authenticated users can access it.
- Inside `StudentController::update()` call `requireAdminOrOwner((int)$id)` or check `isOwner((int)$id)` to ensure a student may only modify their own record.
- Do not rely solely on `RoleMiddleware:student` without ownership checks, as that would allow any student to update other students' records.

### Example 3: Only Admin Can Update Teacher

```php
public function updateTeacher(): void
{
    $teacherId = (int)$this->request->param('id');
    $teacher = $this->model->find($teacherId);

    if (!$teacher || $teacher['role'] !== 'teacher') {
        $this->notFound('Teacher not found');
    }

    // Permission check
    if ($this->hasRole('teacher')) {
        // Teacher can update their own profile only
        if (!$this->isOwner($teacherId)) {
            $this->forbidden('Teachers can only update their own profile');
        }
    } elseif ($this->hasRole('student')) {
        // Student cannot update any teacher
        $this->forbidden('Students cannot update teacher data');
    } elseif (!$this->isAdmin()) {
        // Only admin can update other teachers
        $this->forbidden('Only administrators can update teacher data');
    }

    // Validation and update logic
    $validated = $this->validate([
        'username' => 'max:50|unique:users,username,' . $teacherId,
        'email' => 'max:255|email|unique:users,email,' . $teacherId,
        'password' => 'max:255',
    ]);

    // Teachers cannot change their own role
    if ($this->hasRole('teacher') && !$this->isAdmin()) {
        unset($validated['role'], $validated['status']);
    }

    if (empty($validated['password'])) {
        unset($validated['password']);
    }

    $this->model->update($teacherId, $validated);
    return $this->model->find($teacherId);
}
```

### Example 4: Different Data Based on Role

```php
public function listUsers(): void
{
    $this->requireAnyRole(['admin', 'teacher']);

    $query = $this->model::findQuery();

    // Teachers can only see students
    if ($this->hasRole('teacher')) {
        $query->where('role = :role', ['role' => 'student']);
    }
    // Admin can see everyone (no filter)

    $users = $query->all();

    // Filter sensitive data for non-admins
    if (!$this->isAdmin()) {
        $users = array_map(function ($user) {
            unset($user['password'], $user['remember_token']);
            return $user;
        }, $users);
    }

    return ['data' => $users];
}
```

### Example 5: Admin and Teacher Can Create Students

```php
public function createStudent(): void
{
    // Admin and Teacher can create students
    $this->requireAnyRole(['admin', 'teacher'], 'Only admin and teachers can create students');

    $validated = $this->validate([
        'username' => 'max:50|unique:users,username',
        'email' => 'required|max:255|email|unique:users,email',
        'password' => 'required|max:255',
    ]);

    // Force role to student
    $validated['role'] = 'student';
    $validated['status'] = 'active';

    $id = $this->model->create($validated);

    return [
        'id' => $id,
        'student' => $this->model->find($id)
    ];
}
```

## Error Responses

RBAC errors return standardized message codes for easy frontend handling.

### No Authentication Token

```json
{
  "success": false,
  "message": "Authentication required",
  "message_code": "UNAUTHORIZED"
}
```

### Wrong Role

```json
{
  "success": false,
  "message": "You do not have permission to access this resource",
  "message_code": "FORBIDDEN"
}
```

### Custom Message

```json
{
  "success": false,
  "message": "Only admin and teachers can create students",
  "message_code": "FORBIDDEN"
}
```

### Owner Check Failed

```json
{
  "success": false,
  "message": "You can only update your own data",
  "message_code": "FORBIDDEN"
}
```

## Best Practices

### 1. Use Middleware for Route-Level Protection

```php
// ✅ Good - Protect entire route
$router->get('/admin/users', [AdminController::class, 'index'])
    ->middleware(['AuthMiddleware', 'RoleMiddleware:admin']);

// ❌ Avoid - Checking in every controller method
public function index(): void {
    if (!$this->hasRole('admin')) {
        $this->forbidden();
    }
    // ...
}
```

### 2. Use Helper Methods for Granular Control

```php
// ✅ Good - Clean and readable
public function update(): void
{
    $this->requireAdminOrOwner($resourceId);
    // Update logic
}

// ❌ Avoid - Verbose and hard to maintain
public function update(): void
{
    if (!$this->request->user) {
        $this->unauthorized();
    }
    $isAdmin = $this->request->user->role === 'admin';
    $isOwner = $this->request->user->user_id == $resourceId;
    if (!$isAdmin && !$isOwner) {
        $this->forbidden();
    }
    // Update logic
}
```

### 3. Fail Secure (Deny by Default)

```php
// ✅ Good - Explicit allow
if ($this->isAdmin() || $this->isOwner($resourceId)) {
    // Allow action
} else {
    $this->forbidden();
}

// ❌ Avoid - Implicit allow
if (!$this->isAdmin() && !$this->isOwner($resourceId)) {
    $this->forbidden();
}
// Implicit allow (dangerous)
```

### 4. Check Resource Existence Before Authorization

```php
// ✅ Good - Check existence first
$resource = $this->model->find($id);
if (!$resource) {
    $this->notFound();
}
$this->requireAdminOrOwner((int)$resource['user_id']);

// ❌ Avoid - Reveals if resource exists
$this->requireAdminOrOwner($id); // If fails, user knows resource exists
$resource = $this->model->find($id);
```

### 5. Use Custom Messages for Better UX

```php
// ✅ Good - Descriptive message
$this->requireRole('admin', 'Only administrators can delete users');

// ⚠️ Okay - Generic message
$this->requireRole('admin');
```

### 6. Filter Data Based on Role

```php
// ✅ Good - Different data for different roles
$user = $this->model->find($id);
unset($user['password']); // Always remove password

if (!$this->isAdmin()) {
    unset($user['remember_token']); // Remove sensitive data
    unset($user['email_verified_at']);
}

return $user;
```

### 7. Document Permissions

```php
/**
 * Update student data
 *
 * @permission admin - Can update any student
 * @permission student - Can only update own data
 * @permission teacher - Cannot update student data
 */
public function updateStudent(): void
{
    // Implementation
}
```

## Testing

### Manual Testing with cURL

```bash
# Test as student (can only access own data)
curl -H "Authorization: Bearer STUDENT_TOKEN" \
  http://localhost:8085/api/students/1

# Test as teacher (can view students, cannot update)
curl -H "Authorization: Bearer TEACHER_TOKEN" \
  http://localhost:8085/api/students

# Test as admin (full access)
curl -H "Authorization: Bearer ADMIN_TOKEN" \
  -X PUT http://localhost:8085/api/teachers/5 \
  -H "Content-Type: application/json" \
  -d '{"status":"inactive"}'

# Test unauthorized access (should return 401)
curl http://localhost:8085/api/admin/dashboard

# Test forbidden access (should return 403)
curl -H "Authorization: Bearer STUDENT_TOKEN" \
  http://localhost:8085/api/admin/dashboard
```

### Integration Tests

```php
// tests/RBACTest.php
class RBACTest extends TestCase
{
    public function testAdminCanAccessAdminRoutes()
    {
        $adminToken = $this->getAdminToken();

        $response = $this->get('/api/admin/dashboard', [
            'Authorization' => 'Bearer ' . $adminToken
        ]);

        $this->assertEquals(200, $response['status']);
    }

    public function testStudentCannotAccessAdminRoutes()
    {
        $studentToken = $this->getStudentToken();

        $response = $this->get('/api/admin/dashboard', [
            'Authorization' => 'Bearer ' . $studentToken
        ]);

        $this->assertEquals(403, $response['status']);
        $this->assertEquals('FORBIDDEN', $response['data']['message_code']);
    }

    public function testStudentCanOnlyUpdateOwnData()
    {
        $studentToken = $this->getStudentToken(); // ID: 5

        // Can update own data
        $response = $this->put('/api/students/5', [
            'name' => 'Updated Name'
        ], [
            'Authorization' => 'Bearer ' . $studentToken
        ]);
        $this->assertEquals(200, $response['status']);

        // Cannot update other student's data
        $response = $this->put('/api/students/6', [
            'name' => 'Updated Name'
        ], [
            'Authorization' => 'Bearer ' . $studentToken
        ]);
        $this->assertEquals(403, $response['status']);
    }
}
```

## Complete Example Controller

See [app/Controllers/ExampleRBACController.php](../../app/Controllers/ExampleRBACController.php) for a complete working example with all scenarios covered in this guide.

## Related Documentation

- [Authentication Guide](AUTHENTICATION.md)
- [Error Handling & Message Codes](../03-advanced/ERROR_HANDLING.md)
- [API Testing Guide](../03-advanced/API_TESTING.md)
- [Security Best Practices](../03-advanced/SECURITY.md)

---

**Last Updated:** 2026-01-24  
**Version:** 1.0.0
