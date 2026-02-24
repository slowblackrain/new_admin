<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;

// --- Config ---
$remoteHost = '49.247.170.176';
$remoteDb   = 'dometopia';
$remoteUser = 'dometopia';
$remotePass = '11dnjf7dlf!!';

$localHost = '127.0.0.1';
$localDb   = 'dometopia';
$localUser = 'root';
$localPass = '1111';

// --- Connections ---
echo "Connecting to Remote ($remoteHost)...\n";
try {
    $remotePdo = new PDO("mysql:host=$remoteHost;dbname=$remoteDb", $remoteUser, $remotePass);
    $remotePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $remotePdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Remote Connection Failed: " . $e->getMessage());
}

echo "Connecting to Local ($localHost)...\n";
try {
    $localPdo = new PDO("mysql:host=$localHost;dbname=$localDb", $localUser, $localPass);
    $localPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $localPdo->exec("SET SESSION sql_mode = ''");
} catch (Exception $e) {
    die("Local Connection Failed: " . $e->getMessage());
}

function convertToUtf8($data) {
    if (is_array($data)) return array_map('convertToUtf8', $data);
    if (is_string($data)) return mb_convert_encoding($data, 'UTF-8', 'EUC-KR');
    return $data;
}

echo "Truncating local fm_category_link...\n";
$localPdo->exec("TRUNCATE TABLE fm_category_link");

echo "Fetching data from remote fm_category_link...\n";
$stmt = $remotePdo->query("SELECT * FROM fm_category_link");
$rows = $stmt->fetchAll();

echo "Found " . count($rows) . " rows. Inserting into local...\n";

if (count($rows) > 0) {
    $firstRow = $rows[0];
    $cols = array_keys($firstRow);
    $colsList = implode(", ", $cols);
    $valsList = implode(", ", array_fill(0, count($cols), "?"));
    
    $localPdo->beginTransaction();
    $insertStmt = $localPdo->prepare("INSERT INTO fm_category_link ($colsList) VALUES ($valsList)");
    
    $count = 0;
    foreach ($rows as $row) {
        $row = convertToUtf8($row);
        $insertStmt->execute(array_values($row));
        $count++;
        if ($count % 1000 == 0) echo "Inserted $count rows...\n";
    }
    $localPdo->commit();
}

echo "Restore Complete. Total inserted: $count\n";
