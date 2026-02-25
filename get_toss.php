<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$toss = DB::table('fm_config')->where('codecd', 'toss')->first();
$cker = DB::table('fm_config')->where('codecd', 'cker')->first();

echo "=== TOSS CONFIG ===\n";
if ($toss) echo $toss->value;
else echo "Not found.";

echo "\n\n=== CKER CONFIG ===\n";
if ($cker) echo $cker->value;
else echo "Not found.";
