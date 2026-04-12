<?php
require 'vendor/autoload.php';

// Load env
\Wibiesana\Padi\Core\Env::load(__DIR__);

$model = new \App\Models\User();

$r = new ReflectionClass($model);
$p = $r->getProperty('fillable');
$p->setAccessible(true);
echo "Fillable: \n";
print_r($p->getValue($model));

$data = ['username' => 'test', 'created_at' => '2026-04-12'];
$m = $r->getMethod('filterFillable');
$m->setAccessible(true);
echo "Filtered: \n";
print_r($m->invoke($model, $data));
