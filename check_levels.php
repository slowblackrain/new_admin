<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $levels = DB::table('fm_category')
        ->select('level', DB::raw('count(*) as count'))
        ->groupBy('level')
        ->orderBy('level')
        ->get();

    echo "Category Levels:\n";
    foreach ($levels as $l) {
        echo "Level {$l->level}: {$l->count} items\n";
    }

    // Check what is level 0 or 1
    $top = DB::table('fm_category')->where('level', '<=', 2)->limit(3)->get(['id', 'title', 'level', 'parent_id']);
    print_r($top);

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
