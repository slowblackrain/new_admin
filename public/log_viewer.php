<?php
$logDir = __DIR__ . '/../storage/logs/';
$files = glob($logDir . '*.log');
if (empty($files)) {
    echo "No log files found.";
    exit;
}
usort($files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});
$logPath = $files[0];

$lines = file($logPath);
$output = [];
for ($i = count($lines) - 1; $i >= 0; $i--) {
    if (stripos($lines[$i], 'error') !== false || stripos($lines[$i], 'exception') !== false) {
        $output[] = $lines[$i];
    }
    if (count($output) > 20) break;
}
echo "Log file: " . basename($logPath) . "<br><br>";
echo implode("<br>", array_reverse($output));
