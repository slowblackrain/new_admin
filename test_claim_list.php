<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// admin.order.claim.list (GET)
$request = Illuminate\Http\Request::create('/admin/order/claim/list', 'GET');
$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() == 500) {
    echo "Error: " . $response->exception->getMessage();
} else {
    echo "View rendered successfully.\n";
}
