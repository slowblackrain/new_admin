<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$goodsSeq = 210770;
$goods = \App\Models\Goods::with('categories')->find($goodsSeq);

if (!$goods) {
    echo "Goods $goodsSeq not found.\n";
    exit;
}

echo "Goods: " . $goods->goods_name . "\n";
echo "Code: " . $goods->goods_scode . "\n";
foreach ($goods->categories as $cat) {
    echo "Category: " . $cat->category_code . " (" . $cat->title . ")\n";
}

echo "\n--- Raw DB Check ---\n";
$rows = \Illuminate\Support\Facades\DB::select("SELECT * FROM fm_goods_option WHERE goods_seq = ?", [$goodsSeq]);
foreach ($rows as $row) {
    echo "Row Seq: {$row->option_seq}, Title: '{$row->option1}'\n";
}
