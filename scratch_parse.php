<?php
$html = file_get_contents('scrap_dometopia.html');
$dom = new DOMDocument();
@$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
$xpath = new DOMXPath($dom);
$main_wrap = $xpath->query('//div[@id="main-wrap"]')->item(0);
if ($main_wrap) {
    file_put_contents('tmp_main_wrap.html', $dom->saveHTML($main_wrap));
}
$right_scroll = $xpath->query('//div[@id="rightScrollLayer"]')->item(0);
if ($right_scroll) {
    file_put_contents('tmp_right_scroll.html', $dom->saveHTML($right_scroll));
}
echo "Extraction complete.";
