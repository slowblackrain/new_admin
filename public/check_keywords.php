<?php
use App\Models\Goods;
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<pre>";
// Find goods with keywords
$items = Goods::where('keyword', '!=', '')->limit(10)->get();
foreach ($items as $item) {
    echo "ID: {$item->goods_seq} | Name: {$item->goods_name} | Keyword: {$item->keyword}\n";
}
echo "</pre>";
