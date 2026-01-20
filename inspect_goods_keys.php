<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

$goods = DB::table('fm_goods')->where('goods_seq', 1)->first();
$keys = array_keys((array) $goods);
file_put_contents('goods_keys.txt', implode(", ", $keys));
