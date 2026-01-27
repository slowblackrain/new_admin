<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Check Schema
echo "=== Schema Check ===\n";
$cols = Illuminate\Support\Facades\DB::select("DESCRIBE fm_order order_seq");
print_r($cols);

// 2. Check Timezone
echo "\n=== Timezone Check ===\n";
echo "Config app.timezone: " . config('app.timezone') . "\n";
echo "Date Now: " . date('Y-m-d H:i:s') . "\n";
echo "Carbon Now: " . now()->format('Y-m-d H:i:s') . "\n";

// 3. Search for Orders Today
echo "\n=== Orders Today (20260127%) ===\n";
$orders = Illuminate\Support\Facades\DB::table('fm_order')
    ->where('order_seq', 'like', '20260127%')
    ->orderBy('regist_date', 'desc')
    ->get();

foreach($orders as $o) {
    echo "SEQ: " . $o->order_seq . " | Date: " . $o->regist_date . " | Step: " . $o->step . "\n";
}

$targetId = '2026012702530919792';
echo "\nChecking Specific ID: $targetId\n";
$exists = Illuminate\Support\Facades\DB::table('fm_order')->where('order_seq', $targetId)->exists();
echo "Exists? " . ($exists ? 'YES' : 'NO') . "\n";
