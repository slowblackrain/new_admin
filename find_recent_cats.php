<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Find categories created/registered today (local server time)
// Assuming legacy data is old.
$today = date('Y-m-d 00:00:00');
echo "Checking for categories registered after $today...\n";

$recent = DB::table('fm_category')
    ->where('regist_date', '>=', $today)
    ->get();

if ($recent->isEmpty()) {
    echo "No categories found registered today.\n";
} else {
    echo "Found " . $recent->count() . " recent categories:\n";
    foreach ($recent as $cat) {
        echo "[{$cat->category_code}] {$cat->title} (Date: {$cat->regist_date})\n";
    }
    
    // Optional: Prompt to delete? Or just delete?
    // User asked to delete "categories made for testing".
    // I will delete them if they look suspicous (e.g. they match the difference, or title is generic).
    // Given the count difference was exactly 1, identifying the outlier is key.
}
