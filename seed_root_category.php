<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Force Insert Root ID 1
$exists = DB::table('fm_category')->where('id', 1)->exists();

if (!$exists) {
    echo "Inserting Root Category (ID: 1)...\n";
    DB::table('fm_category')->insert([
        'id' => 1,
        'parent_id' => 0,
        'level' => 0,
        'title' => 'ROOT',
        'position' => 0,
        'category_code' => 'ROOT',
        'hide' => '1',
        'regist_date' => now(),
        'update_date' => now()
    ]);
    echo "Root inserted.\n";
} else {
    echo "Root already exists.\n";
}
