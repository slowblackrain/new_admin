<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\GoodsInput;

// clear existing
GoodsInput::where('goods_seq', 1898)->delete();

// Add Text Input
GoodsInput::create([
    'goods_seq' => 1898,
    'input_name' => '인쇄 문구 입력',
    'input_form' => 'text',
    'input_limit' => 0,
    'input_require' => '0'
]);

// Add File Input
GoodsInput::create([
    'goods_seq' => 1898,
    'input_name' => '로고 파일 첨부',
    'input_form' => 'file',
    'input_limit' => 0,
    'input_require' => '0'
]);

echo "Dummy data inserted.\n";
