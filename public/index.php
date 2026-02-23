<?php

declare(strict_types=1);

// Load composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use Wibiesana\Padi\Core\Application;

// Define project root path
define('PADI_ROOT', dirname(__DIR__));

/**
 * Global helper for debugging (Internal/Server-side)
 * Backward compatibility
 */
if (!function_exists('debug_log')) {
    function debug_log(string $message, string $level = 'info'): void
    {
        if (\Wibiesana\Padi\Core\Env::get('APP_ENV') === 'development' || $level === 'error') {
            error_log("[$level] $message");
        }
    }
}

// ---------------------------------------------------------
// Launch Application
// ---------------------------------------------------------

$app = new Application(PADI_ROOT);
$app->run();
