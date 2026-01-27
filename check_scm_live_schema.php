<?php
$host = '49.247.170.176';
$db   = 'dometopia';
$user = 'dometopia';
$pass = '11dnjf7dlf!!';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    echo "Connecting to Live DB...\n";
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $tables = ['fm_scm_stock_revision', 'fm_scm_stock_revision_goods'];
    
    foreach ($tables as $table) {
        echo "\nCheck Table: $table\n";
        try {
            $stmt = $pdo->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            print_r($columns);
        } catch (\PDOException $e) {
            echo "MISSING or Error: " . $e->getMessage() . "\n";
        }
    }
    
} catch (\PDOException $e) {
    echo "Connection Failed: " . $e->getMessage() . "\n";
}
