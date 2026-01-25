<?php
require_once 'vendor/autoload.php';

use Core\Env;
use Core\Database;

Env::load('.env');
$db = Database::getInstance()->getConnection();
$stmt = $db->query('DESCRIBE student');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Student table structure:\n";
foreach ($rows as $row) {
    echo sprintf(
        "Field: %-20s Type: %-20s Null: %-5s Default: %-10s\n",
        $row['Field'],
        $row['Type'],
        $row['Null'],
        $row['Default'] ?? 'NULL'
    );
}
