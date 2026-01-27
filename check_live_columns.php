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
    echo "Connecting to $host...\n";
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Connected successfully to Live DB.\n";
    
    echo "Checking columns for 'fm_goods':\n";
    $stmt = $pdo->query("DESCRIBE fm_goods");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $checkCols = ['model', 'maker_name', 'origin_name', 'offer_chk'];
    foreach ($checkCols as $col) {
        if (in_array($col, $columns)) {
            echo "Column '$col': EXISTS\n";
        } else {
            echo "Column '$col': MISSING\n";
        }
    }
    
} catch (\PDOException $e) {
    echo "Connection Failed: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
}
