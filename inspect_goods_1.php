<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Goods;

$goods = Goods::with('option')->find(1);

if ($goods) {
    echo "Goods: " . $goods->goods_name . "\n";
    echo "Option Count: " . $goods->option->count() . "\n";
    foreach ($goods->option as $opt) {
        echo " - Opt: " . $opt->option1 . " | Price: " . $opt->price . "\n";
    }
} else {
    echo "Goods 1 not found.\n";
}
