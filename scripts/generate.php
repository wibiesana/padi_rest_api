#!/usr/bin/env php
<?php

/**
 * Code Generator CLI Tool
 * Usage: php generate.php [command] [options]
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Define project root path
define('PADI_ROOT', dirname(__DIR__));

use Wibiesana\Padi\Core\Generator;
use Wibiesana\Padi\Core\Env;

// Load environment
Env::load(__DIR__ . '/../.env');

// Colors for terminal output
class Colors
{
    public static $green = "\033[32m";
    public static $red = "\033[31m";
    public static $yellow = "\033[33m";
    public static $blue = "\033[34m";
    public static $reset = "\033[0m";
}

// Check if required extensions exist
// Removed hardcoded pdo_mysql check to support PostgreSQL/SQLite
if (!extension_loaded('pdo')) {
    echo Colors::$red . "Error: Extension 'pdo' is not active. Enable it in php.ini.\n" . Colors::$reset;
    exit(1);
}

// Check if required classes exist
if (!class_exists('Wibiesana\Padi\Core\Generator')) {
    echo Colors::$red . "Error: Wibiesana\Padi\Core\Generator class not found. Run 'composer install' or check your autoloader.\n" . Colors::$reset;
    exit(1);
}

// Check if in development mode
$appEnv = Env::get('APP_ENV', 'development');
if ($appEnv !== 'development') {
    echo Colors::$red . "\n";
    echo "╔════════════════════════════════════════════════════════╗\n";
    echo "║                   ACCESS DENIED                        ║\n";
    echo "║                                                        ║\n";
    echo "║  Code Generator can only be accessed in development  ║\n";
    echo "║  mode. Set APP_ENV=development in .env                ║\n";
    echo "║  Current status: APP_ENV=" . str_pad($appEnv, 24) . " ║\n";
    echo "╚════════════════════════════════════════════════════════╝\n";
    echo Colors::$reset . "\n";
    exit(1);
}

function printHeader()
{
    echo Colors::$blue . "\n";
    echo "╔════════════════════════════════════════════════════════╗\n";
    echo "║              Padi REST API - Code Generator            ║\n";
    echo "║        Generate Models, Controllers & Routes          ║\n";
    echo "╚════════════════════════════════════════════════════════╝\n";
    echo Colors::$reset . "\n";
}

function printHelp()
{
    echo "Usage: php generate.php [command] [table_name] [options]\n\n";
    echo "Commands:\n";
    echo "  model [table]          Generate Model from database table\n";
    echo "  controller [model]     Generate Controller with CRUD operations\n";
    echo "  routes [resource]      Generate Routes for a resource\n";
    echo "  crud [table]           Generate Model + Controller + Routes + Postman Collection\n";
    echo "  crud-all               Generate CRUD for all tables in database\n";
    echo "  list                   List all database tables\n";
    echo "  help                   Show this help message\n\n";
    echo "Options:\n";
    echo "  --overwrite           Overwrite existing files\n";
    echo "  --write               Auto-append routes to routes/api.php\n";
    echo "  --protected=all       Protect all routes (require auth)\n";
    echo "  --protected=none      No protected routes\n";
    echo "  --middleware=Auth     Add middleware to routes\n";
    echo "  --force               Force regenerate protected tables (not recommended)\n\n";
    echo Colors::$yellow . "Protected Tables (Auto-skipped):\n" . Colors::$reset;
    echo "  • users, password_resets, migrations\n";
    echo "  These tables have custom logic and won't be regenerated.\n\n";
    echo "Examples:\n";
    echo "  php generate.php crud categories --write\n";
    echo "  php generate.php crud post_tags --write       # Generates routes as 'post-tags'\n";
    echo "  php generate.php crud-all --overwrite --write\n";
    echo "  php generate.php model posts --overwrite\n";
    echo "  php generate.php controller Post\n";
    echo "  php generate.php routes posts --write\n";
    echo "  php generate.php routes password_resets --write  # Generates routes as 'password-resets'\n";
    echo "  php generate.php list\n\n";
    echo Colors::$green . "NEW! 🎉 API Collections\n" . Colors::$reset;
    echo "  ✓ Automatically generated for each CRUD\n";
    echo "  ✓ Saved to api_collection/ folder\n";
    echo "  ✓ Import to Postman/Insomnia for instant API testing\n";
    echo "  ✓ See docs/03-advanced/POSTMAN_GUIDE.md for usage guide\n\n";
}

function listTables()
{
    try {
        $db = Wibiesana\Padi\Core\Database::getInstance()->getConnection();
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo Colors::$green . "Available tables:\n" . Colors::$reset;
        foreach ($tables as $table) {
            echo "  - {$table}\n";
        }
        echo "\n";
    } catch (Exception $e) {
        echo Colors::$red . "Error: " . $e->getMessage() . Colors::$reset . "\n";
    }
}

function parseOptions(array $args): array
{
    $options = [];

    foreach ($args as $arg) {
        if (strpos($arg, '--') === 0) {
            $parts = explode('=', substr($arg, 2), 2);
            $key = $parts[0];
            $value = $parts[1] ?? true;

            if ($key === 'protected') {
                if ($value === 'all') {
                    $options['protected'] = ['index', 'show', 'store', 'update', 'destroy'];
                } elseif ($value === 'none') {
                    $options['protected'] = [];
                }
            } elseif ($key === 'middleware') {
                $options['middleware'] = explode(',', $value);
            } else {
                $options[$key] = $value;
            }
        }
    }

    return $options;
}

// Main execution
printHeader();

$command = $argv[1] ?? 'help';

// Determine where options start based on command
$optionsStartIndex = 2;
$target = null;

// Commands that take a target argument: model, controller, routes, crud
$targetRequired = in_array($command, ['model', 'controller', 'routes', 'crud']);

if ($targetRequired && isset($argv[2]) && strpos($argv[2], '--') !== 0) {
    $target = $argv[2];
    $optionsStartIndex = 3;
} elseif ($targetRequired && (!isset($argv[2]) || strpos($argv[2], '--') === 0)) {
    // Target is required but missing or looks like an option
    $target = null;
    $optionsStartIndex = 2;
}

$options = parseOptions(array_slice($argv, $optionsStartIndex));

$generator = new Generator();

switch ($command) {
    case 'model':
        if (!$target) {
            echo Colors::$red . "Error: Table name required\n" . Colors::$reset;
            echo "Usage: php generate.php model [table_name]\n";
            exit(1);
        }

        echo "Generating Model for table: {$target}\n\n";
        $generator->generateModel($target, $options);
        break;

    case 'controller':
        if (!$target) {
            echo Colors::$red . "Error: Model name required\n" . Colors::$reset;
            echo "Usage: php generate.php controller [ModelName]\n";
            exit(1);
        }

        echo "Generating Controller for model: {$target}\n\n";
        $generator->generateController($target, $options);
        break;

    case 'routes':
        if (!$target) {
            echo Colors::$red . "Error: Resource name required\n" . Colors::$reset;
            echo "Usage: php generate.php routes [resource_name]\n";
            exit(1);
        }

        echo "Generating Routes for resource: {$target}\n\n";
        $generator->generateRoutes($target, $options);
        break;

    case 'crud':
        if (!$target) {
            echo Colors::$red . "Error: Table name required\n" . Colors::$reset;
            echo "Usage: php generate.php crud [table_name]\n";
            exit(1);
        }

        $generator->generateCrud($target, $options);
        break;

    case 'crud-all':
        try {
            $db = Wibiesana\Padi\Core\Database::getInstance()->getConnection();
            $stmt = $db->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            echo Colors::$yellow . "Starting CRUD generation for " . count($tables) . " tables...\n" . Colors::$reset;
            echo "Protected tables will be skipped automatically.\n";
            echo str_repeat('=', 60) . "\n";

            $generated = 0;
            $skipped = 0;

            foreach ($tables as $table) {
                echo "\n> Processing table: " . Colors::$green . $table . Colors::$reset . "\n";
                $result = $generator->generateCrud($table, $options);
                if ($result) {
                    $generated++;
                } else {
                    $skipped++;
                }
            }

            echo "\n" . str_repeat('=', 60) . "\n";
            echo Colors::$green . "Generated: {$generated} tables\n" . Colors::$reset;
            if ($skipped > 0) {
                echo Colors::$yellow . "Skipped: {$skipped} protected tables\n" . Colors::$reset;
            }

            echo "\n" . Colors::$green . "SUCCESS: CRUD generation for all tables completed!" . Colors::$reset . "\n";
        } catch (Exception $e) {
            echo Colors::$red . "Error: " . $e->getMessage() . Colors::$reset . "\n";
        }
        break;

    case 'list':
        listTables();
        break;

    case 'help':
    default:
        printHelp();
        break;
}

echo "\n";
