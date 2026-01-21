<?php
$dir = 'C:/dometopia/legacy_source';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        if (preg_match('/function\s*get_shop_key/', $content, $matches)) {
            echo "Found in: " . $file->getPathname() . "\n";
            // Print context
            $lines = file($file->getPathname());
            foreach ($lines as $i => $line) {
                if (strpos($line, 'function get_shop_key') !== false) {
                    echo "Line " . ($i+1) . ": " . trim($line) . "\n";
                    // Print next few lines
                    for ($j = 1; $j <= 10; $j++) {
                        echo "Line " . ($i+1+$j) . ": " . trim($lines[$i+$j]) . "\n";
                    }
                    break;
                }
            }
            exit;
        }
    }
}
echo "Not found.\n";
