<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
$dbname = 'Tables_in_dometopia';

$found = false;
foreach($tables as $t) {
    $table = $t->$dbname;
    $columns = DB::select("SHOW COLUMNS FROM `{$table}`");
    
    foreach($columns as $col) {
        $colName = $col->Field;
        // Only search in string type columns
        if (strpos($col->Type, 'char') !== false || strpos($col->Type, 'text') !== false) {
            $results = DB::table($table)->where($colName, 'LIKE', '%toss%')->limit(1)->get();
            if (count($results) > 0) {
                echo "Found 'toss' in table '$table', column '$colName'\n";
                // print_r($results);
                $found = true;
            }
        }
    }
}
if (!$found) {
    echo "String 'toss' completely not found in any table.\n";
}
