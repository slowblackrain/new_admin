<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Searching for 'shop_key' ---\n";
$row = \Illuminate\Support\Facades\DB::table('fm_config')->where('codecd', 'shop_key')->first();
if ($row) {
    echo "Found shop_key: " . $row->value . "\n";
} else {
    echo "shop_key not found in fm_config.\n";
}

echo "--- Searching for 'encrypt' ---\n";
$rows = \Illuminate\Support\Facades\DB::table('fm_config')->where('codecd', 'like', '%encrypt%')->get();
foreach ($rows as $r) {
    echo $r->codecd . ": " . $r->value . "\n";
}

echo "--- Searching for 'key' (limit 10) ---\n";
$rows2 = \Illuminate\Support\Facades\DB::table('fm_config')->where('codecd', 'like', '%key%')->limit(10)->get();
foreach ($rows2 as $r) {
    echo $r->codecd . ": " . $r->value . "\n";
}
