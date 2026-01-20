<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $rows = \Illuminate\Support\Facades\DB::table('fm_config')
        ->where('groupcd', 'member')
        ->whereIn('codecd', ['agreement', 'privacy', 'policy'])
        ->get();

    print_r($rows);

    // Also check generic 'basic' config just in case
    $basic = \Illuminate\Support\Facades\DB::table('fm_config')
        ->where('groupcd', 'basic')
        ->whereIn('codecd', ['shopName', 'companyName'])
        ->get();
    print_r($basic);

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
