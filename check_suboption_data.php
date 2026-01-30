<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$data = DB::table('fm_goods_suboption')->where('goods_seq', 206718)->get();

if ($data->isEmpty()) {
    echo "No suboptions found for 206718\n";
} else {
    echo "Found " . $data->count() . " suboptions:\n";
    foreach ($data as $row) {
        echo "ID: " . $row->suboption_seq . ", Title: " . $row->suboption_title . ", Suboption: " . $row->suboption . ", Price: " . $row->price . "\n";
    }
}
