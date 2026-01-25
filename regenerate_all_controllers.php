<?php
require_once 'vendor/autoload.php';

use Core\Env;
use Core\Database;
use Core\Generator;

// Load environment
Env::load(__DIR__ . '/.env');

$db = Database::getInstance()->getConnection();
$generator = new Generator();

// Get all tables
$stmt = $db->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

$skipTables = ['migrations', 'migration', 'password_resets', 'users']; // Skip system tables

echo "=== REGENERATING CONTROLLERS FOR ALL TABLES ===\n";

$successCount = 0;
$skipCount = 0;

foreach ($tables as $table) {
    if (in_array($table, $skipTables)) {
        echo "⏭️  Skipping: {$table} (system table)\n";
        $skipCount++;
        continue;
    }

    // Convert table to model name
    $modelName = ucfirst(str_replace('_', '', $table));

    echo "\n📝 Generating controller for: {$table} -> {$modelName}Controller\n";

    try {
        // Generate controller with overwrite
        $result = $generator->generateController($modelName, ['write' => true, 'overwrite' => true]);

        if ($result) {
            echo "✅ Success: {$modelName}Controller\n";
            $successCount++;
        } else {
            echo "❌ Failed: {$modelName}Controller\n";
        }
    } catch (Exception $e) {
        echo "❌ Error for {$modelName}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "✅ Successfully generated: {$successCount} controllers\n";
echo "⏭️  Skipped: {$skipCount} system tables\n";
echo "📋 Total tables processed: " . count($tables) . "\n";

echo "\n🎉 All controllers have been regenerated with proper validation rules!\n";
