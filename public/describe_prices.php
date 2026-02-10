<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "--- fm_goods ---\n";
$columns = Illuminate\Support\Facades\DB::select('SHOW COLUMNS FROM fm_goods');
foreach ($columns as $col) {
    if (strpos($col->Field, 'price') !== false) {
        echo $col->Field . " | " . $col->Type . "\n";
    }
}

echo "\n--- fm_goods_option ---\n";
$columns = Illuminate\Support\Facades\DB::select('SHOW COLUMNS FROM fm_goods_option');
foreach ($columns as $col) {
    if (strpos($col->Field, 'price') !== false) {
        echo $col->Field . " | " . $col->Type . "\n";
    }
}
