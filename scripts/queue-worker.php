<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Env;
use Core\Queue;

Env::load(__DIR__ . '/../.env');

$queue = $argv[1] ?? 'default';

Queue::work($queue);
