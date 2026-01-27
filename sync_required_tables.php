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

// --- Tables to Sync ---
$tablesToSync = [
    // Main Page Design
    'fm_design_display',
    'fm_design_display_item',
    'fm_design_display_tab',
    'fm_design_display_tab_item',
    'fm_design_banner',
    'fm_design_banner_item',
    
    // Config & Categories
    'fm_config',
    'fm_category',
    'fm_category_link',
    
    // Member & Seller
    'fm_member_group',
    'fm_provider',
    'fm_provider_charge',
    'fm_provider_person',
    
    // Board
    'fm_boardmanager',
];

// Tables to Sync Structure + Recent Data
$tablesPartial = [
    'fm_boarddata' => 'LIMIT 50'
];

function syncTable($remote, $local, $table, $limitClause = '') {
    echo "Syncing $table... ";
    
    // 1. Check/Create Table
    try {
        $stmt = $remote->query("SHOW CREATE TABLE $table");
        $createSql = $stmt->fetchColumn(1);
        
        // Remove AUTO_INCREMENT or specific table options if needed, but mostly fine
        $createSql = preg_replace('/AUTO_INCREMENT=\d+/', '', $createSql);
        
        $local->exec("DROP TABLE IF EXISTS $table");
        $local->exec($createSql);
    } catch (Exception $e) {
        echo "Structure Fail: " . $e->getMessage() . "\n";
        return;
    }

    // 2. Fetch Data
    try {
        $stmt = $remote->query("SELECT * FROM $table $limitClause");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($rows)) {
            echo "Empty.\n";
            return;
        }

        // 3. Insert Data
        $cols = array_keys($rows[0]);
        $colsStr = implode(',', array_map(function($c) { return "`$c`"; }, $cols));
        $valsStr = implode(',', array_fill(0, count($cols), '?'));
        
        $insertSql = "INSERT INTO $table ($colsStr) VALUES ($valsStr)";
        $insertStmt = $local->prepare($insertSql);
        
        foreach ($rows as $row) {
            // Encode conversion handled naturally by PDO usually, but keeping an eye on EUC-KR
            // If needed, we can map values here.
            // Assuming local DB is UTF8mb4 and remote is EUC-KR, we might need conversion if connection charset isn't set.
            // Let's rely on basic fetch/insert first.
            $insertStmt->execute(array_values($row));
        }
        
        echo "Inserted " . count($rows) . " rows.\n";
    } catch (Exception $e) {
        echo "Data Fail: " . $e->getMessage() . "\n";
    }
}

// Run Full Syncs
foreach ($tablesToSync as $table) {
    syncTable($remotePdo, $localPdo, $table);
}

// Run Partial Syncs
foreach ($tablesPartial as $table => $limit) {
    syncTable($remotePdo, $localPdo, $table, $limit);
}

echo "Done.\n";
