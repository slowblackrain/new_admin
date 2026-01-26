<?php

use App\Models\OrderLog;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$targetOrder = '20260121073200666';
echo "--- Seeding Logs for $targetOrder ---\n";

OrderLog::create([
    'order_seq' => $targetOrder,
    'type' => 'process',
    'actor' => 'System',
    'title' => '검증용 로그',
    'detail' => '이 로그가 보이면 성공입니다.',
    'regist_date' => now(),
    'mtype' => 's',
    'mseq' => 0
]);

echo "Log created.\n";
