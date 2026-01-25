<?php
require_once 'vendor/autoload.php';

use Core\Env;
use Core\Generator;

// Load environment
Env::load(__DIR__ . '/.env');

echo "=== DEBUG GENERATOR VALIDATION RULES ===\n";

$generator = new Generator();

// Test private method getTableSchema using reflection
$reflection = new ReflectionClass($generator);
$getTableSchemaMethod = $reflection->getMethod('getTableSchema');
$getTableSchemaMethod->setAccessible(true);

$generateValidationRulesMethod = $reflection->getMethod('generateValidationRules');
$generateValidationRulesMethod->setAccessible(true);

echo "Testing table: questionbanks\n";
$schema = $getTableSchemaMethod->invoke($generator, 'questionbanks');

if (empty($schema)) {
    echo "❌ Schema is empty!\n";
} else {
    echo "✓ Schema loaded (" . count($schema) . " columns)\n";

    // Show schema
    foreach ($schema as $column => $info) {
        echo sprintf(
            "  %s: Type=%s, Null=%s, Default=%s\n",
            $column,
            $info['Type'],
            $info['Null'],
            $info['Default'] ?? 'NULL'
        );
    }

    echo "\n=== GENERATING VALIDATION RULES ===\n";
    $rules = $generateValidationRulesMethod->invoke($generator, $schema, 'questionbanks');

    if (empty($rules)) {
        echo "❌ Validation rules are empty!\n";
    } else {
        echo "✓ Validation rules generated:\n";
        foreach ($rules as $field => $rule) {
            echo "  '{$field}' => '{$rule}'\n";
        }
    }
}

// Test with other tables
echo "\n=== TESTING USERS TABLE ===\n";
$schema = $getTableSchemaMethod->invoke($generator, 'users');
$rules = $generateValidationRulesMethod->invoke($generator, $schema, 'users');
echo "Users validation rules count: " . count($rules) . "\n";
foreach ($rules as $field => $rule) {
    echo "  '{$field}' => '{$rule}'\n";
}
