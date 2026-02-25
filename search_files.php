<?php
$dir = new RecursiveDirectoryIterator('c:\dometopia\legacy_source\\');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.(php|ini|txt|json)$/i', RecursiveRegexIterator::GET_MATCH);

$searchFor = ['t_clientId', 'test_ck_', 'cker', 'clientId'];

foreach($files as $file) {
    if (strpos($file[0], '\\cache\\') !== false || strpos($file[0], '\\cach\\') !== false) continue;
    
    $content = file_get_contents($file[0]);
    foreach ($searchFor as $term) {
        if (strpos($content, $term) !== false) {
            echo "Found '$term' in: " . $file[0] . "\n";
            // Just print the line
            $lines = explode("\n", $content);
            foreach ($lines as $i => $line) {
                if (strpos($line, $term) !== false) {
                    echo "  Line " . ($i+1) . ": " . trim($line) . "\n";
                }
            }
        }
    }
}
echo "Search completed.\n";
