<?php
require_once 'vendor/autoload.php';

use Core\Env;
use Core\DatabaseManager;

// Load environment
Env::load(__DIR__ . '/.env');

try {
    $db = DatabaseManager::connection();
    echo "Database connected successfully!\n";

    // Test query to check posts table
    $stmt = $db->query("DESCRIBE posts");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Posts table structure:\n";
    foreach ($rows as $row) {
        echo sprintf(
            "Field: %-20s Type: %-20s Null: %-5s Default: %-10s\n",
            $row['Field'],
            $row['Type'],
            $row['Null'],
            $row['Default'] ?? 'NULL'
        );
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
