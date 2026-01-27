<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Verify Category (Hangul)
$cat = DB::table('fm_category')->limit(3)->get();
echo "=== Category Sample ===\n";
foreach($cat as $c) {
    echo $c->title . " (Valid UTF-8: " . (mb_check_encoding($c->title, 'UTF-8') ? 'YES' : 'NO') . ")\n";
}

// Verify Provider (Hangul)
$prov = DB::table('fm_provider')->limit(3)->get();
echo "\n=== Provider Sample ===\n";
foreach($prov as $p) {
    echo $p->provider_name . " (Valid UTF-8: " . (mb_check_encoding($p->provider_name, 'UTF-8') ? 'YES' : 'NO') . ")\n";
}
