<?php
require 'vendor/autoload.php';

// Manually load .env since Env::load might be finicky in CLI
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

try {
    $dsn = "mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME') . ";charset=utf8mb4";
    $db = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "### USERS TABLE ###\n";
    $s = $db->query('DESCRIBE users');
    foreach($s->fetchAll() as $r) {
        echo $r['Field'].': '.$r['Type']."\n";
    }

    echo "\n### STUDENT TABLE ###\n";
    $s = $db->query('DESCRIBE student');
    foreach($s->fetchAll() as $r) {
        echo $r['Field'].': '.$r['Type']."\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
