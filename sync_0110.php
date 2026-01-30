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

// Connections
$remotePdo = new PDO("mysql:host=$remoteHost;dbname=$remoteDb", $remoteUser, $remotePass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
$localPdo = new PDO("mysql:host=$localHost;dbname=$localDb", $localUser, $localPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
$localPdo->exec("SET SESSION sql_mode = ''");

function convertToUtf8($data) {
    if (is_array($data)) return array_map('convertToUtf8', $data);
    if (is_string($data)) return mb_convert_encoding($data, 'UTF-8', 'EUC-KR');
    return $data;
}

function upsert($pdo, $table, $data, $pk) {
    if (empty($data)) return;
    $data = convertToUtf8($data);
    
    // Sanitize Goods
    if ($table === 'fm_goods') {
         if (isset($data['string_price_use'])) $data['string_price_use'] = ($data['string_price_use'] == 'y' || $data['string_price_use'] == '1') ? '1' : '0';
         if (isset($data['option_use'])) $data['option_use'] = ($data['option_use'] == 'y' || $data['option_use'] == '1') ? '1' : '0';
         if (empty($data['runout_policy']) || !in_array($data['runout_policy'], ['stock','ableStock','unlimited'])) $data['runout_policy'] = 'stock';
         foreach ($data as $k => $v) {
             if (strpos($k, 'multi_discount_unit') === 0 && $v === '') $data[$k] = null;
         }
    }

    $cols = array_keys($data);
    $valsList = implode(", ", array_fill(0, count($cols), "?"));
    
    $updateParts = [];
    foreach ($cols as $col) {
        if ($col === $pk) continue;
        $updateParts[] = "`$col` = VALUES(`$col`)";
    }
    $updateClause = implode(", ", $updateParts);
    
    // BACKTICK COLUMNS
    $colsList = implode(", ", array_map(function($c) { return "`$c`"; }, $cols));
    
    $sql = "INSERT INTO $table ($colsList) VALUES ($valsList) ON DUPLICATE KEY UPDATE $updateClause";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($data));
}

// 1. Cleanup Test Data (0001)
echo "Cleaning up 0001 (Test) data...\n";
$localPdo->exec("DELETE FROM fm_category WHERE category_code LIKE '0001%'");
// We don't delete goods to avoid mass deletion if they overlap, but we remove links
$localPdo->exec("DELETE FROM fm_category_link WHERE category_code LIKE '0001%'");

// 2. Sync Category 0110 (and children)
$targetCode = '0110';
echo "Syncing Category $targetCode%...\n";
$stmt = $remotePdo->prepare("SELECT * FROM fm_category WHERE category_code LIKE ?");
$stmt->execute([$targetCode . '%']);
$cats = $stmt->fetchAll();

foreach ($cats as $cat) {
    upsert($localPdo, 'fm_category', $cat, 'category_code');
}
echo "Synced " . count($cats) . " categories.\n";

// 3. Sync 100 Goods for 0110
echo "Fetching 100 Goods for $targetCode...\n";
$stmt = $remotePdo->prepare("SELECT DISTINCT goods_seq FROM fm_category_link WHERE category_code LIKE ? ORDER BY goods_seq DESC LIMIT 100");
$stmt->execute([$targetCode . '%']);
$seqs = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($seqs as $seq) {
    // Goods
    $stmt = $remotePdo->prepare("SELECT * FROM fm_goods WHERE goods_seq = ?");
    $stmt->execute([$seq]);
    $goods = $stmt->fetch();
    if ($goods) upsert($localPdo, 'fm_goods', $goods, 'goods_seq');

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
    
    // Links (Important!)
    $stmt = $remotePdo->prepare("SELECT * FROM fm_category_link WHERE goods_seq = ?");
    $stmt->execute([$seq]);
    $links = $stmt->fetchAll();
    $localPdo->prepare("DELETE FROM fm_category_link WHERE goods_seq = ?")->execute([$seq]);
    foreach ($links as $link) upsert($localPdo, 'fm_category_link', $link, 'link_seq');
}

// 4. Force Visibility for verified items
if (!empty($seqs)) {
    $inClause = implode(',', array_fill(0, count($seqs), '?'));
    $localPdo->prepare("UPDATE fm_goods SET goods_view = 'look', goods_status = 'normal' WHERE goods_seq IN ($inClause)")->execute($seqs);
}

echo "Sync Complete for 0110.\n";
