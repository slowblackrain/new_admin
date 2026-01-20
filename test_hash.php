<?php
$target = 'e6ff74d23117889aac90c8f25cbd24102d47c81991b99517f8da00c6b7c70963';
$candidates = ['1234', '1111', 'dometopia', 'password', '123456', 'admin'];

foreach ($candidates as $pass) {
    if (hash('sha256', $pass) === $target) {
        echo "MATCH FOUND: $pass (SHA-256)\n";
        exit;
    }
    if (hash('sha256', md5($pass)) === $target) {
        echo "MATCH FOUND: $pass (SHA-256 of MD5)\n";
        exit;
    }
}
echo "No simple match found.\n";
