#!/usr/bin/env php
<?php

/**
 * Padi REST API - Setup Script
 * Similar to Laravel Artisan or Yii Console
 * 
 * Usage: php init.php
 */

// Colors for terminal output
class Colors
{
    public static $colors = [
        'reset' => "\033[0m",
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'cyan' => "\033[36m",
        'bold' => "\033[1m",
    ];

    public static function colorize($text, $color)
    {
        if (!isset(self::$colors[$color])) {
            return $text;
        }
        return self::$colors[$color] . $text . self::$colors['reset'];
    }
}

function info($message)
{
    echo Colors::colorize("ℹ ", 'blue') . $message . PHP_EOL;
}

function success($message)
{
    echo Colors::colorize("✓ ", 'green') . $message . PHP_EOL;
}

function error($message)
{
    echo Colors::colorize("✗ ", 'red') . $message . PHP_EOL;
}

function warning($message)
{
    echo Colors::colorize("⚠ ", 'yellow') . $message . PHP_EOL;
}

function ask($question, $default = '')
{
    $prompt = Colors::colorize($question, 'cyan');
    if ($default !== '') {
        $prompt .= Colors::colorize(" [$default]", 'yellow');
    }
    $prompt .= ": ";

    echo $prompt;
    $input = trim(fgets(STDIN));

    return $input === '' ? $default : $input;
}

function choice($question, array $options, $default = 1)
{
    echo PHP_EOL;
    echo Colors::colorize($question, 'cyan') . PHP_EOL;
    echo str_repeat('-', 60) . PHP_EOL;

    foreach ($options as $key => $label) {
        $marker = ($key == $default) ? '→' : ' ';
        echo "  $marker $key. $label" . PHP_EOL;
    }
    echo str_repeat('-', 60) . PHP_EOL;

    $input = ask("Enter your choice", $default);

    if (!isset($options[$input])) {
        warning("Invalid choice. Using default: $default");
        return $default;
    }

    return $input;
}

function banner()
{
    $banner = <<<BANNER

╔════════════════════════════════════════════════════════════════╗
║             Padi REST API Framework - Setup Wizard             ║
║                        Version 2.0                             ║
║                    Powered by PHP CLI                          ║
╚════════════════════════════════════════════════════════════════╝

BANNER;

    echo Colors::colorize($banner, 'blue') . PHP_EOL;
}

function updateEnv($key, $value)
{
    $envFile = __DIR__ . '/.env';

    if (!file_exists($envFile)) {
        error(".env file not found!");
        return false;
    }

    $content = file_get_contents($envFile);
    $pattern = "/^{$key}=.*/m";
    $replacement = "{$key}={$value}";

    if (preg_match($pattern, $content)) {
        $content = preg_replace($pattern, $replacement, $content);
    } else {
        $content .= PHP_EOL . $replacement;
    }

    return file_put_contents($envFile, $content) !== false;
}

function runCommand($command, $description = '')
{
    if ($description) {
        info($description);
    }

    $output = [];
    $returnCode = 0;
    exec($command . ' 2>&1', $output, $returnCode);

    if ($returnCode !== 0) {
        error("Command failed: $command");
        foreach ($output as $line) {
            echo "  " . $line . PHP_EOL;
        }
        return false;
    }

    foreach ($output as $line) {
        echo "  " . $line . PHP_EOL;
    }

    return true;
}

// ============================================================================
// MAIN SETUP PROCESS
// ============================================================================

