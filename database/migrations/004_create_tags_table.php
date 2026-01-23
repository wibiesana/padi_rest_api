<?php

declare(strict_types=1);

use Core\DatabaseManager;

return new class
{
    public function up(): void
    {
        $db = DatabaseManager::connection();
        $driver = DatabaseManager::getDriver();

        if ($driver === 'sqlite') {
            $sql = "CREATE TABLE IF NOT EXISTS tags (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) NOT NULL UNIQUE,
                slug VARCHAR(100) NOT NULL UNIQUE,
                description TEXT,
                created_at INTEGER DEFAULT (strftime('%s', 'now')),
                updated_at INTEGER DEFAULT (strftime('%s', 'now'))
            )";

            $db->exec($sql);
            $db->exec("CREATE INDEX IF NOT EXISTS idx_tags_slug ON tags(slug)");
        } elseif ($driver === 'pgsql') {
            $sql = "CREATE TABLE IF NOT EXISTS tags (
                id SERIAL PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                slug VARCHAR(100) NOT NULL UNIQUE,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";

            $db->exec($sql);
            $db->exec("CREATE INDEX IF NOT EXISTS idx_tags_slug ON tags(slug)");

            $db->exec("
                DROP TRIGGER IF EXISTS update_tags_updated_at ON tags;
                CREATE TRIGGER update_tags_updated_at 
                    BEFORE UPDATE ON tags 
                    FOR EACH ROW 
                    EXECUTE FUNCTION update_updated_at_column();
            ");
        } else {
            // MySQL/MariaDB
            $sql = "CREATE TABLE IF NOT EXISTS tags (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                slug VARCHAR(100) NOT NULL UNIQUE,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tags_slug (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $db->exec($sql);
        }

        echo "✓ Tags table created\n";
    }

    public function down(): void
    {
        $db = DatabaseManager::connection();
        $driver = DatabaseManager::getDriver();

        if ($driver === 'pgsql') {
            $db->exec("DROP TRIGGER IF EXISTS update_tags_updated_at ON tags");
        }

        $db->exec("DROP TABLE IF EXISTS tags");
        echo "✓ Tags table dropped\n";
    }
};
