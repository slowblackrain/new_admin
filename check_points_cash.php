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
} catch (PDOException $e) {
    die("Remote Connection Failed: " . $e->getMessage());
}

$userId = 'newjjang3';
echo "Checking Point/Cash Data for User: $userId\n";

// Get Remote Member Seq
$stmt = $remotePdo->query("SELECT member_seq, userid FROM fm_member WHERE userid = '$userId'");
$member = $stmt->fetch();

if (!$member) {
    die("Remote Member not found for $userId\n");
}

$memberSeq = $member['member_seq'];
echo "Remote Member Seq: $memberSeq\n";

// Check Emoney (Points)
$emoneyCount = $remotePdo->query("SELECT count(*) FROM fm_emoney WHERE member_seq = $memberSeq")->fetchColumn();
echo "Remote Emoney History Count: $emoneyCount\n";
if ($emoneyCount > 0) {
    echo "Sample Emoney Data:\n";
    $rows = $remotePdo->query("SELECT * FROM fm_emoney WHERE member_seq = $memberSeq ORDER BY emoney_seq DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
}

// Check Cash
$cashCount = $remotePdo->query("SELECT count(*) FROM fm_cash WHERE member_seq = $memberSeq")->fetchColumn();
echo "Remote Cash History Count: $cashCount\n";
if ($cashCount > 0) {
    echo "Sample Cash Data:\n";
    $rows = $remotePdo->query("SELECT * FROM fm_cash WHERE member_seq = $memberSeq ORDER BY cash_seq DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
}

// Check if tables exist by trying to select 1
try {
    $remotePdo->query("SELECT 1 FROM fm_cash LIMIT 1");
} catch (Exception $e) {
    echo "WARNING: fm_cash table might not exist or verify name.\n";
}

echo "Done.\n";
