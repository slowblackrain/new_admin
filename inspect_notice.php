<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$post = \Illuminate\Support\Facades\DB::table('fm_boarddata')
    ->where('boardid', 'notice')
    ->orderBy('seq', 'desc')
    ->first();

print_r($post);
