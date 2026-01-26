<?php

use Core\Router;

$router = new Router();

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here you can register API routes for your application. These routes are
| loaded by the Router class and automatically support middleware,
| automatic response formatting, and exception handling.
|
*/

// ============================================================================
// SITE & HEALTH CHECK ROUTES
// ============================================================================
// Public routes for site information and health monitoring

$router->get('/', 'SiteController@index');
$router->get('/health', 'SiteController@health');

// Site information routes
$router->group(['prefix' => 'site'], function ($router) {
    $router->get('/info', 'SiteController@info');
    $router->get('/endpoints', 'SiteController@endpoints');
});

// ============================================================================
// AUTHENTICATION ROUTES (PUBLIC)
// ============================================================================
// User registration, login, password reset, and token management

$router->group(['prefix' => 'auth'], function ($router) {
    // User registration & login (rate limited)
    $router->post('/register', 'AuthController@register')->middleware('RateLimitMiddleware');
    $router->post('/login', 'AuthController@login')->middleware('RateLimitMiddleware');

    // Token management
    $router->post('/refresh', 'AuthController@refresh');
    $router->post('/logout', 'AuthController@logout');

    // Password recovery (rate limited)
    $router->post('/forgot-password', 'AuthController@forgotPassword')->middleware('RateLimitMiddleware');
    $router->post('/reset-password', 'AuthController@resetPassword')->middleware('RateLimitMiddleware');

    // Get current user info (protected)
    $router->get('/me', 'AuthController@me')->middleware('AuthMiddleware');
});

// ============================================================================
// USER MANAGEMENT ROUTES (PROTECTED)
// ============================================================================
// Standard CRUD operations for user management - requires authentication
$router->group(['prefix' => 'users', 'middleware' => ['AuthMiddleware']], function ($router) {
    // List & view operations
    $router->get('/', 'UserController@index');           // List users with pagination
    $router->get('/all', 'UserController@all');         // Get all users (admin only)
    $router->get('/{id}', 'UserController@show');       // Get specific user

    // Modification operations
    $router->post('/', 'UserController@store');         // Create new user
    $router->put('/{id}', 'UserController@update');     // Update user
    $router->delete('/{id}', 'UserController@destroy'); // Delete user
});


return $router;
