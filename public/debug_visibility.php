<?php
use App\Models\Goods;
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<pre>";
$ids = [206718, 1898];
foreach ($ids as $id) {
    $g = Goods::find($id);
    if (!$g) {
        echo "Product $id NOT FOUND\n";
        continue;
    }
    echo "Product $id: {$g->goods_name}\n";
    echo " - provider_status: {$g->provider_status}\n";
    echo " - display_status: {$g->display_status}\n";
    echo " - sale_status: {$g->sale_status}\n";
    echo " - category: " . ($g->category ? $g->category->category_code : 'None') . "\n";
    
    // Check if it would be found by search query scope
    $isActive = $g->provider_status == 1 && $g->display_status == 1 && $g->sale_status == 1; // Simplistic check
    echo " - Likely Active? " . ($isActive ? "YES" : "NO") . "\n";
}
echo "</pre>";
