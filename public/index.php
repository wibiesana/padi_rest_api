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

// Load config
$config = require __DIR__ . '/../app/Config/app.php';

// Prepare router (Caching routes)
$router = require __DIR__ . '/../routes/api.php';

/**
 * Handle individual request
 */
$handler = function () use ($router, $config) {
    // Re-check origin for each request in worker mode
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $isDevelopment = Core\Env::get('APP_ENV') === 'development';
    $allowedOrigins = array_filter(explode(',', Core\Env::get('CORS_ALLOWED_ORIGINS', '')));

    if (!empty($origin)) {
        if ($isDevelopment || in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: {$origin}");
            header('Access-Control-Allow-Credentials: true');
        }
    } elseif ($isDevelopment) {
        header('Access-Control-Allow-Origin: *');
    }

    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Response-Format, Accept, Origin');

    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        return; // Don't use exit; allows worker loop to continue
    }

    // Set timezone
    date_default_timezone_set($config['timezone']);

    // Error handling configuration
    if ($config['app_debug']) {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
    } else {
        error_reporting(0);
        ini_set('display_errors', '0');
    }

    // Set error handler
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        if ($errno === E_DEPRECATED || $errno === E_USER_DEPRECATED) return true;
        if (!(error_reporting() & $errno)) return false;
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    });

    // Set exception handler for global uncaught exceptions
    set_exception_handler(function ($exception) use ($config) {
        $response = new \Core\Response();
        $error = [
            'success' => false,
            'message' => 'Internal Server Error',
            'message_code' => 'INTERNAL_SERVER_ERROR'
        ];

        if ($exception instanceof PDOException) {
            $error['message'] = 'Database error occurred';
            $error['message_code'] = 'DATABASE_ERROR';
            \Core\DatabaseManager::logError($exception);
        }

        if ($config['app_debug']) {
            $error['debug'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'type' => get_class($exception)
            ];
        }

        $response->json($error, 500);
    });

    // Create request instance & Dispatch
    $request = new \Core\Request();
    $router->dispatch($request);
};

// Execute handler (Worker mode or traditional)
if (function_exists('frankenphp_handle_request')) {
    $maxRequests = (int)Core\Env::get('MAX_REQUESTS', 500);
    for ($count = 0; frankenphp_handle_request(); ++$count) {
        $handler();

        // Restart worker after max requests to prevent memory leaks
        if ($count > $maxRequests) {
            frankenphp_finish_request();
            exit;
        }
    }
} else {
    $handler();
}
