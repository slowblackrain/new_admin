<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Goods;

// Simulate Controller Logic
$code = '0110';
$query = Goods::active()->with(['option', 'images']);

// [Parity] Filter private/ATS goods
$memberSeq = 0; // Guest
$query->where('ATS_member_seq', 0);

if ($code) {
    $query->whereHas('categories', function ($q) use ($code) {
        $q->where('fm_category.category_code', 'like', $code . '%');
    });
}

// Check for MKS product
$query = Goods::where('goods_scode', 'like', 'MKS%');
$check = $query->first();

if (!$check) {
    echo "Product not found in DB at all.\n";
} else {
    echo "Product found in DB: {$check->goods_seq}\n";
    // Check scopes
    if ($check->goods_view != 'look') echo " - goods_view is {$check->goods_view}\n";
    if ($check->goods_status != 'normal') echo " - goods_status is {$check->goods_status}\n";
    
    // Check provider
    $provider = DB::table('fm_provider')->where('provider_seq', $check->provider_seq)->first();
    echo " - Provider Status: " . ($provider ? $provider->provider_status : 'Not Found') . "\n";
    
    // Check Active Scope
    $activeCheck = Goods::active()->where('goods_seq', $check->goods_seq)->first();
    if (!$activeCheck) echo " - Filtered by active() scope.\n";
    
    // Check Category
    $catCheck = DB::table('fm_category_link')->where('goods_seq', $check->goods_seq)->where('category_code', 'like', '0110%')->count();
    echo " - Category Link: $catCheck\n";

    // Simulate Controller Query
    $finalCheck = Goods::active()->excludeHiddenCodes()->with(['option', 'images'])
        ->where('ATS_member_seq', 0)
        ->whereHas('categories', function ($q) use ($code) {
            $q->where('fm_category.category_code', 'like', $code . '%');
        })
        ->where('goods_seq', $check->goods_seq)
        ->first();
        
    if ($finalCheck) {
        echo "FAIL: MKS Product is VISIBLE in Controller Query\n";
    } else {
        echo "PASS: MKS Product is HIDDEN in Controller Query\n";
    }
}
