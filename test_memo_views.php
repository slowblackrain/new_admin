<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// admin.order.customer_memo.index (GET)
$request = Illuminate\Http\Request::create('/admin/order/customer_memo', 'GET');
$response = $kernel->handle($request);

echo "Index Status: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() == 500) {
    echo "Error: " . $response->exception->getMessage();
}

$request2 = Illuminate\Http\Request::create('/admin/order/customer_memo/popup', 'GET');
$response2 = $kernel->handle($request2);

echo "Popup Status: " . $response2->getStatusCode() . "\n";
if ($response2->getStatusCode() == 500) {
    echo "Error: " . $response2->exception->getMessage();
}
