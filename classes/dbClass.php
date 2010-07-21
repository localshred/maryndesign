<?php

class dbClass {
	//Properties
	var $hdlDB;
	var $Results;
	var $NumRows;

	//Methods
	function dbClass() {
		$this->hdlDB = mysql_connect('localhost','maryndb','ol0r!N') or die(mysql_error());
		mysql_select_db('maryn') or die(mysql_error());
	}//end constructor

	function runQuery($sql) {
		if ($_GET['debug'] == 'sql') {
			print "dbClass->runQuery <pre>" . $sql . "</pre>\n\n";
		}
		$this->Results = mysql_query($sql);
		$this->NumRows = mysql_num_rows($this->Results);
		return $this->Results;
	}//end runQuery

	function justQuery($sql) {
		if ($_GET['debug'] == 'sql') {
			print "dbClass->justQuery SQL: <pre>" . $sql . "</pre>\n\n";
		}
		return mysql_query($sql);
	}//end justQuery

	function getObject($sql) {
		if ($_GET['debug'] == 'sql') {
			print "dbClass->getObject SQL: <pre>" . $sql . "</pre>\n\n";
		}
		return mysql_fetch_object(mysql_query($sql));
	}//end getObject

	function nextObject($newsql='') {
		if ($newsql) {
			if ($_GET['debug'] == 'sql') {
				print "dbClass->nextObject SQL: <pre>" . $newsql . "</pre>\n\n";
			}
			return mysql_fetch_object($newsql);
		}
		else {
			if ($_GET['debug'] == 'sql') {
				print "dbClass->nextObject SQL: <pre>" . $newsql . "</pre>\n\n";
			}
			return mysql_fetch_object($this->Results);
		}
	}//end nextObject

	function nextArray($newsql='') {
		if ($newsql) {
			if ($_GET['debug'] == 'sql') {
				print "dbClass->nextArray SQL: <pre>" . $newsql . "</pre>\n\n";
			}
			return mysql_fetch_assoc($newsql);
		}
		else {
			if ($_GET['debug'] == 'sql') {
				print "dbClass->nextArray SQL: <pre>" . $newsql . "</pre>\n\n";
			}
			return mysql_fetch_assoc($this->Results);
		}
	}//end nextArray

	function dataSeek() {
		/* fetch rows in reverse order */
		for ($i = $this->numRows() - 1; $i >= 0; $i--) {
			if (!mysql_data_seek($this->Results, $i)) {
				echo "Cannot seek to row $i: " . mysql_error() . "\n";
				continue;
			}
			if (!($row = mysql_fetch_assoc($this->Results))) {
				continue;
			}
			$arrRows[] = $row['id'];
		}
		return $arrRows;
	}//end dataSeek

	function numRows() {
		return mysql_num_rows($this->Results);
	}//end numRows

	function affectedRows() {
		return mysql_affected_rows();
	}//end affectedRows

	function getID() {
		return mysql_insert_id();
	}//end getID

	function closeLink() {
		mysql_close($dbh);
	}//end closeLink

}//end class

?>
