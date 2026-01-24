<?php

use Core\Router;

$router = new Router();

// Health check routes
$router->get('/', 'HealthController@index');
$router->get('/health', 'HealthController@health');

// Authentication routes (public)
$router->group(['prefix' => 'auth'], function ($router) {
    $router->post('/register', 'AuthController@register')->middleware('RateLimitMiddleware');
    $router->post('/login', 'AuthController@login')->middleware('RateLimitMiddleware');
    $router->post('/refresh', 'AuthController@refresh');
    $router->post('/logout', 'AuthController@logout');
    $router->post('/forgot-password', 'AuthController@forgotPassword')->middleware('RateLimitMiddleware');
    $router->post('/reset-password', 'AuthController@resetPassword')->middleware('RateLimitMiddleware');
    $router->get('/me', 'AuthController@me')->middleware('AuthMiddleware');
});

// User routes (protected)
$router->group(['prefix' => 'users', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->get('/', 'UserController@index');
    $router->get('/all', 'UserController@all');
    $router->get('/{id}', 'UserController@show');
    $router->post('/', 'UserController@store');
    $router->put('/{id}', 'UserController@update');
    $router->delete('/{id}', 'UserController@destroy');
});


return $router;
