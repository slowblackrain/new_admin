<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
foreach($tables as $t) {
    $arr = (array)$t;
    $name = current($arr);
    if(strpos($name, 'dormancy') !== false) {
        echo "Found Table: " . $name . PHP_EOL;
        $schema = \Illuminate\Support\Facades\DB::select("DESCRIBE {$name}");
        foreach($schema as $col) {
            echo " - " . $col->Field . PHP_EOL;
        }
    }
}
