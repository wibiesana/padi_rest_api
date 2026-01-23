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
            $sql = "CREATE TABLE IF NOT EXISTS posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                content TEXT,
                excerpt TEXT,
                featured_image TEXT,
                status VARCHAR(20) DEFAULT 'draft',
                published_at INTEGER,
                views INTEGER DEFAULT 0,
                created_at INTEGER DEFAULT (strftime('%s', 'now')),
                updated_at INTEGER DEFAULT (strftime('%s', 'now')),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )";

            $db->exec($sql);

            $db->exec("CREATE INDEX IF NOT EXISTS idx_posts_user_id ON posts(user_id)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_posts_slug ON posts(slug)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_posts_status ON posts(status)");
        } elseif ($driver === 'pgsql') {
            $sql = "CREATE TABLE IF NOT EXISTS posts (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                content TEXT,
                excerpt TEXT,
                featured_image TEXT,
                status VARCHAR(20) DEFAULT 'draft',
                published_at TIMESTAMP,
                views INTEGER DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )";

            $db->exec($sql);

            $db->exec("CREATE INDEX IF NOT EXISTS idx_posts_user_id ON posts(user_id)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_posts_slug ON posts(slug)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_posts_status ON posts(status)");

            $db->exec("
                DROP TRIGGER IF EXISTS update_posts_updated_at ON posts;
                CREATE TRIGGER update_posts_updated_at 
                    BEFORE UPDATE ON posts 
                    FOR EACH ROW 
                    EXECUTE FUNCTION update_updated_at_column();
            ");
        } else {
            // MySQL/MariaDB
            $sql = "CREATE TABLE IF NOT EXISTS posts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                content TEXT,
                excerpt TEXT,
                featured_image TEXT,
                status VARCHAR(20) DEFAULT 'draft',
                published_at TIMESTAMP NULL,
                views INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_posts_user_id (user_id),
                INDEX idx_posts_slug (slug),
                INDEX idx_posts_status (status),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $db->exec($sql);
        }

        echo "✓ Posts table created\n";
    }

    public function down(): void
    {
        $db = DatabaseManager::connection();
        $driver = DatabaseManager::getDriver();

        if ($driver === 'pgsql') {
            $db->exec("DROP TRIGGER IF EXISTS update_posts_updated_at ON posts");
        }

        $db->exec("DROP TABLE IF EXISTS posts");
        echo "✓ Posts table dropped\n";
    }
};
