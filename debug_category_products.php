<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Goods;
use App\Models\Category;

$code = '0001'; // Default test code

echo "Debugging Category Product Query for Code: $code\n";

// 1. Check Category Existence
$cat = Category::where('category_code', $code)->first();
if (!$cat) {
    echo "Category $code not found!\n";
} else {
    echo "Category Found: {$cat->title} ({$cat->category_code})\n";
}

// 2. Check Raw Link Table
$linkCount = \Illuminate\Support\Facades\DB::table('fm_category_link')
    ->where('category_code', 'like', $code . '%')
    ->count();
echo "Total items in fm_category_link for $code%: $linkCount\n";

// 3. Check Goods Model Scope
$query = Goods::active();
$query->whereHas('categories', function ($q) use ($code) {
    $q->where('fm_category.category_code', 'like', $code . '%');
});

echo "SQL: " . $query->toSql() . "\n";
echo "Bindings: " . implode(', ', $query->getBindings()) . "\n";

$count = $query->count();
echo "Goods::active()->whereHas(...) Count: $count\n";

if ($count == 0) {
    echo "Analyzing first 5 items in category link without active scope...\n";
    $rawItems = \Illuminate\Support\Facades\DB::table('fm_category_link')
        ->where('category_code', 'like', $code . '%')
        ->limit(5)
        ->pluck('goods_seq');
    
    foreach ($rawItems as $seq) {
        $g = Goods::find($seq);
        if ($g) {
            echo "Goods [$seq]: status={$g->goods_view}, provider_status={$g->provider_status}\n";
        } else {
            echo "Goods [$seq]: Not found in fm_goods\n";
        }
    }
}
