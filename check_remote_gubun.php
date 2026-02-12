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
    
    echo "--- fm_member_gubun ---\n";
    $stmt = $remotePdo->query("SELECT * FROM fm_member_gubun");
    $gubuns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($gubuns as $g) {
        print_r($g);
    }

    echo "--- fm_member_group ---\n";
    $stmt = $remotePdo->query("SELECT * FROM fm_member_group");
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($groups as $g) {
        print_r($g);
    }

    // 3. Analyze Member Distribution (Correlation between mtype, gubun_seq, group_seq)
    echo "\n--- Member Distribution (Remote) ---\n";
    $sql = "SELECT mtype, gubun_seq, group_seq, count(*) as cnt FROM fm_member GROUP BY mtype, gubun_seq, group_seq ORDER BY cnt DESC LIMIT 20";
    $stmt = $remotePdo->query($sql);
    $distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($distribution);

    // 4. Check 'level' column existence
    echo "\n--- Check Columns in fm_member (Remote) ---\n";
    $stmt = $remotePdo->query("DESCRIBE fm_member");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if(in_array('level', $columns)) echo "Column 'level' EXISTS.\n";
    else echo "Column 'level' does NOT exist.\n";

} catch (PDOException $e) {
    die("Remote Connection Failed: " . $e->getMessage());
}
