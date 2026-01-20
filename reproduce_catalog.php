<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Goods;
use App\Models\Category;

$code = '0005';
echo "Checking Catalog for Code: $code\n";

// 1. Check Category
$cat = Category::where('category_code', $code)->first();
if ($cat) {
    echo "Category Found: " . $cat->title . "\n";
} else {
    echo "Category NOT Found!\n";
}

// 2. Check Goods Query
$query = Goods::active()->with(['option', 'images']);
$query->whereHas('categories', function ($q) use ($code) {
    $q->where('fm_category.category_code', 'like', $code . '%');
});

$count = $query->count();
echo "Total Goods Found: $count\n";

if ($count > 0) {
    $goods = $query->limit(5)->get();
    foreach ($goods as $g) {
        echo "- [{$g->goods_seq}] {$g->goods_name}\n";
        echo "  - Option Count: " . $g->option->count() . "\n";
        if ($g->option->isEmpty()) {
            echo "  ! WARNING: No Options!\n";
        } else {
            echo "  - Price: " . $g->option->first()->price . "\n";
        }
    }
}
