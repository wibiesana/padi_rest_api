<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=cleversim_api_new', 'root', 'wibie', [
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
