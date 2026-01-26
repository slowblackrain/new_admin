<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Scm\ScmWarehouse;
use App\Models\Scm\ScmStore;

echo "Checking ScmWarehouse & ScmStore...\n";

try {
    // 1. Create a Test Warehouse
    $wh = ScmWarehouse::create([
        'wh_name' => 'Test Warehouse Local',
        'wh_group' => 'Test Group',
        'wh_regist_date' => now(),
    ]);
    echo "[SUCCESS] Created Warehouse: {$wh->wh_seq} - {$wh->wh_name}\n";

    // 2. Create a Test Store
    $store = ScmStore::create([
        'store_name' => 'Test Store Local',
        'store_type' => 'online',
        'regist_date' => now(),
    ]);
    echo "[SUCCESS] Created Store: {$store->store_seq} - {$store->store_name}\n";

    // 3. Verify Read
    $readWh = ScmWarehouse::find($wh->wh_seq);
    if ($readWh && $readWh->wh_name === 'Test Warehouse Local') {
        echo "[SUCCESS] Verified Warehouse Read\n";
    } else {
        echo "[FAIL] Warehouse Read Failed\n";
    }

} catch (\Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}
