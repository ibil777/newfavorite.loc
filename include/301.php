<?php

$URI = $_SERVER['REQUEST_URI'];
$URL = "http://".$_SERVER['HTTP_HOST'];
if($URI == '/???' or $URI == '/home.htm' or $URI == '/index.php' or $URI == '/index.htm') {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: ".$URL);
    exit;
}
if(strpos($URI, '/home.htm') !== false) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: ".$URL.str_replace('/home.htm', '', $URI));
    exit;
}
if(strpos($URI, '/index.php') !== false) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: ".$URL.str_replace('/index.php', '', $URI));
    exit;
}
if(strpos($URI, '?ps=0') !== false) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: ".$URL.str_replace('?ps=0', '', $URI));
    exit;
}



?>