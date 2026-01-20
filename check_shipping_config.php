<?php
use Illuminate\Support\Facades\DB;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking fm_config (Partial)...\n";
// fm_config is usually key-value or categorized. Let's see structure.
// If it's old FirstMall, it might be serialized data in 'config_data' or separate columns.
if (Schema::hasTable('fm_config')) {
    // List some columns to guess structure
    $cols = DB::select("DESCRIBE fm_config");
    // Just select all and print first few rows to see format? NO, might be huge.
    // Let's assume standard FirstMall: usually 'shipping' category?
    // Or just look for 'delivery' in fields?
    // Let's try to find rows where field name contains 'delivery' or 'shipping'
    
    // Actually, legacy often uses `fm_provider_shipping` for provider-based shipping.
}

echo "\nChecking fm_provider_shipping...\n";
if (Schema::hasTable('fm_provider_shipping')) {
    $rows = DB::table('fm_provider_shipping')->get();
    foreach ($rows as $r) {
        print_r($r);
    }
}

echo "\nChecking fm_shipping_group...\n";
if (Schema::hasTable('fm_shipping_grouping')) { // Guessing name
     // ...
}

// Check for 'delivery' keyword in fm_config if table exists
if (Schema::hasTable('fm_config')) {
    $rows = DB::table('fm_config')->where('code', 'like', '%delivery%')->orWhere('code', 'like', '%shipping%')->get();
     foreach ($rows as $r) {
        print_r($r);
    }
}
