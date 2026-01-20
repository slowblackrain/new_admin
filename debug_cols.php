<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$cols = \Illuminate\Support\Facades\DB::select('SHOW COLUMNS FROM fm_category');
foreach ($cols as $c) {
    echo $c->Field . "\n";
}
