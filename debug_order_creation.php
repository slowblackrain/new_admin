<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

try {
    echo "=== Simulating Order Creation ===\n";

    // 1. Mock Data
    $member = App\Models\Member::first();
    if (!$member) {
        throw new Exception("No members found to test with.");
    }
    echo "Test User: " . $member->user_name . " (Seq: " . $member->member_seq . ")\n";

    $goods = App\Models\Goods::active()->with('option')->first();
    if (!$goods) {
        throw new Exception("No active goods found.");
    }
    echo "Test Goods: " . $goods->goods_name . " (Seq: " . $goods->goods_seq . ")\n";

    $price = $goods->option->first()->price ?? 0;

    // 2. Create Order Header
    DB::beginTransaction();

    $order = new \App\Models\Order();
    $order->order_seq = time() . rand(100, 999);
    $order->order_user_name = $member->user_name;
    $order->order_cellphone = '010-0000-0000';
    $order->order_email = $member->email ?? 'test@test.com';
    $order->recipient_user_name = 'Receiver';
    $order->recipient_cellphone = '010-1111-2222';
    $order->recipient_zipcode = '12345';
    $order->recipient_address = 'Test Address';
    $order->recipient_address_detail = '101';
    $order->settleprice = $price;
    $order->step = 15; // Order Received
    $order->payment = 'bank';
    $order->regist_date = now();
    $order->member_seq = $member->member_seq;
    $order->save();

    echo "Order Header Created: " . $order->order_seq . "\n";

    // 3. Create Order Item
    $orderItem = new \App\Models\OrderItem();
    $orderItem->order_seq = $order->order_seq;
    $orderItem->goods_seq = $goods->goods_seq;
    $orderItem->goods_name = $goods->goods_name;
    $orderItem->goods_shipping_cost = 0;
    $orderItem->save();

    echo "Order Item Created: " . $orderItem->item_seq . "\n";

    // 4. Create Order Item Option
    $itemOption = new \App\Models\OrderItemOption();
    $itemOption->order_seq = $order->order_seq;
    $itemOption->item_seq = $orderItem->item_seq;
    $itemOption->price = $price;
    $itemOption->ea = 1;
    $itemOption->step = $order->step;
    $itemOption->option1 = 'Default Option';
    $itemOption->save();

    echo "Order Item Option Created: " . $itemOption->item_option_seq . "\n";

    DB::commit();
    echo "=== SUCCESS: Transaction Committed ===\n";

    // 5. Verify Reading Back
    $savedOrder = \App\Models\Order::with('items.options')->find($order->order_seq);
    echo "Retrieved Order: " . $savedOrder->order_seq . "\n";
    echo "- Items: " . $savedOrder->items->count() . "\n";
    echo "- First Item Option Price: " . $savedOrder->items->first()->options->first()->price . "\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}