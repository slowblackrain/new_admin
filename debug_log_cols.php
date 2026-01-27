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
    
    foreach (['fm_manager_log', 'fm_order_log'] as $t) {
        $stmt = $remotePdo->query("SHOW COLUMNS FROM $t");
        echo "=== $t ===\n";
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
            echo $col['Field'] . " " . $col['Type'] . "\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
