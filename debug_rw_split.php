<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$seq = 182128;

echo "--- Connection Config ---\n";
// Manually inspect the config
$conf = config('database.connections.mysql');
echo "Read Host: " . $conf['read']['host'] . "\n";
echo "Write Host: " . $conf['write']['host'] . "\n";

echo "\n--- Testing READ Connection ---\n";
// To force read, we can just use select (usually)
$readCount = DB::table('fm_goods')->where('goods_seq', $seq)->count();
echo "Default (likely Read) Count: $readCount\n";

echo "\n--- Testing WRITE Connection ---\n";
// To force write, we need to bypass the standard connection or inspect the internal PDOs if verified.
// Laravel doesn't easily expose "force write for select" without 'useWriteConnection' on query builder (if available) or raw PDO.

try {
    // Determine which connection validation uses during POST.
    // Usually validation uses the default connection. 
    // If we are in a transaction, it uses write.
    
    // Let's try to establish a raw PDO to the Write Host explicitly
    $writePdo = new PDO(
        "mysql:host={$conf['write']['host']};dbname={$conf['write']['database']};port={$conf['port']}",
        $conf['write']['username'],
        $conf['write']['password']
    );
    
    $stmt = $writePdo->query("SELECT count(*) FROM fm_goods WHERE goods_seq = $seq");
    $writeParamsCount = $stmt->fetchColumn();
    echo "Explicit WRITE Host Count: $writeParamsCount\n";
    
} catch (\Exception $e) {
    echo "Failed to connect to Write Host manually: " . $e->getMessage() . "\n";
}
