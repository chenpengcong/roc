<?php


if (!function_exists('isHttps')) {
    function isHttps()
    {
        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        }
        return false;
    }
}

if (!function_exists('to10')) {
    //$base进制转10进制
    function to10($num, $base = 62)
    {
        $charList = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $cnt = strlen($num);
        $result = 0;
        for ($i = 0;$i < $cnt;$i++) {
            $char = strpos($charList, $num[$i]); 
            $result = $result * $base + $char;
        }
        return $result;
    }
}


if (!function_exists('toBase')) {
    //10进制转$base进制
    function toBase($num, $base = 62)
    {
        $charList = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $remainder = $num % $base;
        $quotient = floor($num / $base);
        $result = $charList[$remainder];
        while ($quotient) {
            $remainder = $quotient % $base;
            $result = $charList[$remainder] . $result;
            $quotient = floor($quotient / $base);
        }
        return $result;
    }
}