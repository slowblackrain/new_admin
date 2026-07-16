<?php
$file = 'storage/logs/laravel.log';
if (file_exists($file)) {
    $lines = file($file);
    $count = count($lines);
    echo "Total lines: $count\n";
    $printed = 0;
    // Scan backwards
    for ($i = $count - 1; $i >= 0; $i--) {
        if (strpos($lines[$i], 'Cart Controller Actual DB Config') !== false || 
            strpos($lines[$i], 'Cart Index Request') !== false) {
            
            // Print surrounding lines
            echo "Line $i: " . $lines[$i];
            for ($j = 1; $j <= 5; $j++) {
                if (isset($lines[$i + $j])) {
                    echo "  +" . $j . ": " . $lines[$i + $j];
                }
            }
            echo "---------------------------------\n";
            $printed++;
            if ($printed >= 10) break;
        }
    }
} else {
    echo "laravel.log not found\n";
}
