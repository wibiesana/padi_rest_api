<?php
require_once 'vendor/autoload.php';

use Core\Env;
use Core\Database;

// Load environment
Env::load(__DIR__ . '/.env');

echo "=== DEBUG DATABASE CONNECTION ===\n";
echo "DB_HOST: " . Env::get('DB_HOST') . "\n";
echo "DB_PORT: " . Env::get('DB_PORT') . "\n";
echo "DB_DATABASE: " . Env::get('DB_DATABASE') . "\n";
echo "DB_USERNAME: " . Env::get('DB_USERNAME') . "\n";
echo "DB_PASSWORD: " . (Env::get('DB_PASSWORD') ? '[SET]' : '[NOT SET]') . "\n\n";

try {
    // Test database connection
    $db = Database::getInstance()->getConnection();
    echo "✓ Database connection successful!\n\n";

    // Test DESCRIBE query on questionbanks
    echo "=== TESTING DESCRIBE QUERY ===\n";
    $stmt = $db->query("DESCRIBE questionbanks");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        echo "❌ No schema returned from DESCRIBE questionbanks\n";
    } else {
        echo "✓ Schema found for questionbanks:\n";
        foreach ($rows as $row) {
            echo sprintf(
                "  Field: %-20s Type: %-25s Null: %-5s Default: %-15s Key: %-5s\n",
                $row['Field'],
                $row['Type'],
                $row['Null'],
                $row['Default'] ?? 'NULL',
                $row['Key'] ?? ''
            );
        }
    }

    // Test with another table
    echo "\n=== TESTING OTHER TABLES ===\n";
    $tables = ['users', 'posts'];
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("DESCRIBE {$table}");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "✓ Table '{$table}' has " . count($rows) . " columns\n";
        } catch (Exception $e) {
            echo "❌ Table '{$table}' error: " . $e->getMessage() . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "\nCheck your .env settings:\n";
    echo "- DB_HOST: " . Env::get('DB_HOST') . "\n";
    echo "- DB_DATABASE: " . Env::get('DB_DATABASE') . "\n";
    echo "- DB_USERNAME: " . Env::get('DB_USERNAME') . "\n";
}
