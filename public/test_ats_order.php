<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Goods;
use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Front\OrderController;
use Illuminate\Http\Request;

// 1. Login (Use a valid member seq, e.g., 248604 or Create one)
// Let's use a dummy or existing member. 
// Step 1177 used member_seq 0? No, CartController uses Auth::id() ?? 0. 
// For Order, we need a user for Address etc? OrderController allows guest (member_seq=0).
// But Auto-Copy requires Member (Reseller).
// So I MUST use a member.
$member = App\Models\Member::first();
if (!$member) {
    die("No member found. Cannot test Auto-Copy.\n");
}
Auth::login($member);
echo "Logged in as Member: " . $member->member_seq . "\n";

// 2. Add ATS Product to Cart
// ATS Product must be category 0159... and Provider != Member?
// Let's find a valid ATS product.
// A product with category starting with '0159'.
$atsGoods = DB::table('fm_goods as g')
    ->join('fm_category_link as l', 'g.goods_seq', '=', 'l.goods_seq')
    ->where('l.category_code', 'like', '0159%')
    ->where('g.goods_scode', 'not like', 'GT%') // Exclude Reseller Copies
    ->first();

if (!$atsGoods) {
    // If no ATS product, let's pick a random one and pretend (forCart Logic)?
    // But Auto-Copy needs '0159'.
    echo "No ATS Product (Category 0159%) found. Using random goods and mocking category for test?\n";
    $atsGoods = Goods::first();
    // Use Goods ID 1000064 from previous test
    // To test Auto-Copy properly, I need valid data.
    // I will proceed with Cart Logic verification first.
}
echo "Using Goods: " . $atsGoods->goods_seq . "\n";

// 2. Pre-test Cleanup & Setup
// Clear Cart
App\Models\Cart::where('member_seq', $member->member_seq)->delete();
App\Models\CartOption::where('cart_seq', 'IN', function($q) use ($member) {
    $q->select('cart_seq')->from('fm_cart')->where('member_seq', $member->member_seq);
})->delete(); // Actually Cart delete cascades usually? No, manual delete.
// Just truncate cart_option via join is hard in Eloquent delete.
// Let's use raw delete.
DB::table('fm_cart')->where('member_seq', $member->member_seq)->delete();
DB::table('fm_cart_option')->whereNotExists(function ($query) {
    $query->select(DB::raw(1))
          ->from('fm_cart')
          ->whereRaw('fm_cart.cart_seq = fm_cart_option.cart_seq');
})->delete();

// Update Stock for Goods 1000065 to 100
// Find Option Seq first
$opt = DB::table('fm_goods_option')->where('goods_seq', 1000065)->orderBy('default_option', 'desc')->first();
if ($opt) {
    // Check if Supply exists
    $supplyExists = DB::table('fm_goods_supply')->where('option_seq', $opt->option_seq)->exists();
    if (!$supplyExists) {
        DB::table('fm_goods_supply')->insert([
            'goods_seq' => 1000065,
            'option_seq' => $opt->option_seq,
            'stock' => 100,
            'badstock' => 0,
            'reservation15' => 0,
            'reservation25' => 0,
            'total_stock' => 100,
            'suboption_seq' => 0,
            'supply_price' => 0,
            'ablestock15' => 100,
            'safe_stock' => 0,
            'total_supply_price' => 0,
            'total_badstock' => 0
            // 'regist_date' and 'update_date' removed as they don't exist in fm_goods_supply schema
        ]);
        echo "Inserted Supply for Option " . $opt->option_seq . ".\n";
    } else {
        DB::table('fm_goods_supply')->where('option_seq', $opt->option_seq)->update(['stock' => 100]);
        echo "Updated Stock for Option " . $opt->option_seq . " to 100.\n";
    }
    
    DB::table('fm_goods')->where('goods_seq', 1000065)->update(['tot_stock' => 100]);
    echo "Option1 in DB: '" . $opt->option1 . "'\n";
    
    // VERIFY STOCK
    $verifySupply = DB::table('fm_goods_supply')->where('option_seq', $opt->option_seq)->get();
    foreach ($verifySupply as $vs) {
        echo "Supply Seq: {$vs->supply_seq}, Stock: {$vs->stock}\n";
    }
} else {
    echo "WARNING: No Option found for 1000065.\n";
}

