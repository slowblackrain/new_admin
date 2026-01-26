<?php

use App\Models\Order;
use App\Models\OrderLog;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- Seeding Test Logs ---\n";

$order = Order::orderBy('order_seq', 'desc')->first();

if ($order) {
    echo "Adding logs to Order: " . $order->order_seq . "\n";
    
    OrderLog::create([
        'order_seq' => $order->order_seq,
        'type' => 'process',
        'actor' => 'DebugScript',
        'title' => '테스트 로그 1',
        'detail' => '로그 표시 테스트입니다.',
        'regist_date' => now(),
        'mtype' => 's',
        'mseq' => 0
    ]);
    
    OrderLog::create([
        'order_seq' => $order->order_seq,
        'type' => 'pay',
        'actor' => 'System',
        'title' => '결제 로그',
        'detail' => '결제 정보 확인',
        'regist_date' => now()->subMinutes(10),
        'mtype' => 's',
        'mseq' => 0
    ]);
    
    echo "Logs added.\n";
    echo "New Log Count: " . $order->logs()->count() . "\n";
} else {
    echo "No order found!\n";
}
