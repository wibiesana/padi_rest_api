<?php

declare(strict_types=1);

namespace Core;

/**
 * Base Controller
 */
abstract class Controller
{
    protected Request $request;
    protected Response $response;

    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? new Request();
        $this->response = new Response();
    }

    /**
     * Validate request data
     */
    protected function validate(array $rules, array $messages = []): array
    {
        // Prevent empty validation rules
        if (empty($rules)) {
            $this->response->json([
                'success' => false,
                'message' => 'Validation rules not configured. Please contact administrator.',
                'message_code' => 'VALIDATION_RULES_MISSING',
                'errors' => ['system' => ['Validation rules are not properly configured for this endpoint']]
            ], 500);
            exit; // Force exit to prevent further execution
        }

        $validator = new Validator($this->request->all(), $rules, $messages);

        if (!$validator->validate()) {
            $this->response->json([
                'success' => false,
                'message' => 'Validation failed',
                'message_code' => 'VALIDATION_FAILED',
                'errors' => $validator->errors()
            ], 422);
            exit; // Force exit to prevent further execution
        }

        return $validator->validated();
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $code = 200): void
    {
        $this->response->json($data, $code);
    }

    /**
     * Return success response
     */
    protected function success($data = null, string $message = 'Success', int $code = 200): void
    {
        $messageCode = match ($code) {
            200 => 'SUCCESS',
            201 => 'CREATED',
            204 => 'NO_CONTENT',
            default => 'SUCCESS'
        };

        $response = [
            'success' => true,
            'message' => $message,
            'message_code' => $messageCode
        ];

        // Only add item field if data is not null
        if ($data !== null) {
            $response['item'] = $data;
        }

        $this->response->json($response, $code);
    }

    /**
     * Return collection response for lists (with or without pagination)
     */
    protected function collection(array $items, array $meta = [], string $message = 'Success', int $code = 200): void
    {
        $messageCode = match ($code) {
            200 => 'SUCCESS',
            201 => 'CREATED',
            204 => 'NO_CONTENT',
            default => 'SUCCESS'
        };

        $response = [
            'success' => true,
            'message' => $message,
            'message_code' => $messageCode,
            'item' => $items
        ];

        // Add pagination meta if provided
        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        $this->response->json($response, $code);
    }

    /**
     * Return single item response
     */
    protected function single($data, string $message = 'Success', int $code = 200): void
    {
        $messageCode = match ($code) {
            200 => 'SUCCESS',
            201 => 'CREATED',
            204 => 'NO_CONTENT',
            default => 'SUCCESS'
        };

        $response = [
            'success' => true,
            'message' => $message,
            'message_code' => $messageCode
        ];

        // For single items, item should be an object, not an array
        if ($data !== null) {
            $response['item'] = $data;
        }

        $this->response->json($response, $code);
    }

    /**
     * Return error response
     */
    protected function error(string $message = 'Error', int $code = 400, mixed $errors = null, ?string $messageCode = null): void
    {
        if ($messageCode === null) {
            $messageCode = match ($code) {
                400 => 'BAD_REQUEST',
                401 => 'UNAUTHORIZED',
                403 => 'FORBIDDEN',
                404 => 'NOT_FOUND',
                422 => 'VALIDATION_FAILED',
                429 => 'RATE_LIMIT_EXCEEDED',
                500 => 'INTERNAL_SERVER_ERROR',
                default => 'ERROR'
            };
        }

        $response = [
            'success' => false,
            'message' => $message,
            'message_code' => $messageCode
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        $this->response->json($response, $code);
    }

    /**
     * Return not found response
     */
    protected function notFound(string $message = 'Resource not found'): void
    {
        $this->error($message, 404);
    }

    /**
     * Return unauthorized response
     */
    protected function unauthorized(string $message = 'Unauthorized', ?string $messageCode = null): void
    {
        $this->error($message, 401, null, $messageCode);
    }

    /**
     * Return forbidden response
     */
    protected function forbidden(string $message = 'Forbidden'): void
    {
        $this->error($message, 403);
    }

    /**
     * Check if current user has specific role
     */
    protected function hasRole(string $role): bool
    {
        return $this->request->user && $this->request->user->role === $role;
    }

    /**
     * Check if current user has any of the specified roles
     */
    protected function hasAnyRole(array $roles): bool
    {
        return $this->request->user && in_array($this->request->user->role, $roles, true);
    }

    /**
     * Require specific role or throw forbidden error
     */
    protected function requireRole(string $role, ?string $message = null): void
    {
        if (!$this->hasRole($role)) {
            $this->forbidden($message ?? "Only {$role}s can access this resource");
        }
    }

    /**
     * Require any of the specified roles or throw forbidden error
     */
    protected function requireAnyRole(array $roles, ?string $message = null): void
    {
        if (!$this->hasAnyRole($roles)) {
            $roleList = implode(', ', $roles);
            $this->forbidden($message ?? "Only {$roleList} can access this resource");
        }
    }

    /**
     * Check if current user is the owner of the resource
     */
    protected function isOwner(int $resourceUserId): bool
    {
        return $this->request->user && $this->request->user->user_id == $resourceUserId;
    }

    /**
     * Check if current user is admin
     */
    protected function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Require admin role or resource owner
     */
    protected function requireAdminOrOwner(int $resourceUserId, ?string $message = null): void
    {
        if (!$this->isAdmin() && !$this->isOwner($resourceUserId)) {
            $this->forbidden($message ?? 'You can only access your own resources');
        }
    }
}
