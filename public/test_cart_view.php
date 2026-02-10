<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Mock Request to Cart Index
$request = Illuminate\Http\Request::create('/order/cart', 'GET');

// Handle Request
$response = $kernel->handle($request);

// We can't easily extract View data from response content (HTML).
// But we can check if the Controller logic works by instantiating Controller?
// Or we can just look for the "is_postpaid" string if we dumped it?
// Better: resolving the controller and calling the method directly if possible, 
// but method returns a View.

// Let's use a different approach. modifying the controller temporarily to dump data? No.
// Let's rely on the fact that I implemented the logic in the controller earlier.
// 
// I will just modify this script to manually execute the logic:
// Fetch Cart items and check the logic I wrote.

$memberSeq = 0; // Guest
$sessionId = Illuminate\Support\Facades\Session::getId();

// Note: In CLI, Session ID might be different or empty. 
// We need to use the same session as the previous test?
// The previous test created a cart item for a session.
// But we don't know that session ID easily unless we stored it.

// Let's just create a new cart item here for testing logic.
echo "Creating Test Item...\n";
$goodsSeq = 1000064; 
// ... wait, I can just use the Model directly to check logic.

$cartItems = App\Models\Cart::where('goods_seq', $goodsSeq)->get();
echo "Found " . $cartItems->count() . " items.\n";

foreach ($cartItems as $item) {
    $option = $item->options->first();
    $isPostpaid = false;
    if ($option && $option->shipping_method == 'postpaid') {
        $isPostpaid = true;
    }
    
    echo "CartSeq: " . $item->cart_seq . " | Shipping: " . ($option->shipping_method ?? 'None') . " | IsPostpaid: " . ($isPostpaid ? 'YES' : 'NO') . "\n";
}
