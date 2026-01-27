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
    'fm_manager_log',   // Admin Log
    'fm_member',        // Users
    'fm_order',         // Orders
    'fm_order_item',
    'fm_order_item_option',
    'fm_cash',
    'fm_offer',
    'fm_order_log',
];

$limits = [
    'fm_manager_log' => 'ORDER BY manager_log_seq DESC LIMIT 1000',
    'fm_member' => 'ORDER BY member_seq DESC LIMIT 2000',
    'fm_order' => 'ORDER BY order_seq DESC LIMIT 2000',
    'fm_order_item' => 'ORDER BY item_seq DESC LIMIT 5000',
    'fm_order_item_option' => 'LIMIT 5000',
    'fm_cash' => 'LIMIT 100',
    'fm_offer' => 'LIMIT 100',
    'fm_order_log' => 'ORDER BY log_seq DESC LIMIT 1000',
];

function syncTable($remote, $local, $table, $limitClause = '') {
    echo "Syncing $table... ";
    
    try {
        // 1. Structure
        $stmt = $remote->query("SHOW CREATE TABLE $table");
        $createSql = $stmt->fetchColumn(1);
        
        // --- CLEANUP ---
        
        // Robust Comment Stripper: Handles escaped quotes in comments e.g. 'User''s'
        $createSql = preg_replace("/COMMENT '(?:''|[^'])*'/", "", $createSql);
        
        // Remove Auto Increment
        $createSql = preg_replace('/AUTO_INCREMENT=\d+/', '', $createSql);
        
        // Fix Zero Dates
        $createSql = str_replace("DEFAULT '0000-00-00 00:00:00'", "DEFAULT '1970-01-01 00:00:00'", $createSql);
        $createSql = str_replace("DEFAULT '0000-00-00'", "DEFAULT '1970-01-01'", $createSql);
        
        // Fix Weird Defaults (like DEFAULT '''''')
        $createSql = preg_replace("/DEFAULT ''''''/", "DEFAULT ''", $createSql);

        $local->exec("DROP TABLE IF EXISTS $table");
        $local->exec($createSql);
        
        // 2. Data
        $stmt = $remote->query("SELECT * FROM $table $limitClause");
        
        $count = 0;
        $insertStmt = null;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
             if ($count === 0) {
                 $cols = array_keys($row);
                 $colsStr = implode(',', array_map(function($c) { return "`$c`"; }, $cols));
                 $valsStr = implode(',', array_fill(0, count($cols), '?'));
                 $insertSql = "INSERT INTO $table ($colsStr) VALUES ($valsStr)";
                 $insertStmt = $local->prepare($insertSql);
            }
            
            // Sanitize
            foreach ($row as $k => $v) {
                 if (is_string($v)) {
                     if (strpos($v, '0000-00-00') !== false) {
                        $row[$k] = str_replace('0000-00-00', '1970-01-01', $v);
                     }
                 }
            }
            
            $insertStmt->execute(array_values($row));
            $count++;
            if ($count % 500 == 0) echo ".";
        }
        echo " Inserted $count rows.\n";

    } catch (Exception $e) {
        echo "Fail: " . $e->getMessage() . "\n";
    }
}

foreach ($tablesToSync as $table) {
    $limit = $limits[$table] ?? 'LIMIT 1000';
    syncTable($remotePdo, $localPdo, $table, $limit);
}

echo "Done.\n";
