<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Goods;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking DB Readiness for Order Processing...\n";

// 1. Check fm_order_sequence
if (Schema::hasTable('fm_order_sequence')) {
    echo "[OK] fm_order_sequence table exists.\n";
} else {
    echo "[FAIL] fm_order_sequence table MISSING.\n";
}

// 2. Check fm_goods_supply
if (Schema::hasTable('fm_goods_supply')) {
    echo "[OK] fm_goods_supply table exists.\n";
} else {
    echo "[FAIL] fm_goods_supply table MISSING.\n";
}

// 3. User Points/Coupons (fm_member_data, fm_member_coupon?)
if (Schema::hasTable('fm_member')) {
    echo "[OK] fm_member table exists.\n";
}

// 4. Check Stock for a Test Product
$testGoods = Goods::first();
if ($testGoods) {
    echo "Test Goods: {$testGoods->goods_seq} ({$testGoods->goods_name})\n";
    $supply = DB::table('fm_goods_supply')->where('goods_seq', $testGoods->goods_seq)->first();
    if ($supply) {
        echo "[OK] Supply record found. Stock: " . ($supply->stock ?? 'NULL') . "\n";
    } else {
        echo "[WARN] No supply record for this goods.\n";
    }
} else {
    echo "[WARN] No goods found in DB.\n";
}

echo "Done.\n";
