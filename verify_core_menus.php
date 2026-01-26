<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

echo "=================================================\n";
echo "       CORE MENU VERIFICATION\n";
echo "=================================================\n\n";

$routes_to_test = [
    'Goods Catalog' => '/admin/goods/catalog',
    'Goods Regist' => '/admin/goods/regist',
    'Order Catalog' => '/admin/order/catalog',
    'Member Catalog' => '/admin/member/catalog',
    'Board Index' => '/admin/board/index',
    'Marketing Ads' => '/admin/marketing/ad_status',
];

foreach ($routes_to_test as $name => $uri) {
    echo "CHECKING [$name] ($uri)... ";
    try {
        $request = Request::create($uri, 'GET');
        $response = $kernel->handle($request);
        $status = $response->getStatusCode();
        
        if ($status == 200 || $status == 302) {
            echo "OK (Status: $status)\n";
        } else {
            echo "FAIL (Status: $status)\n";
        }
    } catch (\Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}
