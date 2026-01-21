<?php
// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

// Generate unique order sequence
$order_seq = date('YmdHis') . rand(100, 999);

try {
    DB::beginTransaction();

    // 1. Create Order
    $order = new Order();
    $order->order_seq = $order_seq;
    $order->regist_date = date('Y-m-d H:i:s');
    // $order->order_type = 'mobile'; // Column does not exist
    $order->order_user_name = '테스트유저';
    $order->order_cellphone = '010-0000-0000'; // Changed from order_mobile
    $order->order_email = 'test@example.com';
    $order->recipient_user_name = '테스트수령인';
    $order->recipient_cellphone = '010-0000-0000'; // Changed from recipient_mobile
    $order->recipient_zipcode = '12345';
    $order->recipient_address = '서울시 테스트구 테스트동';
    $order->recipient_address_detail = '101호';
    $order->settleprice = 50000;
    $order->step = 10; // Deposit Waiting (입금대기)
    $order->member_seq = 0; // Guest or Test Member
    // $order->userid = 'test_guest'; // Column does not exist
    $order->ip = '127.0.0.1';
    $order->save();

    // 2. Create Order Item
    $item = new OrderItem();
    $item->order_seq = $order_seq;
    // $item->item_seq = 1; // Removed to use Auto Increment
    $item->goods_seq = 1;
    $item->goods_name = '테스트 상품';
    $item->save();
    
    $generated_item_seq = $item->item_seq; // Get ID

    // 3. Create Order Item Option
    $option = new \App\Models\OrderItemOption();
    $option->item_seq = $generated_item_seq;
    $option->order_seq = $order_seq;
    $option->option1 = '기본 옵션';
    $option->ea = 1;
    $option->price = 50000;
    $option->step = 10;
    $option->save();

    DB::commit();
    echo "Test Order Created Successfully: " . $order_seq;

} catch (\Exception $e) {
    DB::rollBack();
    echo "Error Creating Order: " . $e->getMessage();
}
