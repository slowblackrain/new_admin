<?php
$sourcePath = '/home/dmtusr/main_top_right2_raw.jpg';
$destPath = '/home/dmtusr/new_admin/public/main_top_right2.jpg';

if (!file_exists($sourcePath)) {
    echo "Source file $sourcePath does not exist yet.\n";
    exit;
}

echo "Resizing $sourcePath to $destPath...\n";

// Increase memory limit for processing massive 31MB image
ini_set('memory_limit', '1024M');

$src = imagecreatefromjpeg($sourcePath);
if (!$src) {
    echo "Failed to load JPEG image.\n";
    exit;
}

$width = imagesx($src);
$height = imagesy($src);
echo "Original dimensions: {$width}x{$height}\n";

$targetW = 291;
$targetH = 194;

$dst = imagecreatetruecolor($targetW, $targetH);
// Resize
imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetW, $targetH, $width, $height);

// Save as highly compressed lightweight jpeg
if (imagejpeg($dst, $destPath, 90)) {
    echo "SUCCESS! Compressed image saved at $destPath (Size: " . round(filesize($destPath)/1024, 2) . " KB)\n";
    // Delete raw temp file to clean up server space
    unlink($sourcePath);
    echo "Deleted raw temp file.\n";
} else {
    echo "FAILED to save image.\n";
}
