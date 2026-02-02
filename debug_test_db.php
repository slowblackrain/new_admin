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
    echo "Connecting to $db at $host as $user...\n";
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Connected successfully.\n";

    // Check if table exists
    echo "Checking for 'fm_member'...\n";
    
    // Check using SHOW TABLES
    $stmt = $pdo->query("SHOW TABLES LIKE 'fm_goods'");
    $table = $stmt->fetch();
    
    if ($table) {
        echo "Table 'fm_goods' exists.\n";
        $stmt = $pdo->query("DESCRIBE fm_goods");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo $row['Field'] . " (" . $row['Type'] . ")\n";
        }
     // List tables matching fm_account%
echo "Checking tables matching fm_account%...\n";
$stmt = $pdo->query("SHOW TABLES LIKE 'fm_account%'");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    echo $table . "\n";
    // Describe each found table
    echo "  Schema for $table:\n";
    $desc = $pdo->query("DESCRIBE $table");
    foreach ($desc->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo "    " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    echo "\n";
}        
    } else {
        echo "Table 'fm_member' DOES NOT EXIST.\n";
        
        echo "Listing all tables:\n";
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            echo $row[0] . "\n";
        }
    }

} catch (\PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
}
