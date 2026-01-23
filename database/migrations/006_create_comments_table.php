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
            $sql = "CREATE TABLE IF NOT EXISTS comments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                post_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                parent_id INTEGER,
                content TEXT NOT NULL,
                status VARCHAR(20) DEFAULT 'approved',
                created_at INTEGER DEFAULT (strftime('%s', 'now')),
                updated_at INTEGER DEFAULT (strftime('%s', 'now')),
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
            )";

            $db->exec($sql);
            $db->exec("CREATE INDEX IF NOT EXISTS idx_comments_post_id ON comments(post_id)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_comments_user_id ON comments(user_id)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_comments_parent_id ON comments(parent_id)");
        } elseif ($driver === 'pgsql') {
            $sql = "CREATE TABLE IF NOT EXISTS comments (
                id SERIAL PRIMARY KEY,
                post_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                parent_id INTEGER,
                content TEXT NOT NULL,
                status VARCHAR(20) DEFAULT 'approved',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
            )";

            $db->exec($sql);
            $db->exec("CREATE INDEX IF NOT EXISTS idx_comments_post_id ON comments(post_id)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_comments_user_id ON comments(user_id)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_comments_parent_id ON comments(parent_id)");

            $db->exec("
                DROP TRIGGER IF EXISTS update_comments_updated_at ON comments;
                CREATE TRIGGER update_comments_updated_at 
                    BEFORE UPDATE ON comments 
                    FOR EACH ROW 
                    EXECUTE FUNCTION update_updated_at_column();
            ");
        } else {
            // MySQL/MariaDB
            $sql = "CREATE TABLE IF NOT EXISTS comments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                post_id INT NOT NULL,
                user_id INT NOT NULL,
                parent_id INT,
                content TEXT NOT NULL,
                status VARCHAR(20) DEFAULT 'approved',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_comments_post_id (post_id),
                INDEX idx_comments_user_id (user_id),
                INDEX idx_comments_parent_id (parent_id),
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $db->exec($sql);
        }

        echo "✓ Comments table created (with nested comments support)\n";
    }

    public function down(): void
    {
        $db = DatabaseManager::connection();
        $driver = DatabaseManager::getDriver();

        if ($driver === 'pgsql') {
            $db->exec("DROP TRIGGER IF EXISTS update_comments_updated_at ON comments");
        }

        $db->exec("DROP TABLE IF EXISTS comments");
        echo "✓ Comments table dropped\n";
    }
};
