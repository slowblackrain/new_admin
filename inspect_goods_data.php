<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

$goods = DB::table('fm_goods')->where('goods_seq', 1)->first();
foreach ($goods as $key => $value) {
    if (strpos($key, 'price') !== false || strpos($key, 'cost') !== false) {
        echo "$key: $value\n";
    }
}
// Also check for option_type or similar
echo "option_use: " . ($goods->option_use ?? 'N/A') . "\n";
