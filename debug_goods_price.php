<?php
use Illuminate\Support\Facades\DB;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$goods = DB::table('fm_goods')->where('goods_seq', 210770)->first();
echo "Price: " . $goods->price . "\n";
echo "Consumer Price: " . $goods->consumer_price . "\n";
echo "Supply Price: " . $goods->supply_price . "\n";
