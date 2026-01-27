<?php
// Remote Credentials
$host = '49.247.170.176';
$db   = 'dometopia';
$user = 'dometopia';
$pass = '11dnjf7dlf!!';

$targetId = '2026012702530919792';

echo "Connecting to Remote DB ($host)...\n";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Connection Failed: " . $e->getMessage() . "\n");
}

echo "Checking for Order ID: $targetId\n";
$stmt = $pdo->prepare("SELECT order_seq, regist_date, step FROM fm_order WHERE order_seq = ?");
$stmt->execute([$targetId]);
$order = $stmt->fetch();

if ($order) {
    echo "FOUND IN REMOTE DB!\n";
    print_r($order);
} else {
    echo "NOT FOUND in Remote DB.\n";
    
    // Check similar
    echo "Checking similar IDs (202601270253%)...\n";
    $stmt = $pdo->prepare("SELECT order_seq FROM fm_order WHERE order_seq LIKE '202601270253%' LIMIT 5");
    $stmt->execute();
    $list = $stmt->fetchAll();
    print_r($list);
}
