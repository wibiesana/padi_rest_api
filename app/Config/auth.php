<?php

use Core\Env;

return [
    'jwt_secret' => Env::get('JWT_SECRET', 'your-secret-key-change-this-in-production'),
    'jwt_algorithm' => Env::get('JWT_ALGORITHM', 'HS256'),
    'jwt_expiration' => (int)Env::get('JWT_EXPIRATION', 3600), // 1 hour in seconds
    'jwt_refresh_expiration' => (int)Env::get('JWT_REFRESH_EXPIRATION', 604800), // 7 days in seconds
];
