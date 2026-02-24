<?php
// Download dummy images via file_get_contents to bypass local GD and PS quote issues
file_put_contents('C:/dometopia/GTS12345_M1.jpg', file_get_contents('https://dummyimage.com/1000x1000/ff0000/ffffff.jpg&text=TEST+M1'));
file_put_contents('C:/dometopia/GTS12345_M2.jpg', file_get_contents('https://dummyimage.com/1000x1000/0000ff/ffffff.jpg&text=TEST+M2'));
file_put_contents('C:/dometopia/GTS12345_S1.jpg', file_get_contents('https://dummyimage.com/1000x1000/00ff00/ffffff.jpg&text=TEST+S1'));
file_put_contents('C:/dometopia/GTS12345_M3.jpg', file_get_contents('https://dummyimage.com/500x500/000000/ffffff.jpg&text=TEST+M3'));

// Setup test product
$goods = \App\Models\Goods::where('goods_code', 'GTS12345')->first();
if ($goods) {
    $goods->update(['goods_view' => 'notLook']);
} else {
    $goods = \App\Models\Goods::create([
        'goods_code' => 'GTS12345',
        'goods_scode' => 'GTS12345',
        'goods_name' => 'Test Image Batch Goods',
        'goods_view' => 'notLook'
    ]);
}
\App\Models\GoodsImage::where('goods_seq', $goods->goods_seq)->delete();
echo "Test images and product 'GTS12345' are successfully provisioned.\n";
