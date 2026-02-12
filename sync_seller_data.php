<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;

// --- Config ---
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$remoteHost = $_ENV['DB_PROD_HOST'];
$remoteDb   = $_ENV['DB_PROD_DATABASE'];
$remoteUser = $_ENV['DB_PROD_USERNAME'];
$remotePass = $_ENV['DB_PROD_PASSWORD'];
$remotePort = $_ENV['DB_PROD_PORT'];

$localHost = $_ENV['DB_HOST'];
$localDb   = $_ENV['DB_DATABASE'];
$localUser = $_ENV['DB_USERNAME'];
$localPass = $_ENV['DB_PASSWORD'];
$localPort = $_ENV['DB_PORT'];

// --- Connections ---
echo "Connecting to Remote ($remoteHost)...\n";
try {
    $remotePdo = new PDO("mysql:host=$remoteHost;port=$remotePort;dbname=$remoteDb", $remoteUser, $remotePass);
    $remotePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $remotePdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Remote Connection Failed: " . $e->getMessage());
}

echo "Connecting to Local ($localHost)...\n";
try {
    $localPdo = new PDO("mysql:host=$localHost;port=$localPort;dbname=$localDb", $localUser, $localPass);
    $localPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $localPdo->exec("SET SESSION sql_mode = ''");
} catch (Exception $e) {
    die("Local Connection Failed: " . $e->getMessage());
}

// --- Helper Functions ---
function convertToUtf8($data) {
    if (is_array($data)) return array_map('convertToUtf8', $data);
    if (is_string($data)) return mb_convert_encoding($data, 'UTF-8', 'EUC-KR');
    return $data;
}

function upsert($pdo, $table, $data, $pk) {
    if (empty($data)) return;
    $data = convertToUtf8($data);

    // Filter out fields that don't exist in local DB (if schema differs) - simplistic check
    // For now, assume identical schema or ignore errors (try-catch)
    
    $cols = array_keys($data);
    $colsList = implode(", ", $cols);
    $valsList = implode(", ", array_fill(0, count($cols), "?"));
    
    $updateParts = [];
    foreach ($cols as $col) {
        if ($col === $pk) continue;
        $updateParts[] = "$col = VALUES($col)";
    }
    $updateClause = implode(", ", $updateParts);
    
    $sql = "INSERT INTO $table ($colsList) VALUES ($valsList) ON DUPLICATE KEY UPDATE $updateClause";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($data));
    } catch (Exception $e) {
        echo "Upsert failed for $table: " . $e->getMessage() . "\n";
        // Ignore specific column errors if needed
    }
}

// --- TARGET USER ---
$targetUserId = 'newjjang3';
echo "Syncing data for user: $targetUserId\n";

// 1. Sync fm_member
echo "1. Syncing fm_member...\n";
$stmt = $remotePdo->prepare("SELECT * FROM fm_member WHERE userid = ?");
$stmt->execute([$targetUserId]);
$member = $stmt->fetch();

if (!$member) {
    die("User $targetUserId not found in remote DB.\n");
}
upsert($localPdo, 'fm_member', $member, 'member_seq');
$memberSeq = $member['member_seq'];
echo " - Member synced (seq: $memberSeq)\n";

// 2. Sync fm_provider
echo "2. Syncing fm_provider...\n";
// Provider usually linked by provider_id = userid OR member_seq
$stmt = $remotePdo->prepare("SELECT * FROM fm_provider WHERE provider_id = ?");
$stmt->execute([$targetUserId]);
$provider = $stmt->fetch();

if (!$provider) {
    // Try by member_seq ?? usually provider_id matches userid
    die("Provider for $targetUserId not found in remote DB.\n");
}
upsert($localPdo, 'fm_provider', $provider, 'provider_seq');
$providerSeq = $provider['provider_seq'];
echo " - Provider synced (seq: $providerSeq)\n";

// 3. Sync fm_goods (Products)
echo "3. Syncing fm_goods...\n";
$stmt = $remotePdo->prepare("SELECT * FROM fm_goods WHERE provider_seq = ?");
$stmt->execute([$providerSeq]);
$goodsList = $stmt->fetchAll();
echo " - Found " . count($goodsList) . " products.\n";

