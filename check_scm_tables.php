<?php
require 'c:\dometopia\new_admin\vendor\autoload.php';
$app = require_once 'c:\dometopia\new_admin\bootstrap\app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cols1 = DB::select("SHOW COLUMNS FROM fm_scm_order");
echo "fm_scm_order columns:\n";
foreach($cols1 as $c) echo $c->Field . ", ";
echo "\n\n";

$cols2 = DB::select("SHOW COLUMNS FROM fm_scm_order_goods");
echo "fm_scm_order_goods columns:\n";
foreach($cols2 as $c) echo $c->Field . ", ";
echo "\n\n";

$cols3 = DB::select("SHOW COLUMNS FROM fm_scm_autoorder_order");
echo "fm_scm_autoorder_order columns:\n";
foreach($cols3 as $c) echo $c->Field . ", ";
echo "\n\n";
