<?php
use Illuminate\Support\Facades\DB;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$results = DB::table('fm_goods_suboption')->where('goods_seq', 210770)->get();
foreach ($results as $row) {
    echo "Title: " . $row->suboption_title . ", Option: " . $row->suboption . "\n";
}
