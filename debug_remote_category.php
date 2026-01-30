<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;

// --- Config ---
$remoteHost = '49.247.170.176';
$remoteDb   = 'dometopia';
$remoteUser = 'dometopia';
$remotePass = '11dnjf7dlf!!';

echo "Connecting to Remote ($remoteHost)...\n";
try {
    $remotePdo = new PDO("mysql:host=$remoteHost;dbname=$remoteDb", $remoteUser, $remotePass);
    $remotePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $remotePdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Remote Connection Failed: " . $e->getMessage());
}

echo "Fetching sample categories...\n";
$stmt = $remotePdo->query("SELECT category_code, title FROM fm_category LIMIT 5");
$rows = $stmt->fetchAll();
print_r($rows);

echo "Checking link codes for 0001%...\n";
$stmt = $remotePdo->query("SELECT DISTINCT category_code FROM fm_category_link WHERE category_code LIKE '0001%' LIMIT 10");
$codes = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($codes);

if (!empty($codes)) {
    $first = $codes[0];
    echo "Checking if $first exists in fm_category...\n";
    $stmt = $remotePdo->prepare("SELECT * FROM fm_category WHERE category_code = ?");
    $stmt->execute([$first]);
    $cat = $stmt->fetch();
    if ($cat) {
        echo "Found: " . $cat['title'] . "\n";
    } else {
        echo "$first NOT FOUND in fm_category.\n";
    }
}
