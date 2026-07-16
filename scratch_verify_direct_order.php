<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Front\CartController;
use Illuminate\Http\Request;

echo "=== START DIRECT ORDER (BYPASS TO CART) VERIFICATION ===\n";

DB::beginTransaction();
try {
    // 1. Get a valid ordinary product & its default option
    $goods = DB::table('fm_goods')->first();
    if (!$goods) {
        throw new \Exception("No product found for test!");
    }
    
    $option = DB::table('fm_goods_option')->where('goods_seq', $goods->goods_seq)->first();
    if (!$option) {
        throw new \Exception("No option found for test product!");
    }
    
    echo "Using Product Seq: {$goods->goods_seq}, Option Seq: {$option->option_seq}\n";

    // 2. Mock Cart Store Request (simulate direct buy form post)
    $cartController = app(CartController::class);
    
    // Simulate synchronous post buy submission
    $mockRequest = Request::create('/cart/add', 'POST', [
        'goods_seq' => $goods->goods_seq,
        'option_seq' => [$option->option_seq],
        'ea' => [1],
        'direct_buy' => 'Y'
    ]);
    
    echo "Simulating synchronous direct cart store...\n";
    $response = $cartController->store($mockRequest);
    $httpCode = $response->getStatusCode();
    
    echo "Response HTTP Status: {$httpCode}\n";
    
    if ($response->isRedirect()) {
        $redirectUrl = $response->getTargetUrl();
        echo "Response Redirect Target: {$redirectUrl}\n";
        
        if (strpos($redirectUrl, '/order/form') !== false && strpos($redirectUrl, 'cart_seq') !== false) {
            echo "   -> [SUCCESS] Successfully generated direct order redirect with query parameter cart_seq!\n";
        } else {
            throw new \Exception("Redirect target is unexpected: {$redirectUrl}");
        }
    } else {
         throw new \Exception("Response was not a redirect!");
    }

} catch (\Exception $e) {
    echo "!!! TEST FAILED: " . $e->getMessage() . "\n";
} finally {
    DB::rollBack();
    echo "=== DB ROLLBACK COMPLETE. ===\n";
}
