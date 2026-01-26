<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

echo "Verifying Statistic Routes...\n";

// Test GET /admin/statistic_summary
try {
    $request = Request::create('/admin/statistic_summary', 'GET');
    $response = $kernel->handle($request);
    
    // Check status
    echo "GET /admin/statistic_summary => Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() == 200 || $response->getStatusCode() == 302) {
        echo "SUCCESS: Statistic Summary Page Reachable.\n";
    } else {
        echo "FAIL: Statistic Summary Status mismatch.\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
