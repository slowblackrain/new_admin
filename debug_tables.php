<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Debug Information ===\n";

// 1. Check Provider Details
$provider = DB::table('fm_provider')->where('provider_id', 'dometopia')->first();
echo "Provider: " . json_encode($provider) . "\n";

if ($provider) {
    $member = DB::table('fm_member')->where('userid', $provider->userid)->first();
    echo "Member (by userid '{$provider->userid}'): " . ($member ? "Found (seq: {$member->member_seq})" : "NOT FOUND") . "\n";
}

// 2. List All Tables (Limit 50 to avoid flooding, but look for 'board' or 'notice')
echo "\n[Table Search]\n";
$tables = DB::select('SHOW TABLES');
$found = 0;
foreach($tables as $t) {
    // access first property regardless of name
    $tableName = array_values((array)$t)[0];
    
    // Primitive keyword search
    if (strpos($tableName, 'board') !== false || strpos($tableName, 'notice') !== false) {
         echo "- [MATCH] $tableName\n";
         $found++;
    }
}

if ($found === 0) {
    echo "No tables matching 'board' or 'notice' found.\n";
    echo "First 10 tables:\n";
    $count = 0;
    foreach($tables as $t) {
        $tableName = array_values((array)$t)[0];
        echo "- $tableName\n";
        if(++$count >= 10) break;
    }
}
