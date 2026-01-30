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
    $localPdo->exec("SET SESSION sql_mode = ''");
} catch (Exception $e) {
    die("Local Connection Failed: " . $e->getMessage());
}

// --- Helper Functions ---
function convertToUtf8($data) {
    if (is_array($data)) return array_map('convertToUtf8', $data);
    if (is_string($data)) return mb_convert_encoding($data, 'UTF-8', 'EUC-KR');
    return $data;
}

function upsert($pdo, $table, $data, $pk) {
    if (empty($data)) return;
    $data = convertToUtf8($data);
    
    // Simple sanitization for Goods table enum strictness
    if ($table === 'fm_goods') {
         if (isset($data['string_price_use'])) $data['string_price_use'] = ($data['string_price_use'] == 'y' || $data['string_price_use'] == '1') ? '1' : '0';
         if (isset($data['option_use'])) $data['option_use'] = ($data['option_use'] == 'y' || $data['option_use'] == '1') ? '1' : '0';
         if (empty($data['runout_policy']) || !in_array($data['runout_policy'], ['stock','ableStock','unlimited'])) $data['runout_policy'] = 'stock';
         
         // Remove enum fields that might be empty string but need NULL
         foreach ($data as $k => $v) {
             if (strpos($k, 'multi_discount_unit') === 0 && $v === '') $data[$k] = null;
         }
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

// --- 0. Cleanup Test Data ---
echo "Cleaning up [TEST] items...\n";
$localPdo->exec("DELETE FROM fm_goods WHERE goods_code LIKE 'TEST-%'");
$localPdo->exec("DELETE FROM fm_category WHERE category_code LIKE '0001%' AND title LIKE 'Test Category%'");
$localPdo->exec("DELETE FROM fm_category WHERE category_code LIKE '0001%' AND title LIKE 'Sub Category%'");

// --- 1. Sync Categories (0001%) ---
$targetCode = '0001';
echo "Syncing Categories ($targetCode%)...\n";
$stmt = $remotePdo->prepare("SELECT * FROM fm_category WHERE category_code LIKE ?");
$stmt->execute([$targetCode . '%']);
$cats = $stmt->fetchAll();

foreach ($cats as $cat) {
    upsert($localPdo, 'fm_category', $cat, 'category_code');
}
echo "Synced " . count($cats) . " categories.\n";

// --- 2. Fetch Target Goods Seqs ---
echo "Fetching 100 Goods Seqs for Category $targetCode...\n";
// Get links first
$stmt = $remotePdo->prepare("SELECT DISTINCT goods_seq FROM fm_category_link WHERE category_code LIKE ? ORDER BY goods_seq DESC LIMIT 100");
$stmt->execute([$targetCode . '%']);
$targetGoodsSeqs = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "Found " . count($targetGoodsSeqs) . " goods to sync.\n";

// --- 3. Sync Goods & Related ---
foreach ($targetGoodsSeqs as $seq) {
    // Goods
    $stmt = $remotePdo->prepare("SELECT * FROM fm_goods WHERE goods_seq = ?");
    $stmt->execute([$seq]);
    $goods = $stmt->fetch();
    if ($goods) {
        try {
            upsert($localPdo, 'fm_goods', $goods, 'goods_seq');
        } catch (Exception $e) {
            echo "Failed Goods $seq: " . $e->getMessage() . "\n";
            continue;
        }
    } else {
        continue;
    }

    // Options
    $stmt = $remotePdo->prepare("SELECT * FROM fm_goods_option WHERE goods_seq = ?");
    $stmt->execute([$seq]);
    $opts = $stmt->fetchAll();
    $localPdo->prepare("DELETE FROM fm_goods_option WHERE goods_seq = ?")->execute([$seq]);
    foreach ($opts as $opt) upsert($localPdo, 'fm_goods_option', $opt, 'option_seq');

    // Images
    $stmt = $remotePdo->prepare("SELECT * FROM fm_goods_image WHERE goods_seq = ?");
    $stmt->execute([$seq]);
    $imgs = $stmt->fetchAll();
    $localPdo->prepare("DELETE FROM fm_goods_image WHERE goods_seq = ?")->execute([$seq]);
    foreach ($imgs as $img) upsert($localPdo, 'fm_goods_image', $img, 'image_seq');

    // Category Links (Important for catalog visibility)
    $stmt = $remotePdo->prepare("SELECT * FROM fm_category_link WHERE goods_seq = ?");
    $stmt->execute([$seq]);
    $links = $stmt->fetchAll();
    $localPdo->prepare("DELETE FROM fm_category_link WHERE goods_seq = ?")->execute([$seq]);
    foreach ($links as $link) upsert($localPdo, 'fm_category_link', $link, 'link_seq');
}

// --- 4. Repair/Ensure Category 0001 Exists Locally ---
echo "Repairing local Category 0001...\n";
$localPdo->exec("INSERT IGNORE INTO fm_category (category_code, title, parent_id, level, position, hide, hide_in_navigation) VALUES ('0001', 'Test Category (Synced)', 0, 1, 1, '0', '0')");

// --- 5. Force Visibility for Synced Items (for verification) ---
echo "Forcing visibility on synced items...\n";
if (!empty($targetGoodsSeqs)) {
    $inClause = implode(',', array_fill(0, count($targetGoodsSeqs), '?'));
    $localPdo->prepare("UPDATE fm_goods SET goods_view = 'look', goods_status = 'normal' WHERE goods_seq IN ($inClause)")->execute($targetGoodsSeqs);
}

echo "Sync Complete.\n";
