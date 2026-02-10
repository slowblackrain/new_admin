<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;
use App\Models\SubOption;

// 1. Setup Data
$goodsSeq = 206718;
$memberSeq = 1;
$orderSeq = (int) (date('YmdHis') . rand(10, 99));

echo "Testing Order Submission for Goods {$goodsSeq} (OrderSeq: {$orderSeq})...\n";

$subOpt = SubOption::where('goods_seq', $goodsSeq)->where('suboption', '중국1도')->first();
if (!$subOpt) {
    die("Error: SubOption '중국1도' not found.\n");
}
echo "Selected SubOption: {$subOpt->suboption} (+{$subOpt->price})\n";

DB::beginTransaction();
try {
    // 1. Create Order
    DB::table('fm_order')->insert([
        'order_seq' => $orderSeq,
        'regist_date' => date('Y-m-d H:i:s'),
        'member_seq' => $memberSeq,
        'order_user_name' => 'Test User',
        'order_cellphone' => '010-0000-0000',
        'order_email' => 'test@example.com',
        'settleprice' => 1080,
        'step' => 15,
        'mode' => 'test',
        'session_id' => 'test_session',
        'important' => '0',
        'sitetype' => 'P',
        'hidden' => 'N',
        'admin_order' => '',
        'skintype' => 'P',
        'total_ea' => 1,
        'total_type' => 1,
        'international_cost' => 0,
        'tax' => 0,
        'shipping_cost' => 0,
        'original_settleprice' => 1080,
    ]);

    // 2. Create Order Item
    $itemSeq = DB::table('fm_order_item')->insertGetId([
        'order_seq' => $orderSeq,
        'goods_seq' => $goodsSeq,
        'goods_name' => 'Dummy Printing Product',
        'goods_shipping_cost' => 0,
        'goods_kind' => 'goods',
        'tax' => 'tax',
        'shipping_policy' => 'shop', 
        'option_international_shipping_status' => 'n',
    ]);

    // 3. Create Order Item Option
    $itemOptionSeq = DB::table('fm_order_item_option')->insertGetId([
        'order_seq' => $orderSeq,
        'item_seq' => $itemSeq,
        'price' => 1000, 
        'step' => 15,
        'ea' => 1,
        'refund_ea' => 0,
        'consumer_price' => 1000,
        'supply_price' => 800,
        'option1' => 'Default',
        'option2' => '',
        'option3' => '',
        'option4' => '',
        'option5' => '',
        'tax' => 100, 
    ]);

    // 4. Create Order Item SubOption
    $subItemSeq = DB::table('fm_order_item_suboption')->insertGetId([
         'item_seq' => $itemSeq,
         'order_seq' => $orderSeq,
         'item_option_seq' => $itemOptionSeq,
         // 'suboption_seq' removed
         'title' => $subOpt->suboption_title, // Assuming 'title' column maps to suboption_title
         'suboption' => $subOpt->suboption,
         'ea' => 1,
         'refund_ea' => 0,
         'price' => $subOpt->price,
         'consumer_price' => 0,
         'supply_price' => 0,
         'step' => 15,
         'step35' => 0, 'step45' => 0, 'step55' => 0, 'step65' => 0, 'step75' => 0, 'step85' => 0,
    ]);

    DB::commit();
    echo "Order {$orderSeq} created with SubOption.\n";
    
    // Verify
    $savedSub = DB::table('fm_order_item_suboption')->where('item_suboption_seq', $subItemSeq)->first();
    if ($savedSub && $savedSub->suboption === '중국1도' && $savedSub->price == 80) {
        echo "SUCCESS: SubOption saved correctly in Order.\n";
    } else {
        echo "FAIL: SubOption not saved correctly.\n";
    }

} catch (\Exception $e) {
    DB::rollBack();
    echo "ORDER FAIL: " . $e->getMessage() . "\n";
}
