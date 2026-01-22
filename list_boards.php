<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$boards = DB::table('fm_boardmanager')->select('id', 'name')->get();
foreach ($boards as $b) {
    echo $b->id . ": " . $b->name . "\n";
}
