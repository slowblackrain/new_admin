<?php
// explore_remote_db.php
try {
    $remote = new PDO("mysql:host=49.247.170.176;dbname=dometopia;charset=utf8", "dometopia", "11dnjf7dlf!!");
    $remote->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check tables 
    $stmt = $remote->query("SHOW TABLES LIKE '%setting%'");
    $settings = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Settings tables: " . implode(", ", $settings) . "\n";
    
    $stmt = $remote->query("SHOW TABLES LIKE '%config%'");
    $configs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Config tables: " . implode(", ", $configs) . "\n";
    
    // Check if fm_setting exists
    if (in_array('fm_setting', $settings)) {
        $stmt = $remote->query("SELECT * FROM fm_setting WHERE name LIKE '%toss%' OR group LIKE '%toss%' LIMIT 5");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "fm_setting matches for 'toss':\n";
        print_r($rows);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
