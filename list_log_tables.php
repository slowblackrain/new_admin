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
    $stmt = $remotePdo->query("SHOW TABLES LIKE '%manager%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Manager Tables: " . implode(', ', $tables) . "\n";
    
    $stmt = $remotePdo->query("SHOW TABLES LIKE '%log%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Log Tables: " . implode(', ', $tables) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
