<?php
require_once 'vendor/autoload.php';

echo "=== TESTING PHP EMPTY() FUNCTION ===\n";

$testValues = [
    "null" => null,
    "empty string" => '',
    "whitespace" => '   ',
    "zero string" => '0',
    "zero integer" => 0,
    "false" => false,
    "empty array" => [],
    "string with value" => 'test'
];

foreach ($testValues as $label => $value) {
    $isEmpty = empty($value);
    $isZeroString = $value === '0';
    $shouldPass = !$isEmpty || $isZeroString; // Current logic in validator

    echo sprintf(
        "%-15s | Value: %-6s | empty(): %-5s | Pass required?: %s\n",
        $label,
        var_export($value, true),
        $isEmpty ? 'TRUE' : 'FALSE',
        $shouldPass ? 'YES' : 'NO'
    );
}

echo "\n=== REAL VALIDATION TEST ===\n";

use Core\Validator;

// Test dengan data yang seharusnya gagal validation
$testData = [
    'name' => '',
    'email' => '   ',
    'description' => null
];

$rules = [
    'name' => 'required',
    'email' => 'required',
    'description' => 'required'
];

$validator = new Validator($testData, $rules);
$isValid = $validator->validate();

echo "Data: " . json_encode($testData) . "\n";
echo "Rules: " . json_encode($rules) . "\n";
echo "Valid: " . ($isValid ? 'YES' : 'NO') . "\n";
echo "Errors: " . json_encode($validator->errors(), JSON_PRETTY_PRINT) . "\n";
