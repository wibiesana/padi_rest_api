<?php
require_once 'vendor/autoload.php';

use Core\Env;
use Core\DatabaseManager;

Env::load('.env');
$db = DatabaseManager::connection();

$sql = "CREATE TABLE IF NOT EXISTS questionbanks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    subject VARCHAR(100) NOT NULL,
    category VARCHAR(100),
    difficulty_level ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    question_text TEXT NOT NULL,
    question_type ENUM('multiple_choice', 'essay', 'true_false', 'fill_blank') NOT NULL DEFAULT 'multiple_choice',
    option_a VARCHAR(500),
    option_b VARCHAR(500),
    option_c VARCHAR(500),
    option_d VARCHAR(500),
    correct_answer VARCHAR(500) NOT NULL,
    explanation TEXT,
    tags VARCHAR(255),
    status ENUM('active', 'inactive', 'draft') NOT NULL DEFAULT 'active',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_subject (subject),
    INDEX idx_category (category),
    INDEX idx_difficulty (difficulty_level),
    INDEX idx_type (question_type),
    INDEX idx_status (status)
)";

try {
    $db->exec($sql);
    echo "QuestionBanks table created successfully!\n\n";

    // Show table structure
    $stmt = $db->query("DESCRIBE questionbanks");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Table structure:\n";
    foreach ($rows as $row) {
        echo sprintf(
            "Field: %-20s Type: %-30s Null: %-5s Default: %-15s\n",
            $row['Field'],
            $row['Type'],
            $row['Null'],
            $row['Default'] ?? 'NULL'
        );
    }
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
