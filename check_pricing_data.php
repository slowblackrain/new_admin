<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Check Goods Discount Thresholds
echo "--- Goods Discount Thresholds (Sample) ---\n";
$goods = \Illuminate\Support\Facades\DB::table('fm_goods')
    ->select('goods_seq', 'goods_name', 'fifty_discount_ea', 'hundred_discount_ea', 'fifty_discount', 'hundred_discount', 'mtype_discount')
    ->where('fifty_discount_ea', '>', 0)
    ->orWhere('hundred_discount_ea', '>', 0)
    ->limit(10)
    ->get();

foreach ($goods as $g) {
    echo "Goods: {$g->goods_seq} | Name: " . mb_substr($g->goods_name, 0, 20) . "... | Tier1 Qty: {$g->fifty_discount_ea} (Disc: {$g->fifty_discount}) | Tier2 Qty: {$g->hundred_discount_ea} (Disc: {$g->hundred_discount}) | Member Disc: {$g->mtype_discount}\n";
}

// 2. Check fm_member_ats (if exists)
echo "\n--- Check fm_member_ats ---\n";
try {
    $ats = \Illuminate\Support\Facades\DB::table('fm_member_ats')->limit(5)->get();
    print_r($ats);
} catch (\Exception $e) {
    echo "fm_member_ats table not found or error: " . $e->getMessage() . "\n";
}
