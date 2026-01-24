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



// comments routes
$router->group(['prefix' => 'comments'], function($router) {
    $router->get('/', 'CommentController@index');
    $router->get('/all', 'CommentController@all');
    $router->get('/{id}', 'CommentController@show');
    $router->post('/', 'CommentController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'CommentController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'CommentController@destroy')->middleware('AuthMiddleware');
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
