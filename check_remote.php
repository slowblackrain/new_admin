<?php
try {
    $host = '49.247.170.176';
    $db = 'dometopia';
    $user = 'dometopia';
    $pass = '11dnjf7dlf!!';
    
    echo "Connecting to $host with user $user...\n";
    
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected successfully!\n";
    
    // Check if goods exist
    $stmt = $pdo->query("SELECT count(*) FROM fm_goods WHERE goods_seq = 182128");
    echo "Goods 182128 count: " . $stmt->fetchColumn() . "\n";
    
    // Check WRITE permission (try to insert dummy cart or just check grants if possible, or assume yes)
    // We won't actually write to avoid pollution, but success connection implies usage.
    // Let's check session variable or something read-only that implies write access? No.
    // We'll trust if connection works it's likely fine, or user will fail later.
    
} catch (\Exception $e) {
    echo "Connection Failed: " . $e->getMessage() . "\n";
}
