<?php

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$kernel->bootstrap();

// Simulate Request
$request = Illuminate\Http\Request::create('/admin/goods/batch/excel_download?goods_status[]=normal', 'GET');
$app->instance('request', $request);

// Use Route dispatch
$response = $kernel->handle($request);

echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Content-Type: " . $response->headers->get('content-type') . "\n";

if ($response->getStatusCode() === 200 && strpos($response->headers->get('content-type'), 'spreadsheet') !== false) {
    echo "VERIFICATION PASSED: Excel Download check success.\n";
} else {
    echo "VERIFICATION FAILED.\n";
}
