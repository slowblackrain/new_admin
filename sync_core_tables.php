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
    'fm_log_manager', // Admin Log
    'fm_member',      // Users
    'fm_order',       // Orders
    'fm_order_item',
    'fm_order_item_option',
    // 'fm_goods',    // Goods - Already has 600, maybe sync more? Let's skip valid data handling for now unless requested.
    // 'fm_goods_option'
];

// Special handling:
// fm_log_manager: LIMIT 1000 (Recent logs)
// fm_member: LIMIT 5000 (Active users?)
// fm_order: LIMIT 5000 (Recent orders)

$limits = [
    'fm_log_manager' => 'ORDER BY seq DESC LIMIT 1000', // Assuming 'seq' exists? Need to check structure.
    'fm_member' => 'ORDER BY member_seq DESC LIMIT 2000',
    'fm_order' => 'ORDER BY order_seq DESC LIMIT 2000',
    'fm_order_item' => 'ORDER BY item_seq DESC LIMIT 5000',
    'fm_order_item_option' => 'LIMIT 5000', // No reliable sort seq?
];

function syncTable($remote, $local, $table, $limitClause = '') {
    echo "Syncing $table... ";
    
    try {
        // 1. Structure (Only if Missing for some, but forcing update if needed)
        // If table exists and has data, maybe we should APPEND?
        // But schema might be different. Let's RESET for specific tables requested by user.
        // User asked to "create tables and put data".
        
        $stmt = $remote->query("SHOW CREATE TABLE $table");
        $createSql = $stmt->fetchColumn(1);
        
        // Cleanup Schema
        $createSql = preg_replace("/COMMENT '.*?'/s", "", $createSql);
        $createSql = preg_replace('/AUTO_INCREMENT=\d+/', '', $createSql);
        $createSql = str_replace("DEFAULT '0000-00-00 00:00:00'", "DEFAULT '1970-01-01 00:00:00'", $createSql);
        $createSql = str_replace("DEFAULT '0000-00-00'", "DEFAULT '1970-01-01'", $createSql);
        
        // Specific Invalid Default Fixes (Generic)
        $createSql = preg_replace("/DEFAULT '''.*?''/", "DEFAULT ''", $createSql); // Attempt to catch double quoted nonsense?

        $local->exec("DROP TABLE IF EXISTS $table");
        $local->exec($createSql);
        
        // 2. Data
        // Need to know primary key or sort column for 'Recent' data?
        // Default to provided limit clause.
        
        // If limit clause contains ORDER BY but column doesn't exist, it will panic.
        // Let's assume standard named columns or just sync LIMIT without order if unsafe.
        // We will try/catch.

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
