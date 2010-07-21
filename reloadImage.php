<?php

include_once('classes/clsSecureImage.php');
$pathToImg = str_replace(basename($_SERVER['SCRIPT_FILENAME']),'',$_SERVER['SCRIPT_FILENAME']);
$seedstr = substr(preg_replace('/(i|I|l|1|o|O|0)+/','',md5(microtime()*mktime())),0,5);
$validImage = new PWImage(str_replace('admin/','',$pathToImg) . "/images/random.png", $seedstr, 2, array(0, 0, 0), array(255, 128, 200));
$_SESSION['secureForm'] = $seedstr;

print "<img src=\"" . ($_GET['which'] == 'admin' ? '../' : './') . "images/random.png?" . time() . "\" alt=\"Verify the contents of this image in the box provided above to successfully submit this form\" />";
?>
