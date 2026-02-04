<?php
use Illuminate\Support\Facades\DB;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$options = DB::table('fm_goods_option')->where('goods_seq', 210770)->get();
foreach ($options as $opt) {
    echo "Seq: " . $opt->option_seq . ", Price: " . $opt->price . ", Consumer: " . $opt->consumer_price . ", Default: " . $opt->default_option . "\n";
}
