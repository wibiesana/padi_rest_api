<?php

// Load composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Define project root path
define('PADI_ROOT', dirname(__DIR__));

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
    $allowedOrigins = array_map(function ($url) {
        return rtrim(trim($url), '/');
    }, explode(',', Core\Env::get('CORS_ALLOWED_ORIGINS', '')));

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

        // Use exception code if it's a valid HTTP status code, otherwise default to 500
        $code = $exception->getCode();
        $statusCode = ($code >= 400 && $code < 600) ? (int)$code : 500;

        $error = [
            'success' => false,
            'message' => 'Internal Server Error',
            'message_code' => 'INTERNAL_SERVER_ERROR'
        ];

        if ($exception instanceof PDOException) {
            $error['message'] = 'Database error occurred';
            $error['message_code'] = 'DATABASE_ERROR';
            \Core\DatabaseManager::logError($exception);
        } else {
            // For general exceptions, use the exception message
            $error['message'] = $exception->getMessage();
            $error['message_code'] = 'EXCEPTION';
        }

        if ($config['app_debug']) {
            $error['debug'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'type' => get_class($exception)
            ];
        }

        $response->json($error, $statusCode);
    });

    // Reset state between requests (Essential for FrankenPHP worker mode)
    \Core\Database::resetQueryLog();
    \Core\DatabaseManager::clearErrors();

    // Ensure database connections are alive (Fix: MySQL server has gone away)
    foreach (\Core\DatabaseManager::getConnections() as $connName) {
        try {
            \Core\DatabaseManager::connection($connName)->query('SELECT 1');
        } catch (\PDOException $e) {
            // Connection is dead, remove it so it's recreated on next use
            \Core\DatabaseManager::disconnect($connName);
        }
    }

    /**
     * Fix for shared hosting where REQUEST_URI might contain script path
     */
    if (isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SCRIPT_NAME'])) {
        $uri = $_SERVER['REQUEST_URI'];
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $scriptDir = dirname($scriptName);

        // Remove script name from URI if present (e.g. /index.php/foo/bar -> /foo/bar)
        if (strpos($uri, $scriptName) === 0) {
            $uri = substr($uri, strlen($scriptName));
        }
        // Remove script dir from URI if present (e.g. /public/foo/bar -> /foo/bar)
        elseif ($scriptDir !== '/' && strpos($uri, $scriptDir) === 0) {
            $uri = substr($uri, strlen($scriptDir));
        }

        // Ensure URI starts with /
        if ($uri === '' || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }

        $_SERVER['REQUEST_URI'] = $uri;
    }

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
