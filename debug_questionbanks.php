<?php
require_once 'vendor/autoload.php';

use Core\Env;
use Core\DatabaseManager;

// Load environment
Env::load(__DIR__ . '/.env');

try {
    $db = DatabaseManager::connection();
    echo "Database connected successfully!\n";

    // Test query to check questionbanks table
    $stmt = $db->query("DESCRIBE questionbanks");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "QuestionBanks table structure:\n";
    foreach ($rows as $row) {
        echo sprintf(
            "Field: %-20s Type: %-20s Null: %-5s Default: %-10s Key: %-5s\n",
            $row['Field'],
            $row['Type'],
            $row['Null'],
            $row['Default'] ?? 'NULL',
            $row['Key'] ?? ''
        );
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Table questionbanks mungkin belum ada. Silakan buat terlebih dahulu.\n";
}
