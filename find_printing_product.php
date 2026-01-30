<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$goods = \Illuminate\Support\Facades\DB::table('fm_goods_input')->select('goods_seq')->distinct()->limit(5)->get();
print_r($goods);
