<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$t1 = DB::select("SHOW CREATE TABLE fm_offer");
print_r($t1);

$t2 = DB::select("SHOW CREATE TABLE fm_cash");
print_r($t2);

$t3 = DB::select("SHOW CREATE TABLE fm_member");
// Only show columns for member to avoid spam
$cols = DB::select("DESCRIBE fm_member");
foreach($cols as $c){
    if(in_array($c->Field, ['member_seq', 'cash', 'user_id', 'group_seq'])) {
        print_r($c);
    }
}
