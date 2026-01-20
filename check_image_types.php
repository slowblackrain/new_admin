<?php
use Illuminate\Support\Facades\DB;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$types = DB::table('fm_goods_image')->select('image_type')->distinct()->get();
foreach ($types as $t) {
    echo $t->image_type . "\n";
}
