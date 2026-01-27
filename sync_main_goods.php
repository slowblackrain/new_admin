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

// --- Connections ---
echo "Connecting to Remote ($remoteHost)...\n";
try {
    $remotePdo = new PDO("mysql:host=$remoteHost;dbname=$remoteDb", $remoteUser, $remotePass);
    $remotePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $remotePdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Remote Connection Failed: " . $e->getMessage());
}

echo "Connecting to Local ($localHost)...\n";
try {
    $localPdo = new PDO("mysql:host=$localHost;dbname=$localDb", $localUser, $localPass);
    $localPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $localPdo->exec("SET SESSION sql_mode = ''"); // Disable strict mode to allow truncation/defaults
} catch (Exception $e) {
    die("Local Connection Failed: " . $e->getMessage());
}

// --- 1. Identify Target Goods ---
$targetDisplaySeqs = [7150, 7152, 7160, 101810];
$targetGoodsSeqs = [182128]; // Explicit adding the one causing issues

echo "Fetching Target Goods IDs from Remote...\n";

// Get from Display Items
foreach ($targetDisplaySeqs as $dSeq) {
    $stmt = $remotePdo->prepare("SELECT goods_seq FROM fm_design_display_tab_item WHERE display_seq = ? AND display_tab_index = 0");
    $stmt->execute([$dSeq]);
    $seqs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $targetGoodsSeqs = array_merge($targetGoodsSeqs, $seqs);
    echo "  Display $dSeq: Found " . count($seqs) . " items.\n";
}

$targetGoodsSeqs = array_unique($targetGoodsSeqs);
echo "Total Unique Goods to Sync: " . count($targetGoodsSeqs) . "\n";

// --- 2. Sync Logic ---

// --- Helper: Convert Encodings ---
function convertToUtf8($data) {
    if (is_array($data)) {
        return array_map('convertToUtf8', $data);
    }
    if (is_string($data)) {
        return mb_convert_encoding($data, 'UTF-8', 'EUC-KR');
    }
    return $data;
}

function sanitizeGoodsData($goods) {
    // Debug
    if (in_array($goods['goods_seq'], [162009, 180107, 182128])) {
        echo "\n[DEBUG {$goods['goods_seq']}] string_price_use: '{$goods['string_price_use']}', option_use: '{$goods['option_use']}', option_view_type: '{$goods['option_view_type']}'";
    }

    // Map legacy 'y'/'n' to '1'/'0' for known Enums
    $mapYn = function($val) {
        $v = strtolower((string)$val);
        if ($v === 'y') return '1';
        if ($v === 'n') return '0';
        if ($v === '1') return '1';
        if ($v === '0') return '0';
        return '0'; // Default
    };

    if (isset($goods['string_price_use'])) $goods['string_price_use'] = $mapYn($goods['string_price_use']);
    if (isset($goods['multi_discount_use'])) $goods['multi_discount_use'] = $mapYn($goods['multi_discount_use']);
    if (isset($goods['option_use'])) $goods['option_use'] = $mapYn($goods['option_use']);
    
    // multi_discount_unit*: enum('won','percent') (Null: YES)
    foreach ($goods as $key => $val) {
        if (strpos($key, 'multi_discount_unit') === 0) {
            if ($val === '') {
                $goods[$key] = null;
            }
        }
    }
    
    // runout_policy: enum('stock','ableStock','unlimited')
    // If empty, default to 'stock' (or check if null allowed, but better safe)
    if (empty($goods['runout_policy'])) {
        $goods['runout_policy'] = 'stock';
    } else {
        // Validate against allowed
        $allowed = ['stock','ableStock','unlimited'];
        if (!in_array($goods['runout_policy'], $allowed)) {
            $goods['runout_policy'] = 'stock'; 
        }
    }

    return $goods;
}

function upsert($pdo, $table, $data, $pk) {
    if (empty($data)) return;
    
    // CONVERT DATA HERE
    $data = convertToUtf8($data);
    
    // SANITIZE IF GOODS TABLE
    if ($table === 'fm_goods') {
        $data = sanitizeGoodsData($data);
    }

    $cols = array_keys($data);
    $colsList = implode(", ", $cols);
    $valsList = implode(", ", array_fill(0, count($cols), "?"));
    
    $updateParts = [];
    foreach ($cols as $col) {
        if ($col === $pk) continue;
        $updateParts[] = "$col = VALUES($col)";
    }
    $updateClause = implode(", ", $updateParts);
    
    $sql = "INSERT INTO $table ($colsList) VALUES ($valsList) ON DUPLICATE KEY UPDATE $updateClause";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($data));
}

foreach ($targetGoodsSeqs as $seq) {
    echo "Syncing Goods ID $seq... ";
    
    // Fetch Goods
    $stmt = $remotePdo->prepare("SELECT * FROM fm_goods WHERE goods_seq = ?");
    $stmt->execute([$seq]);
    $goods = $stmt->fetch();
    
    if (!$goods) {
        echo "NOT FOUND in Remote.\n";
        continue;
    }
    
    // Upsert Goods
    try {
        upsert($localPdo, 'fm_goods', $goods, 'goods_seq');
        echo "Goods OK. ";
    } catch (Exception $e) {
        echo "Goods Fail: " . $e->getMessage() . "\n";
        continue;
    }
    
    // Fetch & Sync Options
    $stmt = $remotePdo->prepare("SELECT * FROM fm_goods_option WHERE goods_seq = ?");
    $stmt->execute([$seq]);
    $options = $stmt->fetchAll();
    
    $localPdo->prepare("DELETE FROM fm_goods_option WHERE goods_seq = ?")->execute([$seq]);
    foreach ($options as $opt) {
        upsert($localPdo, 'fm_goods_option', $opt, 'option_seq');
    }
    echo "Opts: " . count($options) . ". ";

    // Fetch & Sync Images
    $stmt = $remotePdo->prepare("SELECT * FROM fm_goods_image WHERE goods_seq = ?");
    $stmt->execute([$seq]);
    $images = $stmt->fetchAll();

    $localPdo->prepare("DELETE FROM fm_goods_image WHERE goods_seq = ?")->execute([$seq]);
    foreach ($images as $img) {
        upsert($localPdo, 'fm_goods_image', $img, 'image_seq');
    }
    echo "Imgs: " . count($images) . ".\n";
}

echo "Sync Complete!\n";
