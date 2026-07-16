<?php
file_put_contents(__DIR__ . '/dump.txt', print_r($_POST, true) . print_r($_FILES, true) . print_r($_SERVER, true));
echo "Dumped";
