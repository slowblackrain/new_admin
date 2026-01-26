<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;

$root = Category::find(1);
if ($root) {
    echo "Root exists: " . $root->title . "\n";
} else {
    echo "Root ID 1 does NOT exist.\n";
    // Check what exists
    echo "First 5 categories:\n";
    $cats = Category::orderBy('id')->limit(5)->get();
    foreach($cats as $c) {
        echo $c->id . " (" . $c->parent_id . ") " . $c->title . "\n";
    }
}
