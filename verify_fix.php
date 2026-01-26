<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Verifying Catalog Page Load (After Fix)...\n";

try {
    $request = \Illuminate\Http\Request::create('/admin/goods/catalog', 'GET');
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() == 200) {
        echo "SUCCESS: Page loaded without SQL error.\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
