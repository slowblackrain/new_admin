<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a Mock Request
$request = Illuminate\Http\Request::create('/order/cart/ats-batch', 'POST', [
    'goods_seq_list' => '1000064' // Use the goods_seq we know exists
]);

// Mock Session/Auth if needed?
// The controller uses Auth::id() ?? 0. 
// We can try running it as guest first.

$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";

// Check Database
if ($response->getStatusCode() == 200) {
    $cart = App\Models\Cart::where('goods_seq', '1000064')->orderBy('cart_seq', 'desc')->first();
    if ($cart) {
        echo "Cart Created: " . $cart->cart_seq . "\n";
        $option = App\Models\CartOption::where('cart_seq', $cart->cart_seq)->first();
        if ($option) {
            echo "Option Shipping Method: " . $option->shipping_method . "\n";
        } else {
            echo "Option NOT found!\n";
        }
    } else {
        echo "Cart NOT found!\n";
    }
}
