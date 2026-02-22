<?php

use Wibiesana\Padi\Core\Env;

/**
 * Multi-Database Configuration
 * 
 * Supports: MySQL, MariaDB, PostgreSQL, SQLite
 * 
 * You can configure multiple database connections and switch between them
 * Uses Env::get() for consistent environment variable access with fallback values
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Database Connection
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work.
    |
    */
    'default' => Env::get('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    |
    */
    'connections' => [

        // MySQL Connection (default)
        'mysql' => [
            'driver' => 'mysql',
            'host' => Env::get('DB_HOST', 'localhost'),
            'port' => Env::get('DB_PORT', '3306'),
            'database' => Env::get('DB_DATABASE', 'rest_api_db'),
            'username' => Env::get('DB_USERNAME', 'root'),
            'password' => Env::get('DB_PASSWORD', ''),
            'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
            'collation' => Env::get('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ],
        ],

        // MariaDB Connection (sama seperti MySQL)
        'mariadb' => [
            'driver' => 'mysql', // MariaDB menggunakan driver MySQL
            'host' => Env::get('MARIADB_HOST', 'localhost'),
            'port' => Env::get('MARIADB_PORT', '3306'),
            'database' => Env::get('MARIADB_DATABASE', 'rest_api_db'),
            'username' => Env::get('MARIADB_USERNAME', 'root'),
            'password' => Env::get('MARIADB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],

        // PostgreSQL Connection
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => Env::get('PGSQL_HOST', 'localhost'),
            'port' => Env::get('PGSQL_PORT', '5432'),
            'database' => Env::get('PGSQL_DATABASE', 'rest_api_db'),
            'username' => Env::get('PGSQL_USERNAME', 'postgres'),
            'password' => Env::get('PGSQL_PASSWORD', ''),
            'charset' => Env::get('PGSQL_CHARSET', 'utf8'),
            'schema' => Env::get('PGSQL_SCHEMA', 'public'),
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],

        // SQLite Connection
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => Env::get('SQLITE_DATABASE', dirname(__DIR__, 2) . '/database/database.sqlite'),
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        ],

        // SQLite In-Memory (untuk testing)
        'sqlite_memory' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */
    'migrations' => 'migrations',

];
