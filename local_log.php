<?php
$logPath = 'c:\dometopia\new_admin\storage\logs\laravel.log';
if (file_exists($logPath)) {
    $lines = file($logPath);
    $output = [];
    for ($i = count($lines) - 1; $i >= 0; $i--) {
        if (stripos($lines[$i], 'error') !== false || stripos($lines[$i], 'exception') !== false || stripos($lines[$i], 'stack') !== false) {
            $output[] = $lines[$i];
        }
        if (count($output) > 20) break;
    }
    echo implode("", array_reverse($output));
} else {
    echo "Log file not found.";
}
