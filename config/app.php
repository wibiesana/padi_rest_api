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

// Auto-detect APP_URL from request if not set, empty, or still a localhost placeholder
$appUrl      = Env::get('APP_URL', '');
$isLocalhost = $appUrl === '' || $appUrl === null
    || preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?$#i', (string)$appUrl);

if ($isLocalhost && PHP_SAPI !== 'cli' && !empty($_SERVER['HTTP_HOST'])) {
    // The configured APP_URL is empty or a localhost placeholder — build from the live request.
    // This keeps the API portable: move to any server without touching .env.

    // Detect scheme — honour common reverse-proxy / load-balancer headers
    $isHttps =
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

    $scheme = $isHttps ? 'https' : 'http';

    // HTTP_HOST already includes the non-standard port (e.g. "api.example.com:8080")
    $appUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];

    // Write back so Env::get('APP_URL') returns the detected value everywhere
    // (File::url(), Generator.php, etc.) within this request lifecycle.
    $_ENV['APP_URL'] = $appUrl;
    putenv("APP_URL={$appUrl}");
} elseif ($appUrl !== '' && $appUrl !== null) {
    // APP_URL is explicitly set to a non-localhost value — honour it as-is.
    // This covers CDN/custom-domain setups where the public URL differs from the server host.
    $appUrl = rtrim((string)$appUrl, '/');
}


return [
    'app_name' => Env::get('APP_NAME', 'Padi REST API'),
    'app_env' => $appEnv,
    'app_debug' => $appDebug,
    'app_url' => $appUrl,
    'timezone' => Env::get('TIMEZONE', 'Asia/Jakarta'),
];
