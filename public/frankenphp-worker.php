<?php

use Core\Env;
use Core\Database;
use Core\Request;
use Core\Response;

$logFile = '/app/worker_init.log';
// Ensure log file is writable or try logging to stderr if fails, 
// but for now assume /app is writable as it's mounted.
// We'll wrap file ops in try/catch to be safe.

function log_message($msg)
{
    global $logFile;
    try {
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
    } catch (Throwable $e) {
        fwrite(STDERR, "[LOG FAIL] $msg\n");
    }
}

log_message("START: Worker starting.");

/**
 * FrankenPHP Worker Mode Script
 */

// Check if FrankenPHP worker mode is available
if (!function_exists('frankenphp_handle_request')) {
    log_message("ERROR: FrankenPHP not detected");
    $error = [
        'success' => false,
        'message' => 'FrankenPHP worker mode is not available',
        'error' => 'This script requires FrankenPHP with worker mode enabled',
        'solution' => 'Run with: frankenphp php-server --worker public/frankenphp-worker.php'
    ];
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode($error, JSON_PRETTY_PRINT);
    exit(1);
}

// Undefine frankenphp_handle_request to disable worker mode
if (function_exists('frankenphp_handle_request')) {
    // Create a dummy function to replace frankenphp_handle_request
    function frankenphp_handle_request_disabled($callback)
    {
        log_message("WARNING: FrankenPHP worker mode disabled");
        // Execute callback once and return false to stop loop
        $callback();
        return false;
    }
}

// Global exception handler for initialization phase
set_exception_handler(function ($e) {
    log_message("FATAL EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    exit(1);
});

try {
    // Load composer autoloader once
    log_message("STEP: Loading autoloader");
    require_once __DIR__ . '/../vendor/autoload.php';

    // Load environment variables once
    log_message("STEP: Loading Env");
    Env::load(__DIR__ . '/../.env');

    // Load configuration once
    log_message("STEP: Loading Config");
    $config = require __DIR__ . '/../Config/app.php';

    log_message("DEBUG: APP_DEBUG is " . ($config['app_debug'] ? 'TRUE' : 'FALSE'));

    // Set timezone once
    date_default_timezone_set($config['timezone'] ?? 'UTC');

    // Configure error handling once
    if ($config['app_debug']) {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
    } else {
        error_reporting(0);
        ini_set('display_errors', '0');
    }

    // Set error handler once
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        if ($errno === E_DEPRECATED || $errno === E_USER_DEPRECATED) {
            return true;
        }

        log_message("ERROR HANDLER: $errstr in $errfile:$errline");

        if (!(error_reporting() & $errno)) {
            return false;
        }

        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    });

    // Load routes once - this stays in memory!
    log_message("STEP: Loading Routes");
    $router = require __DIR__ . '/../routes/api.php';

    log_message("SUCCESS: Initialization complete. Entering loop.");
} catch (Throwable $e) {
    log_message("INIT FAILED: " . $e->getMessage());
    exit(1);
}

// FrankenPHP Worker Loop (disabled mode)
for ($nbRequests = 0, $running = true; $running; ++$nbRequests) {
    // Wait for next request and execute handler
    try {
        $running = frankenphp_handle_request_disabled(function () use ($router, $config, $nbRequests) {

            // Debug request
            // if ($config['app_debug']) {
            log_message("[REQUEST] " . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN') . " " . ($_SERVER['REQUEST_URI'] ?? 'UNKNOWN'));
            // }

            // ===== RESET STATE FOR EACH REQUEST =====

            // Reset database query log
            if (class_exists(Database::class)) {
                Database::resetQueryLog();
            }

            // Clear output buffers
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            // Reset headers (if not already sent)
            if (!headers_sent()) {
                header_remove();
            }

            // Create fresh request instance for this request
            $request = new Request();

            // Set exception handler for this specific request
            set_exception_handler(function ($exception) use ($config) {
                log_message("[REQ ERROR] " . $exception->getMessage());

                $error = [
                    'success' => false,
                    'message' => 'Internal Server Error'
                ];

                if (!headers_sent()) {
                    http_response_code(500);
                    header('Content-Type: application/json');
                }
                echo json_encode($error);
            });

            try {
                // Dispatch the request through router
                $router->dispatch($request);
            } catch (\Throwable $e) {
                log_message("[DISPATCH ERROR] " . $e->getMessage());
                // ... error handling
                if (!headers_sent()) {
                    http_response_code(500);
                    header('Content-Type: application/json');
                }
                echo json_encode(['success' => false, 'message' => 'Server Error']);
            }

            // Cleanup after request if needed (e.g. garbage collection)
            if ($nbRequests > 0 && $nbRequests % 100 === 0) {
                gc_collect_cycles();
            }
        });
    } catch (Throwable $e) {
        log_message("[LOOP ERROR] " . $e->getMessage());
        $running = false; // Stop loop on fatal error
    }
}
