<?php
use App\Models\Goods;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<h1>Connection Debug</h1>";

try {
    // 1. Default Connection
    $defaultCount = Goods::query()->count();
    echo "Default Connection Count: $defaultCount<br>";

    // 2. Production Connection
    $prodCount = Goods::on('production')->count();
    echo "Production Connection Count: $prodCount<br>";

    // 3. Search on Default
    $searchDefault = Goods::where('goods_name', 'like', '%SortTest%')->count();
    echo "Search 'SortTest' on Default: $searchDefault<br>";

    // 4. Search on Production
    $searchProd = Goods::on('production')->where('goods_name', 'like', '%SortTest%')->count();
    echo "Search 'SortTest' on Production: $searchProd<br>";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
