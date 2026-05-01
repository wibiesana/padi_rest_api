<?php
require 'vendor/autoload.php';

try {
    $dsn = "mysql:host=localhost;port=3306;dbname=cleversim_api_new;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', 'wibie', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($columns, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo $e->getMessage();
}
