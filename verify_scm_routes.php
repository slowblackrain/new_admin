<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/admin/scm/basic/config', 'GET')
);

echo "Status Code: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() == 302) {
    echo "Redirect Location: " . $response->headers->get('Location') . "\n"; // Should redirect to login if not authenticated
} else {
    echo "Content Length: " . strlen($response->getContent()) . "\n";
}

// Test Warehouse List
$response2 = $kernel->handle(
    $request2 = Illuminate\Http\Request::create('/admin/scm/basic/warehouse', 'GET')
);
echo "Warehouse List Status: " . $response2->getStatusCode() . "\n";
