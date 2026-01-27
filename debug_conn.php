<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Goods;

echo "Default Connection: " . config('database.default') . "\n";
echo "Goods Model Connection: " . (new Goods())->getConnectionName() . "\n";

// Raw SQL Check on Default Connection
$res = DB::connection()->select("SELECT count(*) as cnt FROM fm_goods WHERE goods_seq = 182128");
echo "Raw SQL Count on Default: " . $res[0]->cnt . "\n";

// Validation Check
$v = Validator::make(['goods_seq' => 182128], ['goods_seq' => 'exists:mysql.fm_goods,goods_seq']); 
// Trying strict connection spec
if ($v->fails()) {
    echo "Explicit Connection Validation Failed.\n";
} else {
    echo "Explicit Connection Validation Passed.\n";
}
