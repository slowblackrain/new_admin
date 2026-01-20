<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $counts = \Illuminate\Support\Facades\DB::table('fm_boarddata')
        ->select('boardid', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
        ->groupBy('boardid')
        ->get();

    if ($counts->isEmpty()) {
        echo "Table fm_boarddata is EMPTY." . PHP_EOL;
    } else {
        foreach ($counts as $row) {
            echo "Board: {$row->boardid}, Count: {$row->total}" . PHP_EOL;
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
