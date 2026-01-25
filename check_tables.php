<?php
require_once 'vendor/autoload.php';

use Core\Env;
use Core\Database;

// Load environment
Env::load(__DIR__ . '/.env');

try {
    $db = Database::getInstance()->getConnection();

    // Get all tables in database
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "=== TABLES IN DATABASE ===\n";
    if (empty($tables)) {
        echo "❌ No tables found in database!\n";
    } else {
        echo "✓ Found " . count($tables) . " tables:\n";
        foreach ($tables as $table) {
            echo "  - {$table}\n";
        }
    }

    echo "\n=== CHECKING COMMON CONTROLLER TABLES ===\n";
    $commonTables = ['students', 'teachers', 'subjects', 'classes'];

    foreach ($commonTables as $table) {
        if (in_array($table, $tables)) {
            echo "✓ {$table} exists\n";
        } else {
            echo "❌ {$table} NOT found\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
