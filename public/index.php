<?php

// Load composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Debug REQUEST
file_put_contents(__DIR__ . '/../server_dump.txt', print_r($_SERVER, true));

/**
 * Global helper for debugging
 */
function debug_log(string $message, string $level = 'info'): void
{
    // Debug class not implemented yet
    // Log to PHP error log
    error_log("[$level] $message");
}

// Load environment variables from .env file
Core\Env::load(__DIR__ . '/../.env');

// Initialize Debug (if enabled)
// Debug class not implemented yet
// if (class_exists('Core\\Debug') && Core\\Debug::isEnabled()) {
//     Core\\Debug::init();
// }

// Load configuration
$config = require __DIR__ . '/../app/Config/app.php';

// Set timezone
date_default_timezone_set($config['timezone']);

// Error handling
if ($config['app_debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set error handler - only throw exceptions for actual errors, not deprecations
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // Ignore deprecation warnings in production
    if ($errno === E_DEPRECATED || $errno === E_USER_DEPRECATED) {
        return true;
    }

    // Throw exception for actual errors
    if (!(error_reporting() & $errno)) {
        return false;
    }

    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// Set exception handler
set_exception_handler(function ($exception) use ($config) {
    $response = new \Core\Response();

    $error = [
        'success' => false,
        'message' => 'Internal Server Error',
        'message_code' => 'INTERNAL_SERVER_ERROR'
    ];

    // Handle PDOException specifically
    if ($exception instanceof PDOException) {
        $error['message'] = 'Database error occurred';
        $error['message_code'] = 'DATABASE_ERROR';

        // Log the database error
        \Core\DatabaseManager::logError($exception);
    }

    if ($config['app_debug']) {
        $error['debug'] = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'type' => get_class($exception)
        ];

        // Add database-specific debug info for PDOException
        if ($exception instanceof PDOException) {
            $lastDbError = \Core\DatabaseManager::getLastError();
            if ($lastDbError) {
                $error['debug']['database_error'] = $lastDbError;
            }
        }
    }

    $response->json($error, 500);
});

// Create request instance
$request = new \Core\Request();

// Load routes
$router = require __DIR__ . '/../routes/api.php';

// Dispatch request
$router->dispatch($request);
