<?php
use Illuminate\Support\Facades\DB;
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Check fm_goods_export for delivery codes
    $exports = DB::table('fm_goods_export')
        ->select('delivery_company_code', DB::raw('count(*) as cnt'))
        ->groupBy('delivery_company_code')
        ->orderBy('cnt', 'desc')
        ->limit(20)
        ->get();

    echo "=== Export Courier Codes (fm_goods_export) ===\n";
    foreach($exports as $e) {
        echo "Code: " . $e->delivery_company_code . " | Count: " . $e->cnt . "\n";
    }
    
    // Check fm_code for 'code0', 'code1', or specific courier names
    $deliveryCodes = DB::table('fm_code')
        ->where('codecd', 'like', 'code%') 
        ->orWhere('value', 'like', '%택배%')
        ->orWhere('value', 'like', '%통운%')
        ->orWhere('value', 'like', '%우체국%')
        ->orWhere('value', 'like', '%로지스%')
        ->orderBy('codecd')
        ->get();

    echo "\n=== fm_code Advanced Matches ===\n";
    foreach($deliveryCodes as $d) {
         echo "CodeCD: " . $d->codecd . " | Value: " . mb_convert_encoding($d->value, 'UTF-8', 'auto') . "\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
