<?php

use Wibiesana\Padi\Core\Env;

return [
    'driver' => Env::get('MAIL_DRIVER', 'smtp'),
    'host' => Env::get('MAIL_HOST', 'smtp.mailtrap.io'),
    'port' => Env::get('MAIL_PORT', 2525),
    'username' => Env::get('MAIL_USERNAME', null),
    'password' => Env::get('MAIL_PASSWORD', null),
    'encryption' => Env::get('MAIL_ENCRYPTION', 'tls'),
    'from_address' => Env::get('MAIL_FROM_ADDRESS', 'noreply@example.com'),
    'from_name' => Env::get('MAIL_FROM_NAME', Env::get('APP_NAME', 'Padi REST API')),
];