try {
    banner();

    // Step 1: Check .env.example
    echo Colors::colorize("[1/7] Checking environment file...", 'yellow') . PHP_EOL;

    if (!file_exists(__DIR__ . '/.env.example')) {
        error(".env.example not found!");
        exit(1);
    }

    if (file_exists(__DIR__ . '/.env')) {
        $overwrite = ask("File .env already exists. Overwrite? (y/n)", 'n');
        if (strtolower($overwrite) === 'y') {
            copy(__DIR__ . '/.env.example', __DIR__ . '/.env');
            success(".env file updated from .env.example");
        } else {
            warning("Skipping .env creation");
        }
    } else {
        copy(__DIR__ . '/.env.example', __DIR__ . '/.env');
        success(".env file created from .env.example");
    }

    echo PHP_EOL;

    // Step 2: Choose Database Driver
    echo Colors::colorize("[2/7] Select Database Driver", 'yellow') . PHP_EOL;

    $dbChoice = choice("Please select your database:", [
        1 => 'MySQL (Default)',
        2 => 'MariaDB',
        3 => 'PostgreSQL',
        4 => 'SQLite'
    ], 1);

    $drivers = [
        1 => ['driver' => 'mysql', 'port' => '3306'],
        2 => ['driver' => 'mysql', 'port' => '3306'],
        3 => ['driver' => 'pgsql', 'port' => '5432'],
        4 => ['driver' => 'sqlite', 'port' => '']
    ];

    $selectedDriver = $drivers[$dbChoice]['driver'];
    $defaultPort = $drivers[$dbChoice]['port'];

    success("Selected: " . [1 => 'MySQL', 2 => 'MariaDB', 3 => 'PostgreSQL', 4 => 'SQLite'][$dbChoice]);

    echo PHP_EOL;

    // Step 3: Database Configuration
    echo Colors::colorize("[3/7] Database Configuration", 'yellow') . PHP_EOL;

    if ($selectedDriver === 'sqlite') {
        $dbPath = ask("SQLite database path", "database/database.sqlite");

        // Create database directory
        $dbDir = dirname(__DIR__ . '/' . $dbPath);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }

        success("SQLite will use: $dbPath");
        updateEnv('DB_CONNECTION', $selectedDriver);
        updateEnv('SQLITE_DATABASE', $dbPath);
    } else {
        $dbHost = ask("Database Host", "localhost");
        $dbPort = ask("Database Port", $defaultPort);
        $dbName = ask("Database Name", "rest_api_db");
        $dbUser = ask("Database Username", $selectedDriver === 'pgsql' ? 'postgres' : 'root');
        $dbPass = ask("Database Password (press enter for empty)", "");

        info("Configuration:");
        echo "  Host: $dbHost" . PHP_EOL;
        echo "  Port: $dbPort" . PHP_EOL;
        echo "  Database: $dbName" . PHP_EOL;
        echo "  Username: $dbUser" . PHP_EOL;

        updateEnv('DB_CONNECTION', $selectedDriver);
        updateEnv('DB_HOST', $dbHost);
        updateEnv('DB_PORT', $dbPort);
        updateEnv('DB_DATABASE', $dbName);
        updateEnv('DB_USERNAME', $dbUser);
        updateEnv('DB_PASSWORD', $dbPass);
    }

    success(".env file updated");
    echo PHP_EOL;

    // Step 4: Generate JWT Secret
    echo Colors::colorize("[4/7] Generating JWT Secret...", 'yellow') . PHP_EOL;

    $jwtSecret = bin2hex(random_bytes(32));
    updateEnv('JWT_SECRET', $jwtSecret);

    success("JWT Secret generated and saved");
    echo PHP_EOL;

    // Step 5: Run Migrations
    echo Colors::colorize("[5/7] Database Migrations", 'yellow') . PHP_EOL;

    $migrateChoice = choice("Migration options:", [
        1 => 'Migrate base tables only (users, password_resets)',
        2 => 'Migrate with examples (users, password_resets, posts, comments, tags)',
        3 => 'Skip migrations'
    ], 1);

    if ($migrateChoice == 1) {
        echo PHP_EOL;
        info("Running base migrations...");
        runCommand('php scripts/migrate.php migrate --tables=users,password_resets');
        success("Base migrations completed");
    } elseif ($migrateChoice == 2) {
        echo PHP_EOL;
        info("Running all migrations...");
        runCommand('php scripts/migrate.php migrate');
        success("All migrations completed");
    } else {
        warning("Migrations skipped");
    }

    echo PHP_EOL;

    // Step 6: Generate CRUD
    echo Colors::colorize("[6/7] CRUD Generation", 'yellow') . PHP_EOL;

    $generateChoice = choice("Generate CRUD controllers and models?", [
        1 => 'Yes - Generate for all tables',
        2 => 'Yes - Select specific tables',
        3 => 'No - Skip generation'
    ], 3);

    if ($generateChoice == 1) {
        echo PHP_EOL;
        info("Generating CRUD for all tables...");
        runCommand("php scripts/generate.php crud-all --write --driver=$selectedDriver");
        success("CRUD generation completed");
    } elseif ($generateChoice == 2) {
        echo PHP_EOL;
        info("Available tables:");
        runCommand('php scripts/generate.php list');
        echo PHP_EOL;

        $tables = ask("Enter table names (comma separated)", "");
        if ($tables) {
            $tableList = array_map('trim', explode(',', $tables));
            foreach ($tableList as $table) {
                info("Generating CRUD for $table...");
                runCommand("php scripts/generate.php crud $table --write --driver=$selectedDriver");
            }
            success("CRUD generation completed");
        }
    } else {
        warning("CRUD generation skipped");
    }

    echo PHP_EOL;

    // Step 7: Summary
    echo Colors::colorize("╔════════════════════════════════════════════════════════════════╗", 'green') . PHP_EOL;
    echo Colors::colorize("║              Setup Completed Successfully! 🎉                 ║", 'green') . PHP_EOL;
    echo Colors::colorize("╚════════════════════════════════════════════════════════════════╝", 'green') . PHP_EOL;
    echo PHP_EOL;

    echo Colors::colorize("Next Steps:", 'blue') . PHP_EOL;
    echo "  1. Start the server:    " . Colors::colorize("php -S localhost:8085 -t public", 'yellow') . PHP_EOL;
    echo "  2. Visit:               " . Colors::colorize("http://localhost:8085", 'yellow') . PHP_EOL;
    echo "  3. API Documentation:   " . Colors::colorize("http://localhost:8085/docs", 'yellow') . PHP_EOL;
    echo PHP_EOL;

    echo Colors::colorize("Quick Commands:", 'blue') . PHP_EOL;
    echo "  - List tables:          " . Colors::colorize("php scripts/generate.php list", 'yellow') . PHP_EOL;
    echo "  - Generate CRUD:        " . Colors::colorize("php scripts/generate.php crud [table] --write", 'yellow') . PHP_EOL;
    echo "  - Run migrations:       " . Colors::colorize("php scripts/migrate.php migrate", 'yellow') . PHP_EOL;
    echo "  - Rollback:            " . Colors::colorize("php scripts/migrate.php rollback", 'yellow') . PHP_EOL;
    echo PHP_EOL;

    echo Colors::colorize("Happy coding! 🚀", 'green') . PHP_EOL;
    echo PHP_EOL;
} catch (Exception $e) {
    error("Setup failed: " . $e->getMessage());
    exit(1);
}
