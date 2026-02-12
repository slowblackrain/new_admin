<?php
// sync_banner_missing.php

$files = [
    // Left Banner
    [
        'url' => 'https://dometopia.com/data/skin/beauty/main/img/main_top_left2.jpg',
        'path' => 'public/images/legacy/main/main_top_left2.jpg'
    ],
    // Middle Banners (B1-B4)
    [
        'url' => 'https://dometopia.com/data/skin/beauty/images/main/main_top_B1.jpg',
        'path' => 'public/images/legacy/main/main_top_B1.jpg'
    ],
    [
        'url' => 'https://dometopia.com/data/skin/beauty/images/main/main_top_B2.jpg',
        'path' => 'public/images/legacy/main/main_top_B2.jpg'
    ],
    [
        'url' => 'https://dometopia.com/data/skin/beauty/images/main/main_top_B3.jpg',
        'path' => 'public/images/legacy/main/main_top_B3.jpg'
    ],
    [
        'url' => 'https://dometopia.com/data/skin/beauty/images/main/main_top_B4.jpg',
        'path' => 'public/images/legacy/main/main_top_B4.jpg'
    ]
];

foreach ($files as $file) {
    if (!file_exists(dirname($file['path']))) {
        mkdir(dirname($file['path']), 0777, true);
    }
    
    $content = file_get_contents($file['url']);
    if ($content !== false) {
        file_put_contents($file['path'], $content);
        echo "Downloaded: {$file['path']} (" . strlen($content) . " bytes)\n";
    } else {
        echo "Failed to download: {$file['url']}\n";
    }
}
