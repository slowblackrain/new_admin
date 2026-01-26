<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

echo "Verifying Goods Batch Routes...\n";

try {
    // Check Route
    $request = Request::create('/admin/goods/batch_modify', 'GET');
    $response = $kernel->handle($request);
    
    echo "GET /admin/goods/batch_modify => Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() == 200 || $response->getStatusCode() == 302) {
        echo "SUCCESS: Page Reachable.\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
