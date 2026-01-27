<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

$seq = 182128;

echo "--- Checking DB Status for $seq ---\n";
$goods = DB::table('fm_goods')->where('goods_seq', $seq)->first();
if ($goods) {
    echo "Goods Found: {$goods->goods_name} (Status: {$goods->goods_status})\n";
} else {
    echo "Goods NOT FOUND in DB!\n";
}

$opts = DB::table('fm_goods_option')->where('goods_seq', $seq)->get();
echo "Options Found: " . $opts->count() . "\n";
foreach($opts as $o) {
    echo "- Option Seq: {$o->option_seq} (Default: {$o->default_option})\n";
}

echo "\n--- Simulating Validation ---\n";
$data = [
    'goods_seq' => $seq,
    'option_seq' => [], // Simulate empty or populated
    'ea' => []
];

$validator = Validator::make($data, [
    'goods_seq' => 'required|exists:fm_goods,goods_seq',
]);

if ($validator->fails()) {
    echo "Validation FAILED:\n";
    print_r($validator->errors()->all());
} else {
    echo "Validation PASSED for goods_seq.\n";
}
