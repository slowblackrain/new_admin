<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$goods = \App\Models\Goods::find(212612);
$arr = $goods->toArray();
foreach ($arr as $key => $val) {
    if (is_string($val) && strpos($val, '1387198') !== false) {
        echo "Found in goods: $key => $val\n";
    }
}
$link = \Illuminate\Support\Facades\DB::table('fm_goods_link')->where('goods_seq', 212612)->first();
if ($link) {
    foreach ((array)$link as $key => $val) {
        if (is_string($val) && strpos($val, '1387198') !== false) {
            echo "Found in link: $key => $val\n";
        }
    }
}
$provider = \Illuminate\Support\Facades\DB::table('fm_provider')->where('provider_seq', $goods->provider_seq)->first();
if ($provider) {
    foreach ((array)$provider as $key => $val) {
        if (is_string($val) && strpos($val, '1387198') !== false) {
            echo "Found in provider: $key => $val\n";
        }
    }
}
// What about fm_goods_option?
$opts = \Illuminate\Support\Facades\DB::table('fm_goods_option')->where('goods_seq', 212612)->get();
foreach ($opts as $opt) {
    foreach ((array)$opt as $key => $val) {
        if (is_string($val) && strpos($val, '1387198') !== false) {
            echo "Found in option: $key => $val\n";
        }
    }
}
