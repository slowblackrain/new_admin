<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = \Illuminate\Support\Facades\DB::table('fm_goods_input')->where('goods_seq', 1898)->count();
echo "Inputs count: " . $count . "\n";

$inputs = \Illuminate\Support\Facades\DB::table('fm_goods_input')->where('goods_seq', 1898)->get();
print_r($inputs);
