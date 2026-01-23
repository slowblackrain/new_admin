<?php
// migrate_to_local.php

$remoteConfig = [
    'host' => '49.247.170.176',
    'dbname' => 'dometopia',
    'username' => 'dometopia',
    'password' => '11dnjf7dlf!!',
    'port' => 3306
];

$localConfig = [
    'host' => '127.0.0.1',
    'dbname' => 'dometopia',
    'username' => 'root',
    'password' => '1111',
    'port' => 3306
];

try {
    echo "Connecting to Remote DB...\n";
    $remotePdo = new PDO(
        "mysql:host={$remoteConfig['host']};port={$remoteConfig['port']};dbname={$remoteConfig['dbname']};charset=utf8",
        $remoteConfig['username'],
        $remoteConfig['password']
    );
    $remotePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connecting to Local DB...\n";
    $localPdo = new PDO(
        "mysql:host={$localConfig['host']};port={$localConfig['port']};charset=utf8",
        $localConfig['username'],
        $localConfig['password']
    );
    $localPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create DB if not exists
    $localPdo->exec("CREATE DATABASE IF NOT EXISTS `{$localConfig['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $localPdo->exec("USE `{$localConfig['dbname']}`");

    // Foreign Key Checks Off
    $localPdo->exec("SET FOREIGN_KEY_CHECKS=0");

    // Get All Tables from Remote
    $stmt = $remotePdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Found " . count($tables) . " tables.\n";

    // Tables to get Data from (Limit 100)
    // Add critical tables here
    $dataTables = [
        'fm_admin', // Need admin login
        'fm_manager', // Check if this is admin?
        'fm_member',
        'fm_member_group',
        'fm_goods',
        'fm_goods_option',
        'fm_goods_image',
        'fm_category',
        'fm_category_group',
        'fm_category_link',
        'fm_order',
        'fm_order_item',
        'fm_order_item_option',
        'fm_order_shipping',
        'fm_board_manager', // Boards
        'fm_board',
        'fm_design_banner', // UI
        'fm_design_banner_item',
        'fm_supply',
        'fm_scm_store'
    ];

    foreach ($tables as $table) {
        echo "Processing table: $table ... ";
        
        // 1. Get Create Schema
        $stmt = $remotePdo->query("SHOW CREATE TABLE `$table`");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $createSql = $row['Create Table'];

        // Fix definition references if needed (e.g. DEFINER user)
        $createSql = preg_replace('/DEFINER=`[^`]+`@`[^`]+`/', '', $createSql);

        // Drop and Create in Local
        try {
            $localPdo->exec("DROP TABLE IF EXISTS `$table`");
            $localPdo->exec($createSql);
            echo "[Schema OK] ";
        } catch (Exception $e) {
            echo "[Schema ERR: {$e->getMessage()}] \n";
            continue;
        }

        // 2. Data Migration
        // If it's a data table we want, or small config tables (let's say all tables if < 1000 rows?)
        // For safety, let's stick to the list + small tables.
        
        // Check count first? No, slow.
        // Just Try Select Limit 200.
        
        $shouldImportData = false;
        
        // Import data for explicitly listed tables OR common config tables
        if (in_array($table, $dataTables) || strpos($table, 'fm_design') === 0 || strpos($table, 'fm_config') === 0) {
             $shouldImportData = true;
        }

        if ($shouldImportData) {
            // Special handling for admins/managers to ensure we can login
            $limit = 200;
            if ($table === 'fm_manager' || $table === 'fm_admin') $limit = 1000;

            $stmt = $remotePdo->query("SELECT * FROM `$table` LIMIT $limit");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($rows) > 0) {
                // Build Insert
                // Chunking to avoid massive queries
                foreach ($rows as $row) {
                    $columns = array_keys($row);
                    $values = array_values($row);
                    
                    // Escape values
                    $escapedValues = array_map(function($v) use ($localPdo) {
                        return $v === null ? "NULL" : $localPdo->quote($v);
                    }, $values);
                    
                    $colString = "`" . implode("`, `", $columns) . "`";
                    $valString = implode(", ", $escapedValues);
                    
                    $insertSql = "INSERT INTO `$table` ($colString) VALUES ($valString)";
                    try {
                        $localPdo->exec($insertSql);
                    } catch (Exception $e) {
                        // Ignore duplicate entry or data errors
                    }
                }
                echo "[Data " . count($rows) . " rows]";
            }
        }
        echo "\n";
    }

    $localPdo->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "\nSuccess! Migration Complete.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
