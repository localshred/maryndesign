<?php

/* Generate CAPTCHA Image for Form Validation */

$str = substr(md5(microtime()*mktime()),0,5);
$captcha = imagecreatefrompng("images/random.png");
//make line colors
$black = imagecolorallocate($captcha, 0, 0, 0);
$line = imagecolorallocate($captcha,233,239,239);
imageline($captcha,0,0,39,29,$line);
imageline($captcha,40,0,64,29,$line);
imagestring($captcha, 5, 20, 10, $str, $black);
header("Content-Type: image/png");
imagepng($captcha);

?>
