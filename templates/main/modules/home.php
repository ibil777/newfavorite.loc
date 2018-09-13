<?php
$res_str = make_home_page($res_str);

function make_home_page($text) {
    global $ini_arr, $content_arr;
    $res_str = "";
    
    $res_str .= $text;

    return $res_str;
}
?>