<?php
// Direct PDO connection to avoid framework bootstrapping issues
$host = '127.0.0.1';
$db   = 'dometopia';
$user = 'root';
$pass = '1111';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $stmt = $pdo->query("SHOW COLUMNS FROM fm_category");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($cols as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
