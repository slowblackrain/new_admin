<?php
require __DIR__ . '/vendor/autoload.php';

$remoteHost = '49.247.170.176';
$remoteDb   = 'dometopia';
$remoteUser = 'dometopia';
$remotePass = '11dnjf7dlf!!';

echo "=== TEST 1: No Charset (Default) ===\n";
try {
    $pdo = new PDO("mysql:host=$remoteHost;dbname=$remoteDb", $remoteUser, $remotePass);
    $stmt = $pdo->query("SELECT title FROM fm_category LIMIT 1");
    $val = $stmt->fetchColumn();
    echo "Raw: $val\n";
    echo "Hex: " . bin2hex($val) . "\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }

echo "\n=== TEST 2: DSN charset=utf8 ===\n";
try {
    $pdo = new PDO("mysql:host=$remoteHost;dbname=$remoteDb;charset=utf8", $remoteUser, $remotePass);
    $stmt = $pdo->query("SELECT title FROM fm_category LIMIT 1");
    $val = $stmt->fetchColumn();
    echo "Raw: $val\n";
    echo "Hex: " . bin2hex($val) . "\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }

echo "\n=== TEST 3: DSN charset=euckr ===\n";
try {
    $pdo = new PDO("mysql:host=$remoteHost;dbname=$remoteDb;charset=euckr", $remoteUser, $remotePass);
    $stmt = $pdo->query("SELECT title FROM fm_category LIMIT 1");
    $val = $stmt->fetchColumn();
    echo "Raw: $val\n";
    echo "Hex: " . bin2hex($val) . "\n";
    echo "Converted (EUC-KR -> UTF-8): " . mb_convert_encoding($val, 'UTF-8', 'EUC-KR') . "\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }
