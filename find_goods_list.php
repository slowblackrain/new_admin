<?php
$file = 'c:/dometopia/legacy_source/app/models/goodsmodel.php';
$lines = file($file);
foreach ($lines as $i => $line) {
    if (stripos($line, 'function goods_list') !== false) {
        echo "Found at line " . ($i + 1) . ": $line";
        exit;
    }
}
echo "Not found.";
