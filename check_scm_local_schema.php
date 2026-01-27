<?php
$host = '127.0.0.1';
$db   = 'dometopia';
$user = 'root';
$pass = '1111';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    echo "Connecting to Local DB ($host)...\n";
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $tables = ['fm_scm_stock_revision', 'fm_scm_stock_revision_goods'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("DESCRIBE $table");
            echo "Table '$table' EXISTS.\n";
        } catch (\PDOException $e) {
            echo "Table '$table' MISSING.\n";
        }
    }
    
} catch (\PDOException $e) {
    echo "Connection Failed: " . $e->getMessage() . "\n";
}
