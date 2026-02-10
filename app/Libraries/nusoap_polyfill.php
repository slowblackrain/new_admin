<?php

// Polyfill for deprecated functions removed in PHP 8
// nusoap.php uses ereg, eregi, split

if (!function_exists('ereg')) {
    function ereg($pattern, $string, &$regs = []) {
        return preg_match('/'.addcslashes($pattern, '/').'/', $string, $regs);
    }
}

if (!function_exists('eregi')) {
    function eregi($pattern, $string, &$regs = []) {
        return preg_match('/'.addcslashes($pattern, '/').'/i', $string, $regs);
    }
}

if (!function_exists('split')) {
    function split($pattern, $string, $limit = -1) {
        return preg_split('/'.addcslashes($pattern, '/').'/', $string, $limit);
    }
}

if (!function_exists('get_magic_quotes_gpc')) {
    function get_magic_quotes_gpc() {
        return false;
    }
}
