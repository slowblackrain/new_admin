<?php
$file = 'c:\dometopia\new_admin\storage\logs\laravel.log';
$handle = fopen($file, "r");
$size = filesize($file);
$readSize = min($size, 1024 * 1024); // 1MB
fseek($handle, -$readSize, SEEK_END);
$content = fread($handle, $readSize);
fclose($handle);

$matches = [];
preg_match_all('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] (local|production)\.ERROR: (.*?)(?=\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]|$)/s', $content, $matches);

$lastErrors = array_slice($matches[0], -3);
foreach ($lastErrors as $error) {
    echo substr($error, 0, 500) . "...\n----------------\n";
}
