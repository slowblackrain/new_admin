<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

// Remote Credentials
$host = '49.247.170.176';
$db   = 'dometopia';
$user = 'dometopia';
$pass = '11dnjf7dlf!!';

try {
    $remotePdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $remotePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = ['fm_provider', 'fm_boardmanager'];
    
    foreach ($tables as $t) {
        $stmt = $remotePdo->query("SHOW CREATE TABLE $t");
        $sql = $stmt->fetchColumn(1);
        echo "=== $t ===\n$sql\n\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
