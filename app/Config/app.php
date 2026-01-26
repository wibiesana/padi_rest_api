<?php

use Core\Env;

return [
    'app_name' => Env::get('APP_NAME', 'Padi REST API'),
    'app_env' => Env::get('APP_ENV', 'production'),
    'app_debug' => Env::get('APP_DEBUG', false),
    'app_url' => Env::get('APP_URL', 'http://localhost'),
    'timezone' => Env::get('TIMEZONE', 'Asia/Jakarta'),
];
