<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Delete MKS Test Product
$deleted = DB::table('fm_goods')->where('goods_scode', 'MKS-001')->delete();
echo "Deleted Products: $deleted\n";

// Delete Category 0110 (only if verified it was created by me, but safer to just delete if it matches Title 'Test Category')
$catDeleted = DB::table('fm_category')->where('category_code', '0110')->where('title', 'Test Category')->delete();
echo "Deleted Categories: $catDeleted\n";
