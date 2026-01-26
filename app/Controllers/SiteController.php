<?php

namespace App\Controllers;

use Core\Controller;
use Core\Response;
use Core\Env;

class SiteController extends Controller
{
    /**
     * Display API information
     */
    public function index(): void
    {
        $response = new Response();
        $response->json([
            'success' => true,
            'aplikasi' => Env::get('APP_NAME', 'Padi REST API'),
            'status' => 'Up and running',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Health check endpoint
     */
    public function health(): void
    {
        $response = new Response();
        $response->json([
            'success' => true,
            'environment' => Env::get('APP_ENV', 'production'),
            'debug' => Env::get('APP_DEBUG', 'false') === 'true',
            'message' => Env::get('APP_NAME', 'Padi REST API') . ' is running',
            'version' => Env::get('APP_VERSION', '2.0.0'),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Site information endpoint
     */
    public function info()
    {
        return [
            'site_name' => Env::get('APP_NAME', 'Padi REST API'),
            'description' => 'A RESTful API built with PHP',
            'version' => Env::get('APP_VERSION', '2.0.0'),
            'author' => 'Padi Team',
            'environment' => Env::get('APP_ENV', 'production'),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * API endpoints documentation
     */
    public function endpoints()
    {
        return [
            'endpoints' => [
                'authentication' => [
                    'POST /api/auth/register' => 'Register new user',
                    'POST /api/auth/login' => 'User login',
                    'POST /api/auth/logout' => 'User logout',
                    'POST /api/auth/refresh' => 'Refresh token',
                    'POST /api/auth/forgot-password' => 'Request password reset',
                    'POST /api/auth/reset-password' => 'Reset password',
                    'GET /api/auth/me' => 'Get current user info'
                ],
                'users' => [
                    'GET /api/users' => 'Get users (paginated)',
                    'GET /api/users/all' => 'Get all users',
                    'GET /api/users/{id}' => 'Get user by ID',
                    'POST /api/users' => 'Create user',
                    'PUT /api/users/{id}' => 'Update user',
                    'DELETE /api/users/{id}' => 'Delete user'
                ],
                'site' => [
                    'GET /api/' => 'API information',
                    'GET /api/health' => 'Health check',
                    'GET /api/site/info' => 'Site information',
                    'GET /api/site/endpoints' => 'Available endpoints'
                ],
                'rbac' => [
                    'GET /rbac/stats' => 'Admin statistics (admin only)',
                    'GET /rbac/users' => 'List users (admin/teacher)',
                    'POST /rbac/students' => 'Create student (admin/teacher)',
                    'GET /rbac/my-profile' => 'View own profile (self-access)',
                    'PUT /rbac/my-profile' => 'Update own profile (self-access)'
                ]
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