foreach ($goodsList as $goods) {
    upsert($localPdo, 'fm_goods', $goods, 'goods_seq');
    $gSeq = $goods['goods_seq'];
    
    // Options
    $stmtOpt = $remotePdo->prepare("SELECT * FROM fm_goods_option WHERE goods_seq = ?");
    $stmtOpt->execute([$gSeq]);
    foreach ($stmtOpt->fetchAll() as $opt) upsert($localPdo, 'fm_goods_option', $opt, 'option_seq');
    
    // Supply
    $stmtSup = $remotePdo->prepare("SELECT * FROM fm_goods_supply WHERE goods_seq = ?");
    $stmtSup->execute([$gSeq]);
    foreach ($stmtSup->fetchAll() as $sup) upsert($localPdo, 'fm_goods_supply', $sup, 'supply_seq'); // pk might vary
    
    // Images
    $stmtImg = $remotePdo->prepare("SELECT * FROM fm_goods_image WHERE goods_seq = ?");
    $stmtImg->execute([$gSeq]);
    foreach ($stmtImg->fetchAll() as $img) upsert($localPdo, 'fm_goods_image', $img, 'image_seq');
}

// 4. Sync Orders
echo "4. Syncing Orders...\n";
// Find order items for this provider
$stmt = $remotePdo->prepare("SELECT * FROM fm_order_item WHERE provider_seq = ?");
$stmt->execute([$providerSeq]);
$items = $stmt->fetchAll();
echo " - Found " . count($items) . " order items.\n";

$orderSeqs = [];
foreach ($items as $item) {
    upsert($localPdo, 'fm_order_item', $item, 'item_seq');
    $orderSeqs[$item['order_seq']] = true;
    
    // Item Options
    $stmtOpt = $remotePdo->prepare("SELECT * FROM fm_order_item_option WHERE item_seq = ?");
    $stmtOpt->execute([$item['item_seq']]);
    foreach ($stmtOpt->fetchAll() as $opt) upsert($localPdo, 'fm_order_item_option', $opt, 'item_option_seq');
}

// Sync Parent Orders
$uniqueOrderSeqs = array_keys($orderSeqs);
echo " - Syncing " . count($uniqueOrderSeqs) . " parent orders.\n";
foreach ($uniqueOrderSeqs as $oSeq) {
    $stmtOrd = $remotePdo->prepare("SELECT * FROM fm_order WHERE order_seq = ?");
    $stmtOrd->execute([$oSeq]);
    $order = $stmtOrd->fetch();
    if ($order) {
        upsert($localPdo, 'fm_order', $order, 'order_seq');
    }
}

// 5. Syncing Settlements (fm_account)
echo "5. Syncing Settlements (fm_account)...\n";
try {
    $rows = $remotePdo->query("SELECT * FROM fm_account WHERE provider_seq = $providerSeq")->fetchAll(PDO::FETCH_ASSOC);
    if ($rows) {
        echo " - Found " . count($rows) . " settlement records.\n";
        foreach ($rows as $row) {
            upsert($localPdo, 'fm_account', $row, 'account_seq');
        }
    } else {
        echo " - Found 0 settlement records.\n";
    }
} catch (Exception $e) {
    echo " - Skipping fm_account (table might not exist or error): " . $e->getMessage() . "\n";
}

// 5.1 Syncing Emoney (fm_emoney)
echo "5.1 Syncing Emoney (fm_emoney)...\n";
try {
    // memberSeq is fetched at step 1
    $rows = $remotePdo->query("SELECT * FROM fm_emoney WHERE member_seq = $memberSeq")->fetchAll(PDO::FETCH_ASSOC);
    if ($rows) {
        echo " - Found " . count($rows) . " emoney records.\n";
        foreach ($rows as $row) {
            upsert($localPdo, 'fm_emoney', $row, 'emoney_seq');
        }
    } else {
        echo " - Found 0 emoney records.\n";
    }
} catch (Exception $e) {
    echo " - Skipping fm_emoney: " . $e->getMessage() . "\n";
}

// 5.2 Syncing Cash (fm_cash)
echo "5.2 Syncing Cash (fm_cash)...\n";
try {
    $rows = $remotePdo->query("SELECT * FROM fm_cash WHERE member_seq = $memberSeq")->fetchAll(PDO::FETCH_ASSOC);
    if ($rows) {
        echo " - Found " . count($rows) . " cash records.\n";
        foreach ($rows as $row) {
            upsert($localPdo, 'fm_cash', $row, 'cash_seq');
        }
    } else {
        echo " - Found 0 cash records.\n";
    }
} catch (Exception $e) {
    echo " - Skipping fm_cash: " . $e->getMessage() . "\n";
}

