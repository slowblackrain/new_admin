<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

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

$tablesToFix = ['fm_provider', 'fm_boardmanager'];

foreach ($tablesToFix as $table) {
    echo "Fixing $table... ";
    
    try {
        // 1. Structure
        $stmt = $remotePdo->query("SHOW CREATE TABLE $table");
        $createSql = $stmt->fetchColumn(1);
        
        // --- CLEANUP ---
        
        // Remove Comments (Greedy matching might be risky, use ungreedy)
        $createSql = preg_replace("/COMMENT '.*?'/s", "", $createSql);
        
        // Fix Zero Dates
        $createSql = str_replace("DEFAULT '0000-00-00 00:00:00'", "DEFAULT '1970-01-01 00:00:00'", $createSql);
        
        // Fix Auto Increment
        $createSql = preg_replace('/AUTO_INCREMENT=\d+/', '', $createSql);

        // Fix other potential date defaults like '0000-00-00' (without time)
        $createSql = str_replace("DEFAULT '0000-00-00'", "DEFAULT '1970-01-01'", $createSql);

        $localPdo->exec("DROP TABLE IF EXISTS $table");
        $localPdo->exec($createSql);
        echo "Structure OK. ";
        
        // 2. Data
        $stmt = $remotePdo->query("SELECT * FROM $table"); 
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            $cols = array_keys($rows[0]);
            $colsStr = implode(',', array_map(function($c) { return "`$c`"; }, $cols));
            $valsStr = implode(',', array_fill(0, count($cols), '?'));
            $insertSql = "INSERT INTO $table ($colsStr) VALUES ($valsStr)";
            $insertStmt = $localPdo->prepare($insertSql);
            
            foreach ($rows as $row) {
                // Sanitize Data Dates
                foreach ($row as $k => $v) {
                     if (is_string($v) && (strpos($v, '0000-00-00') !== false)) {
                         $row[$k] = str_replace('0000-00-00', '1970-01-01', $v);
                     }
                }
                $insertStmt->execute(array_values($row));
            }
            echo "Inserted " . count($rows) . " rows.\n";
        } else {
            echo "Empty.\n";
        }

    } catch (Exception $e) {
        echo "Fail: " . $e->getMessage() . "\n";
        // echo "DEBUG SQL: \n" . substr($createSql, 0, 500) . "...\n";
    }
}
