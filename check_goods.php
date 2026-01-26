<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- fm_goods Schema ---\n";
$columns = DB::select("SHOW COLUMNS FROM fm_goods");
foreach ($columns as $col) echo $col->Field . " (" . $col->Type . ") " . $col->Extra . "\n";

echo "\n--- Check Goods Seq 0 ---\n";
$zero = DB::table('fm_goods')->where('goods_seq', 0)->first();
if ($zero) echo "Goods Seq 0 EXISTS.\n";
else echo "Goods Seq 0 does NOT exist.\n";

echo "Max Goods Seq: " . DB::table('fm_goods')->max('goods_seq') . "\n";