// 5.5 Re-fetch Local Provider Seq (Important if Local seq differs from Remote due to existing ID)
$localProvider = $localPdo->query("SELECT provider_seq FROM fm_provider WHERE provider_id = '$targetUserId'")->fetch();
if ($localProvider) {
    if ($providerSeq != $localProvider['provider_seq']) {
        echo " - Notice: Local provider_seq ({$localProvider['provider_seq']}) differs from Remote ($providerSeq). Using Local.\n";
        $providerSeq = $localProvider['provider_seq'];
    }
}

// 6. Reset Password to '1111' locally
echo "6. Resetting local password for verification...\n";
try {
    // SellerUserProvider uses MD5(plain)
    $newPass = md5('1111');
    
    // fm_provider uses 'provider_passwd'
    $localPdo->prepare("UPDATE fm_provider SET provider_passwd = ? WHERE provider_seq = ?")->execute([$newPass, $providerSeq]);
    
    // fm_member: check column name. usually 'password' or 'passwd'. 
    // Let's try both or check first.
    // Try 'password' first.
    try {
        $localPdo->prepare("UPDATE fm_member SET password = ? WHERE member_seq = ?")->execute([$newPass, $memberSeq]);
    } catch (Exception $e) {
        // Try 'passwd'
        try {
             $localPdo->prepare("UPDATE fm_member SET passwd = ? WHERE member_seq = ?")->execute([$newPass, $memberSeq]);
        } catch (Exception $e2) {
             echo " - Member password update failed (column name?): " . $e2->getMessage() . "\n";
        }
    }
    
    echo " - Password reset to '1111' (MD5) for provider_passwd.\n";
} catch (Exception $e) {
    echo " - Password reset failed: " . $e->getMessage() . "\n";
}

// --- DEBUG & DISCOVERY ---
echo "--- DEBUG INFO ---\n";
$localCount = $localPdo->query("SELECT count(*) FROM fm_provider")->fetchColumn();
echo "Local fm_provider count: $localCount\n";

// Fetch by ID
$pById = $localPdo->query("SELECT * FROM fm_provider WHERE provider_id = '$targetUserId'")->fetch();
if ($pById) {
    echo "Found local provider by ID '$targetUserId': Seq " . $pById['provider_seq'] . "\n";
    $providerSeq = $pById['provider_seq']; // Update seq if changed
} else {
    echo "NOT FOUND local provider by ID '$targetUserId'.\n";
    // Fetch last
    $last = $localPdo->query("SELECT * FROM fm_provider ORDER BY provider_seq DESC LIMIT 1")->fetch();
    echo "Last Local Provider: Seq " . ($last['provider_seq']??'N/A') . ", ID " . ($last['provider_id']??'N/A') . "\n";
}

// Fetch provider details using (possibly updated) seq
if (isset($providerSeq)) {
    $p = $localPdo->query("SELECT * FROM fm_provider WHERE provider_seq = $providerSeq")->fetch();
    if ($p) {
        echo "Provider Seq: " . $p['provider_seq'] . "\n";
        echo "Provider Status: " . $p['provider_status'] . "\n";
        // Force status
        if ($p['provider_status'] != '1') {
            echo "Updating status to '1'...\n";
            $localPdo->prepare("UPDATE fm_provider SET provider_status = '1' WHERE provider_seq = ?")->execute([$providerSeq]);
        }
    }
}

$count = $remotePdo->query("SELECT count(*) FROM fm_goods WHERE provider_seq = $providerSeq")->fetchColumn();
echo "Remote fm_goods count for provider_seq $providerSeq: $count\n";

// Check by reg_id - REMOVED (Column doesn't exist)
$countReg = 0; 
// echo "Remote fm_goods count for reg_id '$targetUserId': $countReg\n";

if ($count == 0) {
    echo "!!! User '$targetUserId' has NO goods in fm_goods (provider_seq match).\n";
    // Find a provider WITH goods
    echo "Finding a provider with most goods for suggestion...\n";
    $sql = "SELECT p.provider_id, p.provider_name, count(g.goods_seq) as cnt 
            FROM fm_provider p 
            JOIN fm_goods g ON p.provider_seq = g.provider_seq 
            GROUP BY p.provider_seq 
            ORDER BY cnt DESC LIMIT 1";
    try {
        $topProvider = $remotePdo->query($sql)->fetch();
        if ($topProvider) {
            echo "Suggestion: Use provider '{$topProvider['provider_id']}' ({$topProvider['provider_name']}) which has {$topProvider['cnt']} goods.\n";
        }
    } catch (Exception $e) {
        echo "Failed to find suggestion: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
