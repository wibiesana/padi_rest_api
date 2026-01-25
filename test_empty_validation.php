<?php
require_once 'vendor/autoload.php';

use Core\Env;
use Core\Validator;

// Load environment
Env::load(__DIR__ . '/.env');

echo "=== TESTING VALIDATION WITH EMPTY RULES ===\n";

// Test case 1: Empty validation rules
$emptyRules = [];
$emptyData = [];

$validator = new Validator($emptyData, $emptyRules);
$isValid = $validator->validate();

echo "Empty rules + Empty data:\n";
echo "Valid: " . ($isValid ? 'YES' : 'NO') . "\n";
echo "Validated data: " . json_encode($validator->validated()) . "\n";
echo "Errors: " . json_encode($validator->errors()) . "\n\n";

// Test case 2: Empty rules with data
$emptyRules = [];
$someData = ['name' => '', 'email' => ''];

$validator2 = new Validator($someData, $emptyRules);
$isValid2 = $validator2->validate();

echo "Empty rules + Some empty data:\n";
echo "Valid: " . ($isValid2 ? 'YES' : 'NO') . "\n";
echo "Validated data: " . json_encode($validator2->validated()) . "\n";
echo "Errors: " . json_encode($validator2->errors()) . "\n\n";

// Test case 3: Required rules with empty data  
$requiredRules = ['name' => 'required', 'email' => 'required'];
$emptyData2 = ['name' => '', 'email' => ''];

$validator3 = new Validator($emptyData2, $requiredRules);
$isValid3 = $validator3->validate();

echo "Required rules + Empty data:\n";
echo "Valid: " . ($isValid3 ? 'YES' : 'NO') . "\n";
echo "Validated data: " . json_encode($validator3->validated()) . "\n";
echo "Errors: " . json_encode($validator3->errors()) . "\n";
