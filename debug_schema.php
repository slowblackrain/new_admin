<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$sellers = DB::table('fm_member')->limit(5)->get();
foreach ($sellers as $s) {
    echo "ID: {$s->userid}, Seq: {$s->member_seq}, Name: {$s->user_name}, Group: {$s->group_seq}\n";
}
