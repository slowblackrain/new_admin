<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Find a real goods_seq
$goods = \App\Models\Goods::first();
if (!$goods) {
    echo "No goods in DB.\n";
    exit;
}
$seq = $goods->goods_seq;

$request = Illuminate\Http\Request::create(
    '/common/get_right_display', 'GET',
    ['type' => 'right_item_recent', 'page' => 1, 'limit' => 2], 
    ['goods_today' => json_encode([$seq])] // cookies
);
$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Content:\n" . $response->getContent() . "\n";
