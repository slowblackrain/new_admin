<?php

use App\Models\Order;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- Recent Orders ---\n";

$orders = Order::orderBy('regist_date', 'desc')->take(5)->get();

if ($orders->isEmpty()) {
    echo "No orders found in the database.\n";
} else {
    foreach ($orders as $order) {
        echo "Order Seq: " . $order->order_seq . " | Date: " . $order->regist_date . "\n";
    }
}
