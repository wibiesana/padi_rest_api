<?php

use Core\Router;

$router = new Router();

// Health check
$router->get('/', function () {
    $response = new \Core\Response();
    $response->json([
        'success' => true,
        'message' => 'Padi REST API is running',
        'version' => '2.0.0',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
});

// Authentication routes (public)
$router->group(['prefix' => 'auth'], function ($router) {
    $router->post('/register', 'AuthController@register');
    $router->post('/login', 'AuthController@login');
    $router->post('/logout', 'AuthController@logout');
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
$router->group(['prefix' => 'comments'], function ($router) {
    $router->get('/', 'CommentController@index');
    $router->get('/all', 'CommentController@all');
    $router->get('/{id}', 'CommentController@show');
    $router->post('/', 'CommentController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'CommentController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'CommentController@destroy')->middleware('AuthMiddleware');
});


// jobs routes
$router->group(['prefix' => 'jobs'], function ($router) {
    $router->get('/', 'JobController@index');
    $router->get('/all', 'JobController@all');
    $router->get('/{id}', 'JobController@show');
    $router->post('/', 'JobController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'JobController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'JobController@destroy')->middleware('AuthMiddleware');
});


// migrations routes
$router->group(['prefix' => 'migrations'], function ($router) {
    $router->get('/', 'MigrationController@index');
    $router->get('/all', 'MigrationController@all');
    $router->get('/{id}', 'MigrationController@show');
    $router->post('/', 'MigrationController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'MigrationController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'MigrationController@destroy')->middleware('AuthMiddleware');
});


// post_tags routes
$router->group(['prefix' => 'post_tags'], function ($router) {
    $router->get('/', 'PostTagController@index');
    $router->get('/all', 'PostTagController@all');
    $router->get('/{id}', 'PostTagController@show');
    $router->post('/', 'PostTagController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'PostTagController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'PostTagController@destroy')->middleware('AuthMiddleware');
});


// posts routes
$router->group(['prefix' => 'posts'], function ($router) {
    $router->get('/', 'PostController@index');
    $router->get('/all', 'PostController@all');
    $router->get('/query-all', 'PostController@queryAll');
    $router->get('/{id}', 'PostController@show');
    $router->post('/', 'PostController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'PostController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'PostController@destroy')->middleware('AuthMiddleware');
});


// tags routes
$router->group(['prefix' => 'tags'], function ($router) {
    $router->get('/', 'TagController@index');
    $router->get('/all', 'TagController@all');
    $router->get('/{id}', 'TagController@show');
    $router->post('/', 'TagController@store')->middleware('AuthMiddleware');
    $router->put('/{id}', 'TagController@update')->middleware('AuthMiddleware');
    $router->delete('/{id}', 'TagController@destroy')->middleware('AuthMiddleware');
});

return $router;
