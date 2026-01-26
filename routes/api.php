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

// ============================================================================
// FLEXIBLE RESPONSE EXAMPLES (PUBLIC FOR TESTING)
// ============================================================================
// Demonstrates different response formats and patterns - no authentication required

$router->group(['prefix' => 'examples'], function ($router) {
    $router->get('/simple', 'UserController@indexSimple');    // Simple array response
    $router->get('/raw', 'UserController@rawData');           // Raw data format
    $router->get('/custom', 'UserController@customFormat');   // Custom formatted response
    $router->get('/view/{id}', 'UserController@viewSimple');  // Simple view pattern
    $router->post('/quick', 'UserController@createQuick');    // Quick create pattern
});

// ============================================================================
// RBAC (ROLE-BASED ACCESS CONTROL) EXAMPLES (PROTECTED)
// ============================================================================
// Demonstrates role-based permissions and access control patterns
//
// Available roles: admin, teacher, student
// Auth required: All endpoints require valid authentication token
//
// Endpoints:
// • GET  /rbac/stats        → Admin only - System statistics
// • GET  /rbac/users        → Admin + Teacher - User management  
// • POST /rbac/students     → Admin + Teacher - Student creation
// • GET  /rbac/my-profile   → Self-access - View own profile
// • PUT  /rbac/my-profile   → Self-access - Update own profile

$router->group(['prefix' => 'rbac', 'middleware' => ['AuthMiddleware']], function ($router) {
    // 🔴 Admin-only access
    $router->get('/stats', 'ExampleRBACController@getStats');

    // 🟡 Admin or Teacher access
    $router->get('/users', 'ExampleRBACController@listUsers');
    $router->post('/students', 'ExampleRBACController@createStudent');

    // 🟢 Self-access (any authenticated user)
    $router->get('/my-profile', 'ExampleRBACController@getMyProfile');
    $router->put('/my-profile', 'ExampleRBACController@updateMyProfile');
});



// comments routes
$router->group(['prefix' => 'comments'], function($router) {
    $router->get('/', 'CommentController@index');
    $router->get('/all', 'CommentController@all');
    $router->get('/{id}', 'CommentController@show');
    $router->post('/', 'CommentController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'CommentController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'CommentController@destroy')->middleware('AuthMiddleware');
});


// jobs routes
$router->group(['prefix' => 'jobs'], function($router) {
    $router->get('/', 'JobController@index');
    $router->get('/all', 'JobController@all');
    $router->get('/{id}', 'JobController@show');
    $router->post('/', 'JobController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'JobController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'JobController@destroy')->middleware('AuthMiddleware');
});


// post_tags routes
$router->group(['prefix' => 'post_tags'], function($router) {
    $router->get('/', 'PostTagController@index');
    $router->get('/all', 'PostTagController@all');
    $router->get('/{id}', 'PostTagController@show');
    $router->post('/', 'PostTagController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'PostTagController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'PostTagController@destroy')->middleware('AuthMiddleware');
});


// posts routes
$router->group(['prefix' => 'posts'], function($router) {
    $router->get('/', 'PostController@index');
    $router->get('/all', 'PostController@all');
    $router->get('/{id}', 'PostController@show');
    $router->post('/', 'PostController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'PostController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'PostController@destroy')->middleware('AuthMiddleware');
});


// tags routes
$router->group(['prefix' => 'tags'], function($router) {
    $router->get('/', 'TagController@index');
    $router->get('/all', 'TagController@all');
    $router->get('/{id}', 'TagController@show');
    $router->post('/', 'TagController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'TagController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'TagController@destroy')->middleware('AuthMiddleware');
});

return $router;
