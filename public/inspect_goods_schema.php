<?php
use App\Models\Goods;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<pre>";
$goods = Goods::on('production')->first();
if ($goods) {
    print_r($goods->getAttributes());
} else {
    echo "No goods found.";
}
echo "</pre>";
