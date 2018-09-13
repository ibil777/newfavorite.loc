<?php

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");              // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header("Cache-Control: no-cache, must-revalidate");            // HTTP/1.1
header("Pragma: no-cache");                                    // HTTP/1.0
header("Content-type: image/png");
session_start();

//readfile('secret_bg.png');


$im = @ImageCreateFromPNG('secret_bg.png');
$symbols = "1234567890";
srand((double)microtime() * 1000000);
$_SESSION['sess_secret'] = "";
for($i=0; $i<6; $i++)
    $_SESSION['sess_secret'] .= $symbols[rand(0, strlen($symbols)-1)];

for($i=0; $i<6; $i++) {
	$im_num = @ImageCreateFromPNG('secret_'.$_SESSION['sess_secret'][$i].'.png');
	imagecopyresampled($im, $im_num, $i*16, 0, 0, 0, 20, 30, 20, 30);
	ImageDestroy($im_num);
}
imagejpeg($im);


?>