<?php
try {
    // Create M1 image (Valid 1000x1000)
    $img1 = imagecreatetruecolor(1000, 1000);
    $bg1 = imagecolorallocate($img1, 200, 0, 0); // Red
    imagefill($img1, 0, 0, $bg1);
    $fontColor = imagecolorallocate($img1, 255, 255, 255);
    imagestring($img1, 5, 450, 450, 'TEST M1', $fontColor);
    imagejpeg($img1, 'C:/dometopia/GTS12345_M1.jpg', 90); // ~50kb
    imagedestroy($img1);
    
    // Create M2 image (Valid 1000x1000)
    $img2 = imagecreatetruecolor(1000, 1000);
    $bg2 = imagecolorallocate($img2, 0, 0, 200); // Blue
    imagefill($img2, 0, 0, $bg2);
    imagestring($img2, 5, 450, 450, 'TEST M2', $fontColor);
    imagejpeg($img2, 'C:/dometopia/GTS12345_M2.jpg', 90); 
    imagedestroy($img2);

    // Create M3 image (Invalid Size: 500x500)
    $img3 = imagecreatetruecolor(500, 500);
    $bg3 = imagecolorallocate($img3, 0, 200, 0); // Green
    imagefill($img3, 0, 0, $bg3);
    imagestring($img3, 5, 200, 200, 'TEST M3 INVALID', $fontColor);
    imagejpeg($img3, 'C:/dometopia/GTS12345_M3.jpg', 90); 
    imagedestroy($img3);
    
    // Create S1 image (Invalid Type: S)
    copy('C:/dometopia/GTS12345_M1.jpg', 'C:/dometopia/GTS12345_S1.jpg');

    echo "Dummy images successfully generated in C:/dometopia/ : \n- GTS12345_M1.jpg\n- GTS12345_M2.jpg\n- GTS12345_M3.jpg\n- GTS12345_S1.jpg\n";
} catch(PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
