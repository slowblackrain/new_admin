<?php
$content = file_get_contents('c:/dometopia/new_admin/resources/views/front/goods/view.blade.php');
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (strpos($line, 'changeQty') !== false || strpos($line, 'removeOption') !== false) {
        echo ($i + 1) . ": " . trim($line) . "\n";
    }
}
