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
            $sql = "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(50) UNIQUE,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(50) DEFAULT 'user',
                status VARCHAR(20) DEFAULT 'active',
                email_verified_at INTEGER,
                remember_token VARCHAR(100),
                last_login_at INTEGER,
                created_by INTEGER,
                updated_by INTEGER,
                created_at INTEGER DEFAULT (strftime('%s', 'now')),
                updated_at INTEGER DEFAULT (strftime('%s', 'now')),
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
            )";

            $db->exec($sql);

            // Create indexes
            $db->exec("CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_users_status ON users(status)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_users_role ON users(role)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_users_created_by ON users(created_by)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_users_updated_by ON users(updated_by)");
        } elseif ($driver === 'pgsql') {
            $sql = "CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                username VARCHAR(50) UNIQUE,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(50) DEFAULT 'user',
                status VARCHAR(20) DEFAULT 'active',
                email_verified_at TIMESTAMP,
                remember_token VARCHAR(100),
                last_login_at TIMESTAMP,
                created_by INTEGER,
                updated_by INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
            )";

            $db->exec($sql);

            // Create indexes
            $db->exec("CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_users_status ON users(status)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_users_role ON users(role)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_users_created_by ON users(created_by)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_users_updated_by ON users(updated_by)");

            // Create trigger for updated_at
            $db->exec("
                CREATE OR REPLACE FUNCTION update_updated_at_column()
                RETURNS TRIGGER AS \$\$
                BEGIN
                    NEW.updated_at = CURRENT_TIMESTAMP;
                    RETURN NEW;
                END;
                \$\$ language 'plpgsql';
            ");

            $db->exec("
                DROP TRIGGER IF EXISTS update_users_updated_at ON users;
                CREATE TRIGGER update_users_updated_at 
                    BEFORE UPDATE ON users 
                    FOR EACH ROW 
                    EXECUTE FUNCTION update_updated_at_column();
            ");
        } else {
            // MySQL/MariaDB
            $sql = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(50) DEFAULT 'user',
                status VARCHAR(20) DEFAULT 'active',
                email_verified_at TIMESTAMP NULL,
                remember_token VARCHAR(100),
                last_login_at TIMESTAMP NULL,
                created_by INT NULL,
                updated_by INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_users_email (email),
                INDEX idx_users_username (username),
                INDEX idx_users_status (status),
                INDEX idx_users_role (role),
                INDEX idx_users_created_by (created_by),
                INDEX idx_users_updated_by (updated_by),
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $db->exec($sql);
        }

        echo "✓ Users table created\n";
    }

    public function down(): void
    {
        $db = DatabaseManager::connection();
        $driver = DatabaseManager::getDriver();

        if ($driver === 'pgsql') {
            // Drop trigger and function first
            $db->exec("DROP TRIGGER IF EXISTS update_users_updated_at ON users");
            $db->exec("DROP FUNCTION IF EXISTS update_updated_at_column()");
        }

        $db->exec("DROP TABLE IF EXISTS user");
        echo "✓ Users table dropped\n";
    }
};
