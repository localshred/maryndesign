<?php

/* Privacy Policy */
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');

// Tells PHP which functions to use when handling sessions (the ones we define below)
session_set_save_handler("SessionStart", "SessionEnd", "SessionRead", "SessionWrite", "SessionDestroy", "SessionGarbageCollect");

//Vars passed by the PHP Engine automatically :D
function SessionStart($session_save_path,$session_name) {
	//Connect to the DB
	include_once('dbClass.php');
	$db = new dbClass();
}//end SessionStart

function SessionEnd() {
	//no need to close the DB
	return 1;
}//end SessionEnd

function SessionRead($thesessionid) {
	$result = mysql_query("SELECT data FROM sessions WHERE sessionid = '".session_id()."' AND expires>".time());
	if(@mysql_num_rows($result)) {
		// Find a Current Session
		$var = mysql_fetch_array($result);
		$temp = $var[0];
		// Session Info is decoded automatically
		return $temp;
	}
	else {
		mysql_query("DELETE FROM sessions WHERE sessionid='".session_id()."'");
		global $wcsbvar;
		$expires = time() + ini_get('session.gc_maxlifetime');
		mysql_query("INSERT INTO sessions (sessionid, expires, data, lifetime) VALUES('".session_id()."', '$expires', '$wcsbvar', '" . ini_get('session.gc_maxlifetime') . "')");
		return "";
	}
}//end SessionRead

function SessionWrite($thesessionid,$wcsbvar) {
	mysql_query("UPDATE sessions SET expires=(" . time() . " + lifetime), data='$wcsbvar' WHERE sessionid='".session_id()."' AND expires>".time());
}//end SessionWrite

function SessionDestroy($thesessionid) {
	mysql_query("DELETE FROM sessions WHERE sessionid='".session_id()."'");
}//end SessionDestroy

function SessionGarbageCollect($lifetime) {
	mysql_query("DELETE FROM sessions WHERE expires<".time());
}//end SessionGarbageCollect

//Start session here, could be above, but not before session_set_save_handler
session_start();

?>
