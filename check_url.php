<?php
$url = 'http://127.0.0.1:8001/admin';
$headers = @get_headers($url);
if ($headers) {
    print_r($headers);
} else {
    echo "Failed to connect to $url\n";
    echo "Error: " . error_get_last()['message'];
}
