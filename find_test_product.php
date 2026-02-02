<?php
require 'c:/dometopia/new_admin/vendor/autoload.php';
$app = require_once 'c:/dometopia/new_admin/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$product = DB::select("
    SELECT 
        g.goods_seq, 
        g.goods_name, 
        g.tot_stock, 
        s.stock as option_stock, 
        s.option_seq, 
        l.ea as scm_stock,
        l.wh_seq
    FROM fm_goods g 
    JOIN fm_goods_supply s ON g.goods_seq = s.goods_seq 
    LEFT JOIN fm_scm_location_link l ON g.goods_seq = l.goods_seq 
    WHERE g.goods_view='yes' 
    AND s.stock > 10 
    AND l.wh_seq = 1 
    LIMIT 1
");

print_r($product);
