<?php
// c:/dometopia/new_admin/fetch_analyze_goods.php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;
use App\Models\Goods;
use App\Services\PricingService;

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

$targetSeq = 210145;

echo "--- Fetching Goods Seq: $targetSeq ---\n";

// 1. Goods
$stmt = $remotePdo->prepare("SELECT * FROM fm_goods WHERE goods_seq = ?");
$stmt->execute([$targetSeq]);
$goodsData = $stmt->fetch();

if (!$goodsData) {
    die("Goods $targetSeq not found in Remote DB.\n");
}

upsert($localPdo, 'fm_goods', $goodsData, 'goods_seq');
echo "Synced fm_goods.\n";

// 2. Options
$stmt = $remotePdo->prepare("SELECT * FROM fm_goods_option WHERE goods_seq = ?");
$stmt->execute([$targetSeq]);
$opts = $stmt->fetchAll();
$localPdo->prepare("DELETE FROM fm_goods_option WHERE goods_seq = ?")->execute([$targetSeq]);
foreach ($opts as $opt) upsert($localPdo, 'fm_goods_option', $opt, 'option_seq');
echo "Synced " . count($opts) . " options.\n";

// 3. Images (Optional but good for completeness)
$stmt = $remotePdo->prepare("SELECT * FROM fm_goods_image WHERE goods_seq = ?");
$stmt->execute([$targetSeq]);
$imgs = $stmt->fetchAll();
$localPdo->prepare("DELETE FROM fm_goods_image WHERE goods_seq = ?")->execute([$targetSeq]);
foreach ($imgs as $img) upsert($localPdo, 'fm_goods_image', $img, 'image_seq');
echo "Synced " . count($imgs) . " images.\n";

// 4. Force Visibility for Analysis
$localPdo->prepare("UPDATE fm_goods SET goods_view = 'look', goods_status = 'normal' WHERE goods_seq = ?")->execute([$targetSeq]);


// --- Analysis ---
echo "\n=== Analysis Report for Goods Seq: $targetSeq ===\n";

$product = Goods::with('option')->find($targetSeq);
if (!$product) {
    die("Failed to load product model after sync.\n");
}

$productName = $product->goods_name;
echo "Goods Name: $productName\n";

$pricingService = new PricingService();
$pricing = $pricingService->getProductPricingInfo($product);

echo "\n[Pricing Analysis]\n";
echo "1. Retail Price (Somae): " . number_format($pricing['somae_price']) . " Won\n";
echo "2. Wholesale Price (Domae): " . number_format($pricing['domae_price']) . " Won\n";
echo "3. Wholesale Discount Price (50+): " . number_format($pricing['domae_discount_price']) . " Won (Discount: " . number_format($product->fifty_discount) . ")\n";
echo "4. Import Price (100+): " . number_format($pricing['suip_price']) . " Won (Discount: " . number_format($product->hundred_discount) . ")\n";

echo "\n[Detailed Data]\n";
echo "Consumer Price (DB): " . $product->option->first()->consumer_price . "\n";
echo "Price (DB): " . $product->option->first()->price . "\n";
echo "Fifty Discount (DB): " . $product->fifty_discount . " (Min Qty: " . $product->fifty_discount_ea . ")\n";
echo "Hundred Discount (DB): " . $product->hundred_discount . " (Min Qty: " . $product->hundred_discount_ea . ")\n";
echo "Member Discount (DB): " . $product->mtype_discount . "\n";

