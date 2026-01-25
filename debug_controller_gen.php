<?php
require_once 'vendor/autoload.php';

use Core\Env;
use Core\Generator;

// Load environment
Env::load(__DIR__ . '/.env');

echo "=== DEBUG CONTROLLER GENERATION ===\n";

$generator = new Generator();
$reflection = new ReflectionClass($generator);

// Get private methods
$getTableSchemaMethod = $reflection->getMethod('getTableSchema');
$getTableSchemaMethod->setAccessible(true);

$generateValidationRulesMethod = $reflection->getMethod('generateValidationRules');
$generateValidationRulesMethod->setAccessible(true);

$getBaseControllerTemplateMethod = $reflection->getMethod('getBaseControllerTemplate');
$getBaseControllerTemplateMethod->setAccessible(true);

$modelNameToTableNameMethod = $reflection->getMethod('modelNameToTableName');
$modelNameToTableNameMethod->setAccessible(true);

// Test for Student model
$modelName = 'Student';
$tableName = $modelNameToTableNameMethod->invoke($generator, $modelName);

echo "Model: {$modelName}\n";
echo "Table: {$tableName}\n\n";

// Check schema
$schema = $getTableSchemaMethod->invoke($generator, $tableName);
echo "Schema count: " . count($schema) . "\n";

if (empty($schema)) {
    echo "❌ Schema is empty for table: {$tableName}\n";
    echo "Table might not exist!\n";
} else {
    // Generate validation rules
    $validationRules = $generateValidationRulesMethod->invoke($generator, $schema, $tableName);
    echo "Validation rules count: " . count($validationRules) . "\n";

    if (empty($validationRules)) {
        echo "❌ Validation rules are empty!\n";
    } else {
        echo "✓ Validation rules:\n";
        foreach ($validationRules as $field => $rule) {
            echo "  '{$field}' => '{$rule}'\n";
        }
    }

    // Generate template preview (just first part)
    $template = $getBaseControllerTemplateMethod->invoke($generator, $modelName, $modelName . 'Controller', 'App\\Controllers\\Base', 'App\\Models', $validationRules);

    // Extract store method part
    $storeStart = strpos($template, 'public function store()');
    $storeEnd = strpos($template, 'public function update()', $storeStart);
    $storeMethod = substr($template, $storeStart, $storeEnd - $storeStart);

    echo "\n=== STORE METHOD PREVIEW ===\n";
    echo $storeMethod;
}
