<?php
$file = 'c:\dometopia\new_admin\storage\logs\laravel.log';
$handle = fopen($file, "r");
fseek($handle, -2000, SEEK_END);
echo fread($handle, 2000);
fclose($handle);
