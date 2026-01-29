<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$orderSeq = $argv[1] ?? null;
$step = $argv[2] ?? null;

if (!$orderSeq || !$step) {
    echo "Usage: php update_order_step.php [order_seq] [step]\n";
    exit(1);
}

$order = \App\Models\Order::find($orderSeq);
if ($order) {
    $order->step = $step;
    $order->save();
    echo "Updated order {$orderSeq} to step {$step}\n";
} else {
    echo "Order not found.\n";
}
