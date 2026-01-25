<?php
require_once 'vendor/autoload.php';

use Core\Env;
use Core\Database;

Env::load('.env');
$db = Database::getInstance()->getConnection();

echo "=== TABLES CONTAINING 'exam' ===\n";
$stmt = $db->query("SHOW TABLES LIKE '%exam%'");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    echo "- $table\n";
}

echo "\n=== CHECKING exam_class TABLE STRUCTURE ===\n";
try {
    $stmt = $db->query("DESCRIBE exam_class");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($columns)) {
        echo "❌ No columns found for table exam_class\n";
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
    echo "❌ Error checking exam_class: " . $e->getMessage() . "\n";
}

echo "\n=== CHECKING exam_class_user TABLE STRUCTURE ===\n";
try {
    $stmt = $db->query("DESCRIBE exam_class_user");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($columns)) {
        echo "❌ No columns found for table exam_class_user\n";
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
    echo "❌ Error checking exam_class_user: " . $e->getMessage() . "\n";
}
