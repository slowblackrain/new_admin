<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Category;
use Illuminate\Support\Facades\DB;

$code = '0001%';
echo "Repairing categories for link codes starting with 0001...\n";

// 1. Get all distinct category codes from link table
$links = DB::table('fm_category_link')
    ->where('category_code', 'like', $code)
    ->select('category_code')
    ->distinct()
    ->get();

$created = 0;
foreach ($links as $link) {
    if (strlen($link->category_code) > 16) continue; // Safety check

    $exists = Category::where('category_code', $link->category_code)->exists();
    
    if (!$exists) {
        $parentCode = substr($link->category_code, 0, strlen($link->category_code) - 4);
        $parentId = 0;
        
        if ($parentCode) {
            $parent = Category::where('category_code', $parentCode)->first();
            if ($parent) $parentId = $parent->id;
        }

        // Determine level
        $level = strlen($link->category_code) / 4;

        DB::table('fm_category')->insert([
            'category_code' => $link->category_code,
            'title' => 'Stub Category ' . $link->category_code,
            'parent_id' => $parentId,
            'level' => $level,
            'position' => 0,
            'hide' => '0', // Visible
            'hide_in_navigation' => '1',
        ]);
        $created++;
        echo "Created Stub Category: {$link->category_code}\n";
    }
}

echo "Repair Complete. Created $created stub categories.\n";
