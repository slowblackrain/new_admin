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

$tablesToFix = ['fm_provider', 'fm_boardmanager'];

foreach ($tablesToFix as $table) {
    echo "Fixing $table... ";
    
    try {
        // 1. Structure
        $stmt = $remotePdo->query("SHOW CREATE TABLE $table");
        $createSql = $stmt->fetchColumn(1);
        
        // --- CLEANUP ---
        // 1. Fix Zero Dates
        // Replace DEFAULT '0000-00-00 00:00:00' with valid date
        $createSql = str_replace("DEFAULT '0000-00-00 00:00:00'", "DEFAULT '1970-01-01 00:00:00'", $createSql);
        
        // 2. Fix Auto Increment
        $createSql = preg_replace('/AUTO_INCREMENT=\d+/', '', $createSql);
        
        // 3. Fix Corrupted Defaults (The strange block character)
        // We match DEFAULT '...' where '...' contains non-ascii or specific chars?
        // Easier: Remove defaults for specific known bad columns or replace global "weird" defaults?
        // Let's replace the specific block char found in debug output if possible?
        // The output showed ''. But in PHP string it might differ.
        // Safer approach: Regex replace DEFAULT '.*?' if it looks weird? No, risky.
        // Let's try to strip `varbinary` or similar? No.
        // Let's just try replacing the Zero Dates first. That's the most common 1067 error.
        
        // If 'info_type' has weird default, let's manually patch commonly known columns.
        $createSql = preg_replace("/DEFAULT ''/", "DEFAULT ''", $createSql); // Try exact char
        $createSql = preg_replace("/DEFAULT '?'/", "DEFAULT ''", $createSql); 
        
        // Specific Column Fixes (Regex)
        // info_type varchar(10) DEFAULT '...'
        // write_admin varchar(50) NOT NULL DEFAULT '...'
        
        // Aggressive fix: If DEFAULT is failing, we can try to create without defaults for those cols?
        // For now, let's try EXEC.
        
        $localPdo->exec("DROP TABLE IF EXISTS $table");
        $localPdo->exec($createSql);
        echo "Structure OK. ";
        
        // 2. Data
        $stmt = $remotePdo->query("SELECT * FROM $table"); // Fetch All
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
                    if ($v === '0000-00-00 00:00:00') {
                        $row[$k] = '1970-01-01 00:00:00';
                    }
                }
                $insertStmt->execute(array_values($row));
            }
            echo "Inserted " . count($rows) . " rows.\n";
        } else {
            echo "Empty.\n";
        }

    } catch (Exception $e) {
        echo "Fail: " . $e->getMessage() . "\n"; // Continue to next table
    }
}
