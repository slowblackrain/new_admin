<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\GoodsInput;

// clear existing
GoodsInput::where('goods_seq', 206718)->delete();

// 1. Select Box: Printing Method
$options = [
    '중국1도 50만원 미만시 인쇄비 80원, 몰드 2만원',
    '중국2도 150원/몰드4만원/500개당 몰드1개 무료',
    '중국3도 200원/몰드6만원/500개당 몰드1개 무료',
    '중국4도 250원/몰드8만원/500개당 몰드1개 무료',
    '중국스티커 기본 1,000장 15,000원/작업비장당80원별도/납기12일내외',
    '한국스티커 기본 1,000장 30,000원/작업비장당150원별도/납기6일내외',
    '한국자수 상담후 결제',
    '한국타월 인쇄:100원~200원/몰드판비 20,000원/상담후 확정',
    '중국선물포장 세변의 합 60cm이내 100원/60cm이상 250원/상담후 확정',
    '한국선물포장 세변의 합 60cm이내 200원/60cm이상 500원/상담후 확정',
    '한국중국선물상자 상담후 결정/상품의크기 포장재질에 따라 다름'
];
$optionsString = implode(',', $options);

GoodsInput::create([
    'goods_seq' => 206718,
    'input_name' => '인쇄',
    'input_form' => 'select',
    'input_limit' => $optionsString,
    'input_require' => '0'
]);

// 2. Text Input
GoodsInput::create([
    'goods_seq' => 206718,
    'input_name' => '인쇄 문구 입력',
    'input_form' => 'text',
    'input_limit' => 0,
    'input_require' => '0'
]);

// 3. File Input
GoodsInput::create([
    'goods_seq' => 206718,
    'input_name' => '로고 파일 첨부',
    'input_form' => 'file',
    'input_limit' => 0,
    'input_require' => '0'
]);

echo "Dummy data inserted for 206718 with Select options.\n";
