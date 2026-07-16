<?php
$log = file_get_contents('C:\dometopia\new_admin\storage\logs\laravel.log');
// The log is UTF-16LE, so we need to encode it to UTF-8
$log_utf8 = mb_convert_encoding($log, 'UTF-8', 'UTF-16LE');

$lines = explode("\n", $log_utf8);
foreach (array_reverse($lines) as $line) {
    if (str_contains($line, 'Testing 002000260010')) {
        echo trim($line) . "\n";
        break;
    }
}
