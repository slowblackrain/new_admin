<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

$request = Request::create('/', 'GET');
$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Redirect Target: " . $response->headers->get('Location') . "\n";
