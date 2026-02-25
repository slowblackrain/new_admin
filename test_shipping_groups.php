<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$prefixes = ['GKD', 'GTD', 'GTS', 'GDR', 'GDF', 'GKM', 'GUS'];
$results = [];

foreach ($prefixes as $p) {
    $goods = DB::table('fm_goods')->where('goods_scode', 'like', $p.'%')->first();
    if ($goods) {
        $results[] = [
            'prefix' => $p,
            'scode' => $goods->goods_scode,
            'shipping_policy' => $goods->shipping_policy,
            'goods_shipping_policy' => $goods->goods_shipping_policy,
            'postpaid_delivery_cost_yn' => $goods->postpaid_delivery_cost_yn,
            'unlimit_shipping_price' => $goods->unlimit_shipping_price,
            'provider_seq' => $goods->provider_seq,
        ];
    }
}

print_r($results);
