<?php
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$ids = [206718, 1898];
$items = DB::table('fm_goods')->whereIn('goods_seq', $ids)->get();

echo "<h1>ID Check</h1>";
if ($items->isEmpty()) {
    echo "NO ITEMS FOUND with IDs: " . implode(', ', $ids);
} else {
    foreach ($items as $item) {
        echo "ID: {$item->goods_seq} | Name: {$item->goods_name} | View: {$item->goods_view} | Status: {$item->goods_status} | Provider: {$item->provider_status}<br>";
    }
}
