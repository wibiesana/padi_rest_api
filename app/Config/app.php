<?php

use Core\Env;

$appEnv = Env::get('APP_ENV', 'production');

// Strictly enforce: Debug is ON in development (unless explicitly disabled) 
// and ALWAYS OFF in production for security.
$appDebug = false;
if ($appEnv === 'development') {
    $debugEnv = Env::get('APP_DEBUG', 'true');
    $appDebug = filter_var($debugEnv, FILTER_VALIDATE_BOOLEAN);
}

return [
    'app_name' => Env::get('APP_NAME', 'Padi REST API'),
    'app_env' => $appEnv,
    'app_debug' => $appDebug,
    'app_url' => Env::get('APP_URL', 'http://localhost'),
    'timezone' => Env::get('TIMEZONE', 'Asia/Jakarta'),
];
