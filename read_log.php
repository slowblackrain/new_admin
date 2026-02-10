<?php
$file = __DIR__ . '/storage/logs/laravel.log';
if (!file_exists($file)) die("File not found\n");
$lines = file($file);
$lastErrorIndex = -1;
for ($i = count($lines) - 1; $i >= 0; $i--) {
    if (strpos($lines[$i], '.ERROR:') !== false) {
        $lastErrorIndex = $i;
        break;
    }
}

if ($lastErrorIndex != -1) {
    $slice = array_slice($lines, $lastErrorIndex, 15);
    foreach ($slice as $line) {
         $enc = mb_detect_encoding($line, ['UTF-8', 'EUC-KR', 'CP949'], true);
         if ($enc && $enc !== 'UTF-8') {
             echo mb_convert_encoding($line, 'UTF-8', $enc);
         } else {
             echo $line;
         }
    }
} else {
    echo "No error found in last lines.";
}

