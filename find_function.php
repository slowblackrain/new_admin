<?php
$file = 'c:/dometopia/legacy_source/app/models/goodsmodel.php';
$content = file_get_contents($file);
$methods = ['function get_goods_suboption', 'function get_goods_input'];
foreach ($methods as $method) {
    $pos = strpos($content, $method);
    if ($pos !== false) {
        $line = substr_count(substr($content, 0, $pos), "\n") + 1;
        echo "$method Found at line: " . $line . "\n";
    } else {
        echo "$method Not found.\n";
    }
}
