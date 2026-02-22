<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Wibiesana\Padi\Core\Env;
use Wibiesana\Padi\Core\Queue;

Env::load(__DIR__ . '/../.env');

$queue = $argv[1] ?? 'default';

Queue::work($queue);
