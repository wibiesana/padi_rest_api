<?php
require_once 'vendor/autoload.php';

use Core\Env;
use Core\Database;

Env::load('.env');
$db = Database::getInstance()->getConnection();

$tableName = 'violation_counseling';

echo "=== SCHEMA FOR TABLE: {$tableName} ===\n";

try {
    $stmt = $db->query("DESCRIBE {$tableName}");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($columns)) {
        echo "❌ No columns found for table {$tableName}\n";
    } else {
        echo "✓ Found " . count($columns) . " columns:\n\n";
        foreach ($columns as $column) {
            $required = $column['Null'] === 'NO' && !in_array($column['Field'], ['id', 'created_at', 'updated_at', 'deleted_at'])
                && $column['Default'] === null
                && strpos($column['Extra'], 'auto_increment') === false;

            $marker = $required ? '⚠️  REQUIRED' : '   optional';

            echo sprintf(
                "  %s | %-20s | %-15s | %-8s | %s\n",
                $marker,
                $column['Field'],
                $column['Type'],
                $column['Null'],
                $column['Default'] ?? 'NULL'
            );
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
