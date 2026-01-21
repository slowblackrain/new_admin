<?php
require 'C:/dometopia/new_admin/vendor/autoload.php';
$app = require_once 'C:/dometopia/new_admin/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Searching for 'key' in codecd ---\n";
$rows = Illuminate\Support\Facades\DB::select("SELECT * FROM fm_config WHERE codecd LIKE '%key%' OR codecd LIKE '%encrypt%'");
print_r($rows);

echo "--- Searching for 'shop_key' ---\n";
$rows2 = Illuminate\Support\Facades\DB::select("SELECT * FROM fm_config WHERE codecd = 'shop_key'");
print_r($rows2);

echo "--- Dumping 'system' group ---\n";
$rows3 = Illuminate\Support\Facades\DB::select("SELECT * FROM fm_config WHERE groupcd = 'system'");
print_r($rows3);
