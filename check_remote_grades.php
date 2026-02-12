<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$remoteHost = env('DB_PROD_HOST');
$remoteDb   = env('DB_PROD_DATABASE');
$remoteUser = env('DB_PROD_USERNAME');
$remotePass = env('DB_PROD_PASSWORD');

try {
    $remotePdo = new PDO("mysql:host=$remoteHost;dbname=$remoteDb;charset=utf8", $remoteUser, $remotePass);
    $remotePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to Remote.\n";
    
    $stmt = $remotePdo->query("SELECT * FROM fm_member_group ORDER BY group_seq ASC");
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($groups as $g) {
        echo "Group Seq: {$g['group_seq']} | Name: {$g['group_name']} | Sale: " . ($g['sale_use'] == 'Y' ? $g['sale_price'] . ($g['sale_price_type']=='PER'?'%':'KRW') : 'None') . "\n";
    }

} catch (PDOException $e) {
    die("Remote Connection Failed: " . $e->getMessage());
}
