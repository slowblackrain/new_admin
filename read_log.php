<?php
$lines = file('C:\dometopia\new_admin\storage\logs\laravel.log');
foreach (array_reverse($lines) as $line) {
    if (str_contains($line, 'Testing 002000260010')) {
        echo trim($line) . "\n";
        break;
    }
}
