<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$goodsSeq = 210770;
$goods = \App\Models\Goods::find($goodsSeq);

if (!$goods) {
    echo "Goods $goodsSeq not found.\n";
    exit;
}

echo "Goods: " . $goods->goods_name . "\n";
echo "--- SubOptions (Printing) ---\n";
$subOptions = \Illuminate\Support\Facades\DB::table('fm_goods_suboption')
    ->where('goods_seq', $goodsSeq)
    ->get();

if ($subOptions->isEmpty()) {
    echo "No SubOptions found.\n";
} else {
    foreach ($subOptions as $opt) {
        echo "Seq: {$opt->suboption_seq}, Title: {$opt->suboption_title}, Value: {$opt->suboption}\n";
    }
}

echo "--- Inputs (Printing Text/Image) ---\n";
$inputs = \Illuminate\Support\Facades\DB::table('fm_goods_input')
    ->where('goods_seq', $goodsSeq)
    ->get();

if ($inputs->isEmpty()) {
    echo "No Inputs found.\n";
} else {
    foreach ($inputs as $input) {
        echo "Seq: {$input->input_seq}, Name: {$input->input_name}, FormType: {$input->input_form}\n";
    }
}
