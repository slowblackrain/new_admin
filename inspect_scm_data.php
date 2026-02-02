<?php
require 'c:/dometopia/new_admin/vendor/autoload.php';
$app = require_once 'c:/dometopia/new_admin/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$scm_links = DB::table('fm_scm_location_link')->get();

foreach ($scm_links as $link) {
    echo "SCM Link Found:\n";
    print_r($link);

    $goods = DB::table('fm_goods')->where('goods_seq', $link->goods_seq)->first();
    if ($goods) {
        echo "Related Goods: " . $goods->goods_name . " (Seq: " . $goods->goods_seq . ", Stock: " . $goods->tot_stock . ")\n";
        
        $options = DB::table('fm_goods_option')->where('goods_seq', $goods->goods_seq)->get();
        echo "Options Count: " . $options->count() . "\n";
    } else {
        echo "Related Goods NOT FOUND for seq: " . $link->goods_seq . "\n";
    }
    echo "--------------------------------------------------\n";
}
