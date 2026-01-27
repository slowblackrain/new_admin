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
    // IMPORTANT: remote charset=utf8 to force server-side conversion from EUC-KR
    $remotePdo = new PDO("mysql:host=$remoteHost;dbname=$remoteDb;charset=utf8", $remoteUser, $remotePass);
    $remotePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Local charset=utf8mb4
    $localPdo = new PDO("mysql:host=$localHost;dbname=$localDb;charset=utf8mb4", $localUser, $localPass);
    $localPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $localPdo->exec("SET SESSION sql_mode = ''");
} catch (Exception $e) {
    die("Connection Failed: " . $e->getMessage());
}

// List of all tables we touched or need
$tables = [
    // Design
    'fm_design_display',
    'fm_design_display_item',
    'fm_design_display_tab',
    'fm_design_display_tab_item',
    'fm_design_banner',
    'fm_design_banner_item',
    'fm_config',
    
    // Category (The one user complained about)
    'fm_category',
    'fm_category_link' => 'LIMIT 5000',

    // Member & Seller
    'fm_member_group',
    'fm_provider',
    'fm_provider_charge',
    'fm_provider_person',
    'fm_member' => 'ORDER BY member_seq DESC LIMIT 2000',
    
    // Board
    'fm_boardmanager',
    'fm_boarddata' => 'LIMIT 100', // Sample data
    'fm_manager_log' => 'ORDER BY manager_log_seq DESC LIMIT 1000', // Corrected Sort Col
    
    // Order
    'fm_order' => 'ORDER BY order_seq DESC LIMIT 2000',
    'fm_order_item' => 'ORDER BY item_seq DESC LIMIT 5000',
    'fm_order_item_option' => 'LIMIT 5000',
    'fm_order_log' => 'ORDER BY log_seq DESC LIMIT 1000', // Corrected Sort Col
    
    // SCM / Accounting
    'fm_cash' => 'LIMIT 100',
    'fm_offer' => 'LIMIT 100',
];

function syncTable($remote, $local, $table, $limitClause = '') {
    echo "Syncing $table... ";
    
    try {
        // 1. Structure
        $stmt = $remote->query("SHOW CREATE TABLE $table");
        $createSql = $stmt->fetchColumn(1);
        
        // --- SCHEMA SANITIZATION (Robust) ---
        // Strip Comments
        $createSql = preg_replace("/COMMENT '(?:''|[^'])*'/", "", $createSql);
        
        // Remove Auto Increment
        $createSql = preg_replace('/AUTO_INCREMENT=\d+/', '', $createSql);
        
        // Fix Zero Dates
        $createSql = str_replace("DEFAULT '0000-00-00 00:00:00'", "DEFAULT '1970-01-01 00:00:00'", $createSql);
        $createSql = str_replace("DEFAULT '0000-00-00'", "DEFAULT '1970-01-01'", $createSql);
        
        // Fix Weird Defaults (like DEFAULT '''''')
        $createSql = preg_replace("/DEFAULT ''''''/", "DEFAULT ''", $createSql);
        
        // Handle `fm_provider` specific weirdness if any remains
        $createSql = preg_replace("/(`info_type`.*?DEFAULT\s*)'.*?'/i", "$1''", $createSql);
        $createSql = preg_replace("/(`write_admin`.*?DEFAULT\s*)'.*?'/i", "$1''", $createSql);
        $createSql = preg_replace("/(`calcu_bank`.*?DEFAULT\s*)'.*?'/i", "$1''", $createSql);

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
            
            // Sanitize Data Values
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

foreach ($tables as $key => $val) {
    if (is_int($key)) {
        syncTable($remotePdo, $localPdo, $val);
    } else {
        syncTable($remotePdo, $localPdo, $key, $val);
    }
}

echo "Done.\n";
