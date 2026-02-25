<?php
$file = 'c:\dometopia\legacy_source\app\helpers\common_helper.php';
$content = file_get_contents($file);

// Fix $array{$index} to $array[$index]
// Actually, it's easier to just regex replace string access
$content = preg_replace('/\$([a-zA-Z0-9_]+)\{([^}]+)\}/', '$$1[$2]', $content);

file_put_contents($file, $content);
echo "Fixed common_helper.php syntax errors.\n";
