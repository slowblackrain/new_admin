<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

// Check Order
$order = \App\Models\Order::find('2026012901305417531');

if (!$order) {
    echo "Order not found!\n";
} else {
    echo "Order Seq: " . $order->order_seq . "\n";
    echo "Order Member Seq: " . $order->member_seq . "\n"; 
}

// Find a test User from Member model
$users = \App\Models\Member::where('status', 'active')->take(5)->get();
echo "Found " . $users->count() . " active users:\n";
foreach ($users as $user) {
    echo "Seq: " . $user->member_seq . " | ID: " . $user->userid . " | Name: " . $user->user_name . "\n";
}
