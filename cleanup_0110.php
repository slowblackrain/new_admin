<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Delete the specific test category
$deleted = DB::table('fm_category')->where('category_code', '0110')->where('title', 'Browser Verify Category')->delete();
echo "Deleted $deleted category (Browser Verify Category).\n";

if ($deleted > 0) {
    echo "Re-migrating to fetch real 0110 category...\n";
    passthru('php c:/dometopia/new_admin/migrate_legacy.php');
} else {
    echo "Target category not found or already deleted.\n";
}
