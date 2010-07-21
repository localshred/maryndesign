<?php

include_once('dbClass.php');
$db = new dbClass();

$user = "admin";
$pass = "\$h@29rk";
$name = "BrownStone Administrator";
$accesslevel = '1';

$sql = "INSERT INTO AdminUsers (UserName,PassWord,Name,AccessLevel) VALUES ('$user',ENCODE('" . $pass . "','wH!t3W|za4D'),'$name','$accesslevel')";

$db->runQuery($sql);
	print "User added Successfully\n";


?>