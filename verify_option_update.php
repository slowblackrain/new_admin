<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\GoodsController;
use Illuminate\Http\Request;

echo "Verifying Option Update Logic...\n";

// 1. Pick a goods and option
$option = DB::table('fm_goods_option')->first();
if (!$option) {
    die("No options found to test.\n");
}
$optSeq = $option->option_seq;
$oldPrice = $option->price;

// Get current stock
$supply = DB::table('fm_goods_supply')->where('option_seq', $optSeq)->first();
$oldStock = $supply ? $supply->stock : 0;

echo "Target Option: {$optSeq} | Price: {$oldPrice} -> 12345 | Stock: {$oldStock} -> 99\n";

// 2. Simulate Request
$controller = new GoodsController();
$request = Request::create('/admin/goods/update_options', 'POST', [
    'options' => [
        $optSeq => [
            'price' => '12,345', // With comma
            'stock' => '99'
        ]
    ]
]);

// 3. Execute
try {
    $redirect = $controller->updateOptions($request);
    
    // 4. Verify DB
    $newOption = DB::table('fm_goods_option')->where('option_seq', $optSeq)->first();
    $newSupply = DB::table('fm_goods_supply')->where('option_seq', $optSeq)->first();
    
    echo "Result Price: {$newOption->price} (Exp: 12345)\n";
    echo "Result Stock: {$newSupply->stock} (Exp: 99)\n";

    if ($newOption->price == 12345 && $newSupply->stock == 99) {
        echo "PASS: Update Success.\n";
        
        // Restore
        DB::table('fm_goods_option')->where('option_seq', $optSeq)->update(['price' => $oldPrice]);
        DB::table('fm_goods_supply')->where('option_seq', $optSeq)->update(['stock' => $oldStock]);
        echo "Restored original values.\n";
    } else {
        echo "FAIL: Values mismatch.\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
