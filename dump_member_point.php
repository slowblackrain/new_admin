<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = Illuminate\Support\Facades\DB::table('fm_member')->first();
print_r([
    'emoney' => $user->emoney,
    'point' => $user->point,
    'cash' => $user->cash ?? 'null'
]);
