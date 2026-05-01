<?php
require 'vendor/autoload.php';
// Boot the framework minimal if possible
// Or just use PDO for direct check

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $dsn = "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']};charset=utf8mb4";
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $stmt = $pdo->query("SELECT * FROM question_bank ORDER BY id DESC LIMIT 1");
    $row = $stmt->fetch();
    echo "Last Question Bank:\n";
    print_r($row);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
