<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cols = DB::select('SHOW COLUMNS FROM fm_provider');
foreach($cols as $c) echo $c->Field . "\n";
