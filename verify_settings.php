<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

echo "Verifying Setting Routes...\n";

try {
    // Disable collision handler for CLI check if possible, or just catch all
    $request = Request::create('/admin/setting/basic', 'GET');
    $response = $kernel->handle($request);

    echo "GET /admin/setting/basic => Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() == 200 || $response->getStatusCode() == 302) {
        echo "SUCCESS: Route exists (Status Code OK).\n";
    } else {
        echo "FAIL: Unexpected Status Code.\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
