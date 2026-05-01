<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=cleversim_api_new', 'root', 'wibie', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "### TEACHER TABLE ###\n";
    $s = $db->query('DESCRIBE teacher');
    foreach($s->fetchAll() as $r) {
        if(strpos($r['Field'], 'at') !== false) echo $r['Field'].': '.$r['Type']."\n";
    }

    echo "\n### STAFF TABLE ###\n";
    $s = $db->query('DESCRIBE staff');
    foreach($s->fetchAll() as $r) {
        if(strpos($r['Field'], 'at') !== false) echo $r['Field'].': '.$r['Type']."\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
