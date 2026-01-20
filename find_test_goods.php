<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Goods;

$noOption = Goods::doesntHave('option')->first();
$hasOption = Goods::has('option')->first();

echo "No Option Goods Seq: " . ($noOption ? $noOption->goods_seq : 'None') . "\n";
echo "Has Option Goods Seq: " . ($hasOption ? $hasOption->goods_seq : 'None') . "\n";
