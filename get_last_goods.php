<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
use Illuminate\Support\Facades\DB;
echo DB::table('fm_goods')->where('goods_name', 'DetailTestItem')->orderBy('goods_seq', 'desc')->value('goods_seq');
