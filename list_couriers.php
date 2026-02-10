<?php
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Search fm_config for delivery related keys
$config = DB::table('fm_config')
    ->where('codecd', 'like', '%delivery%')
    ->orWhere('codecd', 'like', '%company%')
    ->get();

echo "=== fm_config Matches ===\n";
foreach($config as $c) {
     echo "Key: " . $c->codecd . " | Value: " . mb_convert_encoding(substr($c->value, 0, 200), 'UTF-8', 'auto') . "\n";
}
