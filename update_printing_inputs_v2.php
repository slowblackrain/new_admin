<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$goodsSeq = 206718;

echo "Updating Printing Inputs for Goods {$goodsSeq}...\n";

// 1. Delete existing inputs
DB::table('fm_goods_input')->where('goods_seq', $goodsSeq)->delete();
echo "Deleted existing inputs.\n";

// 2. Insert '인쇄문구' (Text)
DB::table('fm_goods_input')->insert([
    'goods_seq' => $goodsSeq,
    'input_name' => '인쇄문구',
    'input_form' => 'text',
    'input_limit' => 0,
    'input_require' => '0',
]);
echo "Inserted '인쇄문구'.\n";

// 3. Insert '인쇄이미지' (File) x 10
// Legacy allows up to 10 files. We insert 10 rows.
for ($i = 1; $i <= 10; $i++) {
    DB::table('fm_goods_input')->insert([
        'goods_seq' => $goodsSeq,
        'input_name' => '인쇄이미지',
        'input_form' => 'file',
        'input_limit' => 0,
        'input_require' => '0',
    ]);
}
echo "Inserted 10 '인쇄이미지' inputs.\n";

echo "Done.\n";
