<?php

include_once('clsSession.php');
$ScriptName = $_SERVER['SCRIPT_NAME'];

if ($ScriptName == '/admin/login.php' && !$_SESSION['UserName']) {
	//do nothing
}
elseif ($ScriptName == '/admin/login.php' && $_SESSION['UserName']) {
	header("Location: /admin/index.php");
}
elseif (!$_SESSION['UserName']) {
	header("Location: /admin/login.php");
}

?>
