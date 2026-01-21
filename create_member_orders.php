<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemOption;
use Illuminate\Support\Facades\DB;

$member_seq = 10; // newjjang3
$orders_to_create = [
    ['step' => 15, 'price' => 15000, 'item' => '테스트 입금대기 상품'],
    ['step' => 25, 'price' => 25000, 'item' => '테스트 결제완료 상품'],
];

foreach ($orders_to_create as $data) {
    try {
        DB::beginTransaction();

        $order_seq = date('YmdHis') . rand(100, 999);

        // 1. Create Order
        $order = new Order();
        $order->order_seq = $order_seq;
        $order->regist_date = date('Y-m-d H:i:s');
        $order->order_user_name = '장승호';
        $order->order_cellphone = '010-1234-5678';
        $order->order_email = 'newjjang3@test.com';
        $order->recipient_user_name = '장승호';
        $order->recipient_cellphone = '010-1234-5678';
        $order->recipient_zipcode = '12345';
        $order->recipient_address = '서울시 테스트구';
        $order->recipient_address_detail = '테스트동';
        $order->settleprice = $data['price'];
        $order->step = $data['step'];
        $order->member_seq = $member_seq;
        // $order->order_type = 'pc'; // Column does not exist
        $order->ip = '127.0.0.1';
        $order->save();

        // 2. Create Order Item
        $item = new OrderItem();
        $item->order_seq = $order_seq;
        $item->goods_seq = 1;
        $item->goods_name = $data['item'];
        $item->save();
        
        // 3. Create Order Item Option
        $option = new OrderItemOption();
        $option->item_seq = $item->item_seq;
        $option->order_seq = $order_seq;
        $option->option1 = '기본';
        $option->ea = 1;
        $option->price = $data['price'];
        $option->step = $data['step']; // Sync with order step
        $option->save();

        DB::commit();
        echo "Created Order: {$order_seq} (Step: {$data['step']})\n";

    } catch (\Exception $e) {
        DB::rollBack();
        echo "Error: " . $e->getMessage() . "\n";
    }
}
