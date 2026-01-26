<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Scm\ScmOrderController;

// 1. Setup Data
// 0. Get/Setup Admin Goods (Provider 1)
$admin_goods = DB::table('fm_goods')->where('provider_seq', 1)->first();
if (!$admin_goods) {
   die("No Admin Goods found. Need at least one goods with provider_seq=1.");
}
$admin_goods_id = $admin_goods->goods_seq;
echo "Using Admin Goods: $admin_goods_id\n";

// 1. Get/Setup Agency Goods (Provider != 1)
// Find one or pick another admin good and force update it (dangerous? no, it's dev env)
// Better: pick a different goods_seq than admin
$agency_goods = DB::table('fm_goods')->where('goods_seq', '!=', $admin_goods_id)->first();
if (!$agency_goods) {
    die("No second goods found.");
}
$agency_goods_id = $agency_goods->goods_seq;
$provider_id = 999;
// Temporarily make it agency
DB::table('fm_goods')->where('goods_seq', $agency_goods_id)->update(['provider_seq' => $provider_id]);
echo "Using Agency Goods: $agency_goods_id (Provider set to $provider_id)\n";

// Setup Provider Cash
DB::table('fm_member')->updateOrInsert(['member_seq' => $provider_id], ['cash' => 10000, 'userid' => 'test_provider', 'group_seq' => 1, 'group_set_date' => now(), 'nickname' => 'Provider', 'regist_date' => now(), 'update_date' => now(), 'lastlogin_date' => now()]);

// Supply Price setup (Default Info)
DB::table('fm_scm_order_defaultinfo')->insert([
    'goods_seq' => $agency_goods_id,
    'supply_price' => 500, // 500 won
    'main_trade_type' => 'Y',
    'trader_seq' => 1
]);

// 2. Mock Request
$request = new Request();
$request->merge([
    'orders' => [
        $admin_goods_id => 10,  // Should succeed
        $agency_goods_id => 2   // Cost 1000. Should succeed and deduct 1000.
    ]
]);

echo "Initial Cash: " . DB::table('fm_member')->where('member_seq', $provider_id)->value('cash') . "\n";

// 3. Run Controller
$controller = new ScmOrderController();
try {
    $response = $controller->create_auto_order($request);
    echo "Controller Executed.\n";
} catch (\Exception $e) {
    echo "Controller Error: " . $e->getMessage() . "\n";
}

// 4. Verify
$offer_admin = DB::table('fm_offer')->where('goods_seq', $admin_goods_id)->count();
$offer_agency = DB::table('fm_offer')->where('goods_seq', $agency_goods_id)->count();
$final_cash = DB::table('fm_member')->where('member_seq', $provider_id)->value('cash');

echo "Admin Offer Count: $offer_admin (Expected 1)\n";
echo "Agency Offer Count: $offer_agency (Expected 1)\n";
echo "Final Cash: $final_cash (Expected 9000)\n";

if ($offer_admin == 1 && $offer_agency == 1 && $final_cash == 9000) {
    echo "[PASS] Order Creation Logic Verified.\n";
} else {
    echo "[FAIL] Verification Failed.\n";
}
