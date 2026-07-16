<?php
$logPath = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logPath)) {
    $lines = file($logPath);
    // get last 50 lines that contain "ERROR"
    $output = [];
    for ($i = count($lines) - 1; $i >= 0; $i--) {
        if (stripos($lines[$i], 'error') !== false || stripos($lines[$i], 'exception') !== false) {
            $output[] = $lines[$i];
        }
        if (count($output) > 20) break;
    }
    echo implode("", array_reverse($output));
} else {
    echo "Log file not found.";
}
