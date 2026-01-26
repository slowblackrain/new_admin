<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\GoodsController;
use Illuminate\Http\Request;

echo "Debugging Option Update...\n";

// Setup
$optSeq = 1;
$goodsSeq = 1;

// Ensure Supply doesn't exist to test INSERT (or does exist to test UPDATE)
// Let's delete supply for opt 1
DB::table('fm_goods_supply')->where('option_seq', $optSeq)->delete();

$controller = new GoodsController();
$request = Request::create('/admin/goods/update_options', 'POST', [
    'options' => [
        $optSeq => [
            'price' => '12,345',
            'stock' => '99'
        ]
    ]
]);

// Override input to be sure
$request->merge([
    'options' => [
        $optSeq => [
            'price' => '12,345',
            'stock' => '99'
        ]
    ]
]);

// Mock Session
$session = new \Illuminate\Session\Store(
    'test',
    new \Illuminate\Session\ArraySessionHandler(10)
);
$request->setLaravelSession($session);
$app->instance('request', $request);

echo "Input: " . print_r($request->input('options'), true) . "\n";

try {
    $response = $controller->updateOptions($request);
    
    // Check Session for errors
    $session = $request->session(); 
    // CLI request might not have session persistence same way.
    // usage of back()->with() puts data in session flasher.
    // In CLI, getting that is hard without session middleware.
    
    // Just check DB
    $opt = DB::table('fm_goods_option')->where('option_seq', $optSeq)->first();
    echo "DB Price: {$opt->price}\n";

    $sup = DB::table('fm_goods_supply')->where('option_seq', $optSeq)->first();
    if ($sup) {
        echo "DB Stock: {$sup->stock}\n";
    } else {
        echo "DB Supply NOT FOUND.\n";
    }

} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