// Call addAtsBatch
$cartController = app(CartController::class);
$req = Request::create('/order/cart/ats-batch', 'POST', [
    'goods_seq_list' => $atsGoods->goods_seq
]);
$res = $cartController->addAtsBatch($req);
echo "Added to Cart: " . $res->getContent() . "\n";

// Debug Cart Option
$cOpt = DB::table('fm_cart_option')->where('cart_seq', '>', 0)->orderBy('cart_option_seq', 'desc')->first();
echo "Cart Option1: '" . $cOpt->option1 . "'\n";

// 3. Get Cart Items
$cartItems = App\Models\Cart::where('member_seq', $member->member_seq)->get();
if ($cartItems->isEmpty()) {
    die("Cart Empty!\n");
}
$cartSeqs = $cartItems->pluck('cart_seq')->toArray();
echo "Cart Seqs: " . implode(',', $cartSeqs) . "\n";

// 4. Submit Order
// Prepare Request
$orderReq = Request::create('/order/pay', 'POST', [
    'cart_seq' => $cartSeqs,
    'order_user_name' => 'Tester',
    'order_cellphone' => '010-1234-5678',
    'order_email' => 'test@test.com',
    'recipient_user_name' => 'Receiver',
    'recipient_cellphone' => '010-9876-5432',
    'recipient_zipcode' => '12345',
    'recipient_address' => 'Test Address',
    'recipient_address_street' => 'Test Street',
    'recipient_address_detail' => '101',
    'payment' => 'bank',
    'bank_account' => 'Test Bank',
    'depositor' => 'Tester'
]);

// Mock Session for CSRF/etc if needed? 
// OrderController uses Session::getId().
// Controller validation might fail on validation rules matching.

$orderController = app(OrderController::class);

try {
    // Call store
    // Note: store returns RedirectResponse usually.
    $response = $orderController->store($orderReq);
    echo "Order Response Status: " . $response->getStatusCode() . "\n";
    
    if ($response->isRedirect()) {
        echo "Redirect Target: " . $response->getTargetUrl() . "\n";
        // parse order_seq from URL if possible? 
        // route('order.complete', ['id' => $order->order_seq])
        // /order/complete/{id}
    }
    
    // 5. Verify DB
    $latestOrder = App\Models\Order::where('member_seq', $member->member_seq)->latest('regist_date')->first();
    if ($latestOrder) {
        echo "Order Created: " . $latestOrder->order_seq . "\n";
        echo "Shipping Cost: " . $latestOrder->shipping_cost . " (Expect 0)\n";
        
        $items = $latestOrder->items;
        foreach ($items as $item) {
            echo "Item: " . $item->goods_name . " | ShippingCost: " . $item->goods_shipping_cost . "\n";
            $opt = $item->option;
            if ($opt) {
                echo "Option Title: " . $opt->title1 . " (Expect [착불])\n";
            }
        }
        
        // 6. Verify Auto Copy
        // Check Admin Memo of original goods
        $original = Goods::find($atsGoods->goods_seq);
        echo "Original Memo: " . $original->admin_memo . "\n";
        
        // Check New Goods
        $newGoods = Goods::where('old_goods_seq', $atsGoods->goods_seq)
            ->where('provider_member_seq', $member->member_seq)
            ->first();
            
        if ($newGoods) {
            echo "SUCCESS: New Goods Copied! Seq: " . $newGoods->goods_seq . "\n";
        } else {
            echo "WARNING: New Goods NOT Copied (maybe not valid ATS category or logic skipped)\n";
        }
    } else {
        echo "FAIL: Order Not Created.\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
