<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$goods = \Illuminate\Support\Facades\DB::table('fm_goods')->where('goods_seq', 1000074)->first();
print_r($goods);
