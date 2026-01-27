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
    $stmt = $remotePdo->query("SHOW CREATE TABLE fm_order");
    echo $stmt->fetchColumn(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
