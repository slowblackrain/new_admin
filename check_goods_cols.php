<?php
require 'c:\dometopia\new_admin\vendor\autoload.php';
$app = require_once 'c:\dometopia\new_admin\bootstrap\app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cols = DB::select("SHOW COLUMNS FROM fm_goods");
foreach($cols as $c) echo $c->Field . " ";
