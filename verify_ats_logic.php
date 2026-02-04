<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Goods;
use App\Services\Agency\AgencyProductService;
use App\Services\Agency\AgencyPriceCalculator;

echo "\n[ATS Validation] Starting...\n";

try {
    DB::beginTransaction();

    // 1. Setup Reseller User
    $resellerId = 'test_reseller_' . rand(1000,9999);
    $memberSeq = DB::table('fm_member')->insertGetId([
        'userid' => $resellerId,
        'user_name' => 'Reseller',
        'email' => 'reseller@test.com',
        'cellphone' => '010-1234-5678',
        'status' => 'active',
        'regist_date' => now(),
        'provider_YN' => 'Y' // Must be provider? Service checks fm_provider table
    ]);
    
    // Create Provider entry for this member
    $providerSeq = DB::table('fm_provider')->insertGetId([
        'provider_id' => $resellerId,
        'provider_name' => 'TestProvider',
        'userid' => $resellerId, // Link by userid
        'regdate' => now()
    ]);
    
    echo " -> Created Reseller: $resellerId (Member: $memberSeq, Provider: $providerSeq)\n";

    // 2. Setup Source ATS Product
    // Category 0159... implies ATS. But service assumes we passed the correct ID.
    // OrderController has the check logic.
    // We create a product that LOOKS like an ATS source item (Normal scode, controlled by admin/provider 1)
    $sourceScode = 'ATS' . rand(10000,99999);
    $sourceSeq = DB::table('fm_goods')->insertGetId([
        'goods_name' => 'ATS Source Item',
        'goods_code' => rand(100000,999999), // int code
        'goods_scode' => $sourceScode,
        'goods_view' => 'look',
        'goods_status' => 'normal',
        'provider_seq' => 1, // Admin or Main Provider
        'regist_date' => now(),
        'update_date' => now()
    ]);
    
    // Insert Option
    $optSeq = DB::table('fm_goods_option')->insertGetId([
        'goods_seq' => $sourceSeq,
        'price' => 10000,
        'consumer_price' => 12000,
        'provider_price' => 8000
    ]);
    
    // Insert Supply (Required for service to read supply_price)
    DB::table('fm_goods_supply')->insert([
        'goods_seq' => $sourceSeq,
        'option_seq' => $optSeq,
        'supply_price' => 8000,
        'stock' => 100,
        'total_stock' => 100
    ]);
    
    // Insert Image
    DB::table('fm_goods_image')->insert([
        'goods_seq' => $sourceSeq,
        'image_type' => 'list1',
        'image' => '/data/test.jpg',
        'cut_number' => 1
    ]);
    
    // Insert Category Link (ATS Category)
    DB::table('fm_category_link')->insert([
        'goods_seq' => $sourceSeq,
        'category_code' => '01590001', // Example ATS Category
        'link' => 1
    ]);

    echo " -> Created Source Product: #$sourceSeq ($sourceScode)\n";

    // 3. Test Service Logic
    $calc = new AgencyPriceCalculator();
    $service = new AgencyProductService($calc);
    
    echo " -> Calling duplicateProduct()...\n";
    $newGoods = $service->duplicateProduct($sourceSeq, $memberSeq);

    // 4. Verification
    echo " -> New Goods Created: #{$newGoods->goods_seq}\n";
    echo "    SCODE: {$newGoods->goods_scode}\n";
    echo "    Orign: {$newGoods->old_goods_seq}\n";
    echo "    Provider: {$newGoods->provider_seq}\n";
    
    // Assert Scode starts with GT
    if (strpos($newGoods->goods_scode, 'GT') !== 0) {
        throw new Exception("FAIL: New Scode does not start with GT ({$newGoods->goods_scode})");
    }
    
    // Assert Provider matches Reseller
    if ($newGoods->provider_seq != $providerSeq) {
        throw new Exception("FAIL: Provider Seq mismatch (Expected $providerSeq, Got {$newGoods->provider_seq})");
    }
    
    // Assert Source Suspended
    $sourceReload = Goods::find($sourceSeq);
    if ($sourceReload->goods_view != 'notLook' || $sourceReload->goods_status != 'unsold') {
        throw new Exception("FAIL: Source Product was not suspended (View: {$sourceReload->goods_view})");
    }
    echo " -> PASS: Source Product Suspended.\n";

    // Assert Options Cloned
    $optCount = DB::table('fm_goods_option')->where('goods_seq', $newGoods->goods_seq)->count();
    if ($optCount == 0) throw new Exception("FAIL: Options not cloned");
    echo " -> PASS: Options Cloned.\n";
    
    // Assert Images Cloned
    $imgCount = DB::table('fm_goods_image')->where('goods_seq', $newGoods->goods_seq)->count();
    if ($imgCount == 0) throw new Exception("FAIL: Images not cloned");
    echo " -> PASS: Images Cloned.\n";

    DB::rollBack();
    echo "\n[SUCCESS] ATS Auto-Copy Logic Verified.\n";

} catch (Exception $e) {
    DB::rollBack();
    echo "\n[ERROR] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
