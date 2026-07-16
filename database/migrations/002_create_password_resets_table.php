<?php

return [
    'up' => function (\PDO $pdo) {
        $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS password_resets (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    email VARCHAR(255) NOT NULL,
                    token VARCHAR(255) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    created_by INTEGER NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
                );
            ");
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_pw_email ON password_resets (email);");
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_pw_token ON password_resets (token);");
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_pw_expires_at ON password_resets (expires_at);");
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_pw_created_by ON password_resets (created_by);");
        } elseif ($driver === 'pgsql') {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS password_resets (
                    id SERIAL PRIMARY KEY,
                    email VARCHAR(255) NOT NULL,
                    token VARCHAR(255) NOT NULL,
                    expires_at TIMESTAMP NOT NULL,
                    created_by INT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
                );
            ");
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_pw_email ON password_resets (email);");
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_pw_token ON password_resets (token);");
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_pw_expires_at ON password_resets (expires_at);");
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_pw_created_by ON password_resets (created_by);");
        } else {
            // MySQL/MariaDB
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS password_resets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) NOT NULL,
                    token VARCHAR(255) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    created_by INT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
                    INDEX idx_email (email),
                    INDEX idx_token (token),
                    INDEX idx_expires_at (expires_at),
                    INDEX idx_created_by (created_by)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        echo "✓ Table 'password_resets' created successfully\n";
    },

    'down' => function (\PDO $pdo) {
        $pdo->exec("DROP TABLE IF EXISTS password_resets");
        echo "✓ Table 'password_resets' dropped successfully\n";
    }
];
