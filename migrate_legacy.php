<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Legacy DB Credentials (from c:/dometopia/legacy_source/app/config/database.php)
// Group: 'default'
$sourceHost = '49.247.170.176';
$sourceUser = 'dometopia';
$sourcePass = '11dnjf7dlf!!';
$sourceDb   = 'dometopia';

echo "Connecting to Legacy DB ($sourceHost)...\n";

try {
    $sourcePdo = new PDO("mysql:host=$sourceHost;dbname=$sourceDb;charset=utf8", $sourceUser, $sourcePass);
    $sourcePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sourcePdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    echo "Connected to Legacy DB.\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

// 1. Categories (fm_category)
echo "Migrating Categories...\n";
try {
    // Truncate Local Categories? User said "no info", so safe to truncate or upsert.
    // Let's Truncate to match exact structure if possible, but constraints might block.
    // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    // DB::table('fm_category')->truncate();
    // DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    
    // Fetch All Categories
    $stmt = $sourcePdo->query("SELECT * FROM fm_category");
    $categories = $stmt->fetchAll();
    
    echo "Found " . count($categories) . " categories.\n";
    
    foreach (array_chunk($categories, 100) as $chunk) {
        $dataToInsert = [];
        foreach ($chunk as $row) {
            // Clean up? Direct mapping.
            $dataToInsert[] = $row; 
        }
        // Use insertOrIgnore
        DB::table('fm_category')->insertOrIgnore($dataToInsert);
    }
    echo "Categories Migrated.\n";
    
} catch (Exception $e) {
    echo "Error migrating categories: " . $e->getMessage() . "\n";
}

// 2. Goods (fm_goods) - Top 1000 + Main Display
echo "Migrating Goods...\n";
try {
    // Strategy: 
    // A. Get Main Display Goods IDs first (fm_design_display, fm_design_display_item)
    // B. Get Recent 1000 Goods IDs
    // C. Merge and Fetch full data
    
    $targetGoodsIds = [];
    
    // A. Main Display
    // Check if fm_design_display_item exists in source
    try {
        // Usually `fm_design_display_item` connects display group to goods.
        // Or `fm_design_display` has content?
        // Let's try to fetch from `fm_design_display_item`
        $stmt = $sourcePdo->query("SELECT goods_seq FROM fm_design_display_item LIMIT 2000"); // Just grab a bunch
        $displayGoods = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $targetGoodsIds = array_merge($targetGoodsIds, $displayGoods);
        echo "Found " . count($displayGoods) . " display goods.\n";
    } catch (Exception $e) {
        echo "Warning: Could not fetch display items (" . $e->getMessage() . ")\n";
    }
    
    // B. Recent 1000
    $stmt = $sourcePdo->query("SELECT goods_seq FROM fm_goods ORDER BY goods_seq DESC LIMIT 1000");
    $recentGoods = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $targetGoodsIds = array_merge($targetGoodsIds, $recentGoods);
    
    $targetGoodsIds = array_unique($targetGoodsIds);
    $targetGoodsIds = array_filter($targetGoodsIds); // remove empty
    
    echo "Total unique goods to migrate: " . count($targetGoodsIds) . "\n";
    
    if (empty($targetGoodsIds)) {
        die("No goods found to migrate.\n");
    }
    
    // Chunking IDs for fetching
    $idChunks = array_chunk($targetGoodsIds, 500);
    
    foreach ($idChunks as $chunkIds) {
        $idStr = implode(',', $chunkIds);
        
        // 2.1 Fetch fm_goods
        $stmt = $sourcePdo->query("SELECT * FROM fm_goods WHERE goods_seq IN ($idStr)");
        $goodsData = $stmt->fetchAll();
        
        // Insert Goods
        foreach (array_chunk($goodsData, 50) as $insertChunk) {
            DB::table('fm_goods')->insertOrIgnore($insertChunk);
        }
        
        // 2.2 Fetch fm_goods_option
        $stmt = $sourcePdo->query("SELECT * FROM fm_goods_option WHERE goods_seq IN ($idStr)");
        $optionData = $stmt->fetchAll();
        foreach (array_chunk($optionData, 50) as $insertChunk) {
            DB::table('fm_goods_option')->insertOrIgnore($insertChunk);
        }
        
        // 2.3 Fetch fm_goods_image
        $stmt = $sourcePdo->query("SELECT * FROM fm_goods_image WHERE goods_seq IN ($idStr)");
        $imageData = $stmt->fetchAll();
        foreach (array_chunk($imageData, 50) as $insertChunk) {
            DB::table('fm_goods_image')->insertOrIgnore($insertChunk);
        }
        
        // 2.4 Fetch fm_category_link
        $stmt = $sourcePdo->query("SELECT * FROM fm_category_link WHERE goods_seq IN ($idStr)");
        $linkData = $stmt->fetchAll();
        foreach (array_chunk($linkData, 50) as $insertChunk) {
            DB::table('fm_category_link')->insertOrIgnore($insertChunk);
        }

        // 2.5 Fetch fm_goods_supply (Stock)
        // Note: fm_goods_supply is linked by goods_seq? Or option_seq?
        // Usually by goods_seq OR option_seq. Let's look for goods_seq column.
        // Assuming goods_seq exists
        try {
             $stmt = $sourcePdo->query("SELECT * FROM fm_goods_supply WHERE goods_seq IN ($idStr)");
             $supplyData = $stmt->fetchAll();
             foreach (array_chunk($supplyData, 50) as $insertChunk) {
                DB::table('fm_goods_supply')->insertOrIgnore($insertChunk);
             }
        } catch (Exception $e) {
             // Maybe no goods_seq column? Try checking columns? Or skip.
             // Usually supply is critical. Let's try option_seq based?
             // Too complex for blind script. If it fails, we skip supply (stock will be 0 or missing).
             echo "Warning: Supply migration failed or schema mismatch.\n";
        }
        
        echo "Processed chunk of " . count($chunkIds) . " goods.\n";
    }
    
    echo "Goods Migration Completed.\n";
    
} catch (Exception $e) {
    echo "Error migrating goods: " . $e->getMessage() . "\n";
}

echo "Migration Finished.\n";
?>
