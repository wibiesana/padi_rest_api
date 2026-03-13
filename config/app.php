<?php

use Wibiesana\Padi\Core\Env;

$appEnv = Env::get('APP_ENV', 'production');

// Strictly enforce: Debug is ON in development (unless explicitly disabled) 
// and ALWAYS OFF in production for security.
$appDebug = false;
if ($appEnv === 'development') {
    $debugEnv = Env::get('APP_DEBUG', 'true');
    $appDebug = filter_var($debugEnv, FILTER_VALIDATE_BOOLEAN);
}

// Auto-detect APP_URL from request if not set or empty in .env
$appUrl = Env::get('APP_URL', '');
if ($appUrl === '' || $appUrl === null) {
    if (PHP_SAPI !== 'cli' && !empty($_SERVER['HTTP_HOST'])) {
        // Detect scheme (http/https)
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
            ? 'https' : 'http';

        $appUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
    } else {
        $appUrl = 'http://localhost';
    }

    // Write back to $_ENV so Env::get('APP_URL') returns the
    // auto-detected value everywhere (including core File.php, Generator.php)
    $_ENV['APP_URL'] = $appUrl;
    putenv("APP_URL={$appUrl}");
}

return [
    'app_name' => Env::get('APP_NAME', 'Padi REST API'),
    'app_env' => $appEnv,
    'app_debug' => $appDebug,
    'app_url' => $appUrl,
    'timezone' => Env::get('TIMEZONE', 'Asia/Jakarta'),
];
