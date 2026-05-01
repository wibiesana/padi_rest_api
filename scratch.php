<?php
require 'vendor/autoload.php';

$app = new \Wibiesana\Padi\Core\Application(__DIR__);
$app->boot();

$model = new \App\Models\User();
$data = [
    'email' => 'test@test.com',
    'username' => 'test',
    'password' => '123',
    'role' => 'student',
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
];

echo "Fillable array in User model:\n";
$reflection = new \ReflectionClass(get_class($model));
$prop = $reflection->getProperty('fillable');
$prop->setAccessible(true);
$fillable = $prop->getValue($model);
print_r($fillable);

echo "Data after filterFillable:\n";
$method = $reflection->getMethod('filterFillable');
$method->setAccessible(true);
$filtered = $method->invoke($model, $data);
print_r($filtered);
