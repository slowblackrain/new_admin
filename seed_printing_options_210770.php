<?php

use Illuminate\Support\Facades\DB;
use App\Models\Goods;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$goodsSeq = 210770;

echo "Seeding Printing Options for Goods: $goodsSeq\n";

// 1. Delete existing (just in case)
DB::table('fm_goods_suboption')->where('goods_seq', $goodsSeq)->delete();
DB::table('fm_goods_input')->where('goods_seq', $goodsSeq)->delete();

// 2. Insert Suboptions
$suboptions = [
    '중국1도' => 0,
    '중국2도' => 0,
    '중국3도' => 0,
    '중국4도' => 0,
    '한국1도' => 0,
    '중국스티커' => 15000,
    '한국스티커' => 30000,
    '한국자수' => 0,
    '한국타월' => 0,
    '중국선물포장' => 100,
    '한국선물포장' => 200,
    '한국중국선물상자' => 0,
    '볼펜/연필' => 0,
];

$now = date('Y-m-d H:i:s');

foreach ($suboptions as $title => $price) {
    DB::table('fm_goods_suboption')->insert([
        'goods_seq' => $goodsSeq,
        'suboption_title' => '인쇄', // Matched to view.blade.php expectation
        'suboption' => $title,
        'consumer_price' => $price,
        'price' => $price,
        'reserve' => 0,
        'sub_required' => 'n', // Changed to n to avoid issues if frontend enforces it strictly too early
    ]);
}

// 3. Insert Supply Info
$insertedSuboptions = DB::table('fm_goods_suboption')->where('goods_seq', $goodsSeq)->get();
foreach ($insertedSuboptions as $sub) {
    DB::table('fm_goods_supply')->insert([
        'goods_seq' => $goodsSeq,
        'suboption_seq' => $sub->suboption_seq,
        'supply_price' => $sub->price,
        'stock' => 9999,
        'badstock' => 0,
        'safe_stock' => 0,
        'total_stock' => 9999,
        'total_badstock' => 0,
        'total_supply_price' => 0,
    ]);
}


// 4. Insert Inputs
DB::table('fm_goods_input')->insert([
    'goods_seq' => $goodsSeq,
    'input_name' => '인쇄문구',
    'input_form' => 'text',
    'input_limit' => 0,
    'input_require' => 'n',
]);

DB::table('fm_goods_input')->insert([
    'goods_seq' => $goodsSeq,
    'input_name' => '인쇄이미지',
    'input_form' => 'file',
    'input_limit' => 0,
    'input_require' => 'n',
]);


echo "Seeding Completed.\n";
