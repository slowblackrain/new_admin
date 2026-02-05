<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Goods;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

$code = '0001';
echo "Checking integrity for Code: $code\n";

// 1. Check Links vs Categories
$links = DB::table('fm_category_link')
    ->where('category_code', 'like', $code . '%')
    ->select('category_code')
    ->distinct()
    ->get();

echo "Found " . $links->count() . " distinct category codes in Link table:\n";
$missingInCat = 0;
foreach ($links as $link) {
    $exists = DB::table('fm_category')->where('category_code', $link->category_code)->exists();
    if (!$exists) {
        $missingInCat++;
        // Find which goods have this bad link
        $goodsIds = DB::table('fm_category_link')
            ->where('category_code', $link->category_code)
            ->pluck('goods_seq')
            ->toArray();
        $goodsList = implode(', ', array_slice($goodsIds, 0, 5));
        echo " - [MISSING] code {$link->category_code} (Goods: $goodsList...)\n";
    } else {
         echo " - [OK] code {$link->category_code}\n";
    }
}

echo "\nSummary: $missingInCat codes missing in fm_category table.\n";

if ($missingInCat > 0) {
    echo "CRITICAL: 'whereHas(\"categories\")' will fail for items linked to these missing codes because the Eloquent relationship performs an INNER JOIN with fm_category.\n";
}
