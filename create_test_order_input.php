<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItemInput;

// 1. Find Provider 'dometopia001'
$provider = DB::table('fm_provider')->where('provider_id', 'newjjang3')->first();
if (!$provider) {
    die("Provider 'newjjang3' not found.\n");
}
echo "Provider: " . $provider->provider_id . " (User: " . $provider->userid . ")\n";

// 2. Find Linked Member
$member = \App\Models\Member::where('userid', $provider->userid)->first();
if (!$member) {
    die("Member not found for userid: " . $provider->userid . "\n");
}
echo "Member Seq: " . $member->member_seq . "\n";

// 3. Find Latest Order for this Member
$order = Order::where('member_seq', $member->member_seq)
    ->orderBy('regist_date', 'desc')
    ->with('items')
    ->first();

if (!$order) {
    echo "No order found for this seller. Creating a dummy order...\n";
    // Setup minimal dummy order
    $order = new Order();
    $order->order_seq = date('YmdHis') . '99999';
    $order->member_seq = $member->member_seq;
    $order->order_user_name = 'Test Seller';
    $order->order_cellphone = '010-1234-5678';
    $order->order_email = 'test@example.com';
    $order->recipient_user_name = 'Receiver';
    $order->recipient_cellphone = '010-9876-5432';
    $order->recipient_zipcode = '12345';
    $order->recipient_address = 'Test Address';
    $order->recipient_address_detail = '101';
    $order->settleprice = 10000;
    $order->step = 25; // Payment Confirmed
    $order->regist_date = now();
    $order->save();

    // Create Item
    $item = new \App\Models\OrderItem();
    $item->order_seq = $order->order_seq;
    $item->goods_seq = 0;
    $item->goods_name = 'Test Custom Product';
    $item->save();
    
    // Reload items
    $order->load('items');
}

echo "Target Order: " . $order->order_seq . "\n";
$item = $order->items->first();

if (!$item) {
     $item = new \App\Models\OrderItem();
    $item->order_seq = $order->order_seq;
    $item->goods_seq = 0;
    $item->goods_name = 'Test Custom Product';
    $item->save();
    echo "Created dummy item.\n";
}

echo "Target Item: " . $item->item_seq . "\n";

// 4. Insert Dummy Inputs
OrderItemInput::where('item_seq', $item->item_seq)->delete(); // Clean up old

OrderItemInput::create([
    'item_seq' => $item->item_seq,
    'title' => '인쇄 문구',
    'value' => 'Happy New Year 2026!',
    'type' => 'text'
]);

OrderItemInput::create([
    'item_seq' => $item->item_seq,
    'title' => '로고 파일',
    'value' => 'uploads/order/dummy_logo.png', // Mock path
    'type' => 'file'
]);

echo "SUCCESS: Dummy Inputs Created for Order " . $order->order_seq . "\n";
