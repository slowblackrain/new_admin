<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;

// --- Config ---
$remoteHost = '49.247.170.176';
$remoteDb   = 'dometopia';
$remoteUser = 'dometopia';
$remotePass = '11dnjf7dlf!!';

$localHost = '127.0.0.1';
$localDb   = 'dometopia';
$localUser = 'root';
$localPass = '1111';

// --- Connect ---
echo "Connecting...\n";
try {
    $remotePdo = new PDO("mysql:host=$remoteHost;dbname=$remoteDb", $remoteUser, $remotePass);
    $remotePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $localPdo = new PDO("mysql:host=$localHost;dbname=$localDb", $localUser, $localPass);
    $localPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $localPdo->exec("SET SESSION sql_mode = ''");
} catch (Exception $e) {
    die("Connection Failed: " . $e->getMessage());
}

$tablesToSync = [
    // Category Link (Heavy - Limit this)
    'fm_category_link' => 'LIMIT 5000',
    
    // Member & Seller
    'fm_member_group' => '',
    'fm_provider' => '',
    'fm_provider_charge' => '',
    'fm_provider_person' => '',
    
    // Board
    'fm_boardmanager' => '',
    'fm_boarddata' => 'LIMIT 50',
];

function syncTable($remote, $local, $table, $limitClause = '') {
    echo "Syncing $table... ";
    
    try {
        // 1. Structure
        $stmt = $remote->query("SHOW CREATE TABLE $table");
        $createSql = $stmt->fetchColumn(1);
        $createSql = preg_replace('/AUTO_INCREMENT=\d+/', '', $createSql);
        
        $local->exec("DROP TABLE IF EXISTS $table");
        $local->exec($createSql);
        
        // 2. Data
        // Use unbuffered query for checking? No, just limit.
        $stmt = $remote->query("SELECT * FROM $table $limitClause");
        
        $count = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($count === 0) {
                 $cols = array_keys($row);
                 $colsStr = implode(',', array_map(function($c) { return "`$c`"; }, $cols));
                 $valsStr = implode(',', array_fill(0, count($cols), '?'));
                 $insertSql = "INSERT INTO $table ($colsStr) VALUES ($valsStr)";
                 $insertStmt = $local->prepare($insertSql);
            }
            $insertStmt->execute(array_values($row));
            $count++;
            if ($count % 1000 == 0) echo ".";
        }
        
        echo " Inserted $count rows.\n";
        
    } catch (Exception $e) {
        echo "Fail: " . $e->getMessage() . "\n";
    }
}

foreach ($tablesToSync as $table => $limit) {
    syncTable($remotePdo, $localPdo, $table, $limit);
}

echo "Done.\n";
