<?php
$file = 'c:\dometopia\new_admin\storage\logs\laravel.log';
$handle = fopen($file, "r");
fseek($handle, -10000, SEEK_END);
echo fread($handle, 10000);
fclose($handle);
