<?php

declare(strict_types=1);

namespace App\Middleware;

use Wibiesana\Padi\Core\Request;

/**
 * Role-based Authorization Middleware
 * 
 * Usage in routes:
 * $router->get('/admin/users', [AdminController::class, 'index'])->middleware(['AuthMiddleware', 'RoleMiddleware:admin']);
 * $router->get('/users', [UserController::class, 'index'])->middleware(['AuthMiddleware', 'RoleMiddleware:admin,teacher']);
 */
class RoleMiddleware
{
    public function handle(Request $request, string $roles = ''): void
    {
        // Check if user is authenticated
        if (!$request->user) {
            throw new \Exception('Authentication required', 401);
        }

        // If no specific roles required, just check authentication
        if (empty($roles)) {
            return;
        }

        // Parse allowed roles (comma-separated)
        $allowedRoles = array_map('trim', explode(',', $roles));

        // Get user role from JWT payload
        $userRole = $request->user->role ?? null;

        // Check if user has required role
        if (!in_array($userRole, $allowedRoles, true)) {
            throw new \Exception('You do not have permission to access this resource', 403);
        }
    }
}
