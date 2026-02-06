<?php
$file = 'c:\dometopia\new_admin\storage\logs\laravel.log';
$handle = fopen($file, "r");
fseek($handle, -50000, SEEK_END);
echo fread($handle, 50000);
fclose($handle);
