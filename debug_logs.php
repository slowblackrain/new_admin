<?php

use App\Models\Order;
use App\Models\OrderLog;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- Debugging Order Logs ---\n";

try {
    // 1. Check Table Existence
    $tables = DB::select("SHOW TABLES LIKE 'fm_order_log'");
    echo "Table 'fm_order_log' exists: " . (count($tables) > 0 ? "YES" : "NO") . "\n";

    if (count($tables) > 0) {
        // 2. Check Schema (Primary Key)
        $columns = DB::select("SHOW COLUMNS FROM fm_order_log");
        echo "Columns:\n";
        $hasId = false;
        foreach ($columns as $col) {
            echo " - " . $col->Field . " (" . $col->Type . ")\n";
            if ($col->Field == 'id' || $col->Key == 'PRI') $hasId = true;
        }

        // 3. Check Data Count
        $count = DB::table('fm_order_log')->count();
        echo "Total Log Count: " . $count . "\n";

        // 4. Test Model
        $order = Order::orderBy('order_seq', 'desc')->first();
        if ($order) {
            echo "Testing with Order: " . $order->order_seq . "\n";
            $logs = $order->logs;
            echo "Logs via Eloquent: " . $logs->count() . "\n";
            
            if ($logs->count() > 0) {
                echo "First Log Title: " . $logs->first()->title . "\n";
            }
        } else {
            echo "No orders found.\n";
        }

    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "--- End Debug ---\n";
