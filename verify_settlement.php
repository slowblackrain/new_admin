<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\Scm\ScmSettlementController;
use Illuminate\Http\Request;

echo "Checking for Stocked Offers (Step 11)...\n";
$stockedCount = DB::table('fm_offer')->where('step', 11)->count();
echo "Stocked Offers: $stockedCount\n";

if ($stockedCount == 0) {
    echo "Creating a Mock Stocked Offer...\n";
    // Need a trader first
    $trader = DB::table('fm_scm_trader')->first();
    if (!$trader) {
        $traderId = DB::table('fm_scm_trader')->insertGetId([
            'trader_name' => 'Test Trader',
            'trader_id' => 'T001',
            'trader_pass' => '1234', // valid pass
            'regist_date' => now(),
            'modify_date' => now()
        ]);
    } else {
        $traderId = $trader->trader_seq;
    }

    DB::table('fm_offer')->insert([
        'offer_cn' => 'TEST-01',
        'trader_seq' => $traderId,
        'step' => 11, // Completed
        'stock_date' => date('Y-m-d H:i:s'), // Current datetime
        'ord_tot_price' => '500000',
        'ord_stock' => 10,
        'goods_seq' => 1, // Required
        'regist_date' => now(),
        'update_date' => now(),
        // Required enums/defaults
        'favorite_chk' => 'none',
        'trader_chk' => 'none',
        'inday' => 0
    ]);
    echo "Created Mock Offer.\n";
}

echo "Running Controller Logic...\n";
$controller = new ScmSettlementController();
$request = Request::create('/admin/scm_settlement/trader_monthly', 'GET', ['year' => date('Y'), 'month' => date('m')]);

// Capture View Data
// Note: In CLI, returning view() just returns View object, we can inspect it.
$view = $controller->trader_monthly($request);
$data = $view->getData();

print_r($data['purchases']);
echo "\nLogic Verification Done.\n";
