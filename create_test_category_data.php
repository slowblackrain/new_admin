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
echo "Creating Test Data for Category $code...\n";

// 1. Create Category
$cat = Category::firstOrNew(['category_code' => $code]);
if (!$cat->exists) {
    $cat->title = 'Test Category';
    $cat->parent_id = 0;
    $cat->level = 1;
    $cat->position = 1;
    $cat->hide = '0'; // Visible
    $cat->hide_in_navigation = '0';
    $cat->save();
    echo "Created Category: Test Category ($code)\n";
} else {
    echo "Category exists: {$cat->title}\n";
}

// 2. Create Sub-Categories (for Nav test)
for ($i = 1; $i <= 3; $i++) {
    $subCode = $code . str_pad($i, 4, '0', STR_PAD_LEFT);
    $sub = Category::firstOrNew(['category_code' => $subCode]);
    if (!$sub->exists) {
        $sub->title = "Sub Category $i";
        $sub->parent_id = $cat->id;
        $sub->level = 2;
        $sub->position = $i;
        $sub->hide = '0';
        $sub->hide_in_navigation = '0';
        $sub->save();
        echo "Created Sub-Category: {$sub->title} ($subCode)\n";
    }
}

// 3. Create Test Products
$statuses = [
    'normal' => 'Normal Product',
    'runout' => 'Sold Out Product',
    'unsold' => 'Stopped Product'
];

foreach ($statuses as $status => $name) {
    $goodsId = rand(100000, 999999);
    
    // Check collision
    while(Goods::where('goods_seq', $goodsId)->exists()) {
        $goodsId = rand(100000, 999999);
    }

    $g = new Goods();
    $g->goods_seq = $goodsId;
    $g->goods_name = "[TEST] $name";
    $g->goods_status = $status;
    $g->goods_view = 'look';
    $g->goods_code = 'TEST-' . $goodsId;
    $g->goods_scode = ($i % 2 == 0) ? 'G000' . $goodsId : 'A000' . $goodsId; // Mix Box/Single
    $g->regist_date = date('Y-m-d H:i:s');
    $g->save();

    // Link
    DB::table('fm_category_link')->updateOrInsert(
        ['goods_seq' => $goodsId, 'category_code' => $code],
        ['link_seq' => $goodsId] // Dummy primary if needed, usually auto-inc
    );

    // Option (Price)
    DB::table('fm_goods_option')->insert([
        'goods_seq' => $goodsId,
        'consumer_price' => 12000,
        'price' => 10000,
        'option1' => 'Default',
        'default_option' => 'y'
    ]);
    
    // Image (Re-use existing or placeholder)
    // We'll trust the view to show no_image.gif if missing
    
    echo "Created Goods: {$g->goods_name} (Seq: $goodsId)\n";
}

echo "Done.\n";
