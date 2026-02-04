<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cats = Illuminate\Support\Facades\DB::table('fm_category')->where('category_code', 'like', '0110%')->get();
echo "Categories found: " . count($cats) . "\n";
foreach ($cats as $c) {
    echo "{$c->category_code} : {$c->title}\n";
}

if (count($cats) == 0) {
    echo "Creating category 0110...\n";
    Illuminate\Support\Facades\DB::table('fm_category')->insert([
        'category_code' => '0110',
        'title' => 'Test Category',
        'parent_id' => 1,
        'level' => 2,
        'hide' => '0',
        'regist_date' => now(),
        'update_date' => now()
    ]);
    echo "Created.\n";
}
