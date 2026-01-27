<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // 1. Check Schema (Charset)
    echo "=== SCHEMA ===\n";
    $schema = DB::select("SHOW CREATE TABLE fm_category");
    $createObj = current((array)$schema[0]); // Handle object result
    
    // The key is usually 'Create Table'
    if (isset($schema[0]->{'Create Table'})) {
        echo $schema[0]->{'Create Table'} . "\n\n";
    } else {
        // Fallback for array result
        $arr = (array)$schema[0];
        echo current($arr) . "\n\n";
    }

    // 2. Check Data
    echo "=== DATA SAMPLE ===\n";
    $categories = DB::table('fm_category')->limit(5)->get();
    
    foreach ($categories as $cat) {
        $props = (array)$cat;
        foreach ($props as $key => $val) {
            if (is_string($val)) {
                echo "$key: $val\n";
                // Only print encoding check if it looks multibyte
                if (strlen($val) != mb_strlen($val, 'UTF-8')) {
                    echo "  -> Valid UTF-8? " . (mb_check_encoding($val, 'UTF-8') ? 'YES' : 'NO') . "\n";
                }
            }
        }
        echo "----------------\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
