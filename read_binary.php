<?php
$content = file_get_contents('C:/dometopia/legacy_source/app/helpers/basic_helper.php', false, null, 0, 100);
echo bin2hex($content);
echo "\n";
echo $content;
