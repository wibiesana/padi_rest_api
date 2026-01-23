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
            $sql = "CREATE TABLE IF NOT EXISTS post_tags (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                post_id INTEGER NOT NULL,
                tag_id INTEGER NOT NULL,
                created_at INTEGER DEFAULT (strftime('%s', 'now')),
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
                UNIQUE(post_id, tag_id)
            )";

            $db->exec($sql);
            $db->exec("CREATE INDEX IF NOT EXISTS idx_post_tags_post_id ON post_tags(post_id)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_post_tags_tag_id ON post_tags(tag_id)");
        } elseif ($driver === 'pgsql') {
            $sql = "CREATE TABLE IF NOT EXISTS post_tags (
                id SERIAL PRIMARY KEY,
                post_id INTEGER NOT NULL,
                tag_id INTEGER NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
                UNIQUE(post_id, tag_id)
            )";

            $db->exec($sql);
            $db->exec("CREATE INDEX IF NOT EXISTS idx_post_tags_post_id ON post_tags(post_id)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_post_tags_tag_id ON post_tags(tag_id)");
        } else {
            // MySQL/MariaDB
            $sql = "CREATE TABLE IF NOT EXISTS post_tags (
                id INT AUTO_INCREMENT PRIMARY KEY,
                post_id INT NOT NULL,
                tag_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_post_tags_post_id (post_id),
                INDEX idx_post_tags_tag_id (tag_id),
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
                UNIQUE KEY unique_post_tag (post_id, tag_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $db->exec($sql);
        }

        echo "✓ Post_tags table created (Many-to-Many relationship)\n";
    }

    public function down(): void
    {
        $db = DatabaseManager::connection();
        $db->exec("DROP TABLE IF EXISTS post_tags");
        echo "✓ Post_tags table dropped\n";
    }
};
