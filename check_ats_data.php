<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$member = \App\Models\Member::where('userid', 'newjjang3')->first();
echo "Member Emoney: " . $member->emoney . "\n";

$goods = \Illuminate\Support\Facades\DB::table('fm_category_link')
    ->where('category_code', 'like', '0159%')
    ->join('fm_goods', 'fm_category_link.goods_seq', '=', 'fm_goods.goods_seq')
    ->where('fm_goods.goods_view', 'look') // 판매중인 상품만
    ->orderBy('fm_goods.goods_seq', 'desc')
    ->select('fm_goods.*', 'fm_category_link.category_code')
    ->first();

if ($goods) {
    echo "ATS Goods Seq: " . $goods->goods_seq . "\n";
    echo "ATS Goods Name: " . $goods->goods_name . "\n";
    echo "ATS Goods Scode: " . $goods->goods_scode . "\n";
} else {
    echo "No ATS Goods found.\n";
}
