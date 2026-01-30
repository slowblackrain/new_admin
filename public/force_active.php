<?php
use App\Models\Goods;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$ids = [206718, 1898];

// Force Update with correct values from Goods model scope
// scopeActive: where('goods_view', 'look')->where('goods_status', 'normal')
try {
    DB::table('fm_goods')
        ->whereIn('goods_seq', $ids)
        ->update([
            'provider_status' => 1,
            'goods_view' => 'look',
            'goods_status' => 'normal'
        ]);
    echo "Force Active Complete (view=look, status=normal) for IDs: " . implode(', ', $ids);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<br><a href='/goods/search?search_text=SortTest'>Search Now</a>";
