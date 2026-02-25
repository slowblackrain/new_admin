<?php
// exact_search_remote_db.php
try {
    $remote = new PDO("mysql:host=49.247.170.176;dbname=dometopia;charset=utf8", "dometopia", "11dnjf7dlf!!");
    $remote->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Explicit user query
    $stmt = $remote->query("SELECT * FROM fm_config WHERE groupcd = 'toss'");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== TOSS CONFIG (Remote DB) ===\n";
    if (count($rows) > 0) {
        print_r($rows);
    } else {
        echo "No records found for groupcd='toss'.\n";
    }

    $stmt2 = $remote->query("SELECT * FROM fm_config WHERE groupcd = 'cker'");
    $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== CKER CONFIG (Remote DB) ===\n";
    if (count($rows2) > 0) {
        print_r($rows2);
    } else {
        echo "No records found for groupcd='cker'.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
