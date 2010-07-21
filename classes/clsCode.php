<?php
/*
include_once('dbClass.php');

class clsCode extends dbClass {
	//Properties
	var $CodeID;
	var $Type;
		
	//Methods
	function clsCode() {
		$this->dbClass();
	}//end constructor
	
	function getSnippetList($_Type = '') {
		$this->Type = $_Type;
		
		$sql = "
			SELECT c.*
			FROM Codes c
				LEFT JOIN CodeTypes";
	}	
	
	function getSnippet($_CodeID) {
		$this->CodeID = $_CodeID;
		$sql = "
			SELECT c.*
			FROM Codes c
			WHERE c.CodeID = '" . $this->CodeID . "'";
		return $this->runQuery($sql);
	}
	
	
	
}//end class
*/
?>