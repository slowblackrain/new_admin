<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=================================================\n";
echo "       GOODS FILTER QUERY VERIFICATION\n";
echo "=================================================\n\n";

// 1. Seed Test Data for Filters
$providerSeq = 888;
DB::table('fm_provider')->updateOrInsert(['provider_seq' => $providerSeq], ['provider_name' => 'Test Provider']);
DB::table('fm_goods')->insertOrIgnore([
    'goods_seq' => 88888,
    'goods_name' => 'Filter Test Item',
    'goods_code' => 'FILTER_001',
    'provider_seq' => $providerSeq,
    // 'brand_code' => 'TESTBRAND', // Column missing
    'model' => 'TESTMODEL',
    'maker_name' => 'TESTMAKER',
    'origin_name' => 'TESTORIGIN',
    'regist_date' => now()
]);
DB::table('fm_goods_option')->insertOrIgnore([
    'goods_seq' => 88888,
    'price' => 50000,
    'default_option' => 'y'
]);


echo "Data Seeded. Please verify manually via UI or checking DB query logs.\n";
echo "Routes check:\n";
// Shell_exec to route list to confirm controller
passthru("php artisan route:list --path=admin/goods/catalog");
