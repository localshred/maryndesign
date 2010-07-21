<?php

include_once(CLASSES.'db.cls.php');
class Setter extends DB {
	var $arrStatus = array();
	var $err = array();
	var $msg = array();
	
	function Setter() {
		$this->DB();
		$this->arrStatus = $this->getStatusArray();
	}//end constructor
	
	function getAll($show_inactive=false) {
		$sql = 'SELECT * FROM Setters';
		if (!$show_inactive) {
			$sql .= ' WHERE Status = "Active"';
		}
		$sql .= ' ORDER BY Status, Sort, Name';
		$this->runQuery($sql);
		if ($this->num_rows > 0) {
			return $this->buildResult();
		} else {
			$this->err[] = 'No Setters found.';
			return false;
		}
	}//end getAll()
	
	function get($setterID) {
		if (!preg_match('/^[0-9]+$/',$setterID) || $setterID == 0) {
			$this->err[] = 'Please supply a valid setter.';
			return false;
		}

		$sql = 'SELECT * FROM Setters WHERE SetterID = "'.$setterID.'"';
		if ($this->justQuery($sql)) {
			return $this->nextObject();
		} else {
			$this->err[] = 'Failed to retrieve setter info: '.mysql_error($this->hdl_db);
			return false;
		}
	}
	
	function add($name, $sort=0, $status='Active') {
		if (!preg_match('/^[a-zA-Z\.\-_ ,\'"]+$/', $name)) {
			$this->err[] = 'Please supply a valid setter name.';
			return false;
		}
		if (!preg_match('/^[0-9]+$/',$sort)) {
			$this->err[] = 'Please supply a valid sort (Must be numeric).';
			return false;
		}
		if (!in_array($status, $this->arrStatus)) {
			$this->err[] = 'Invalid Status change: Please call Active or Inactive.';
			return false;
		}
		
		$sql = '
			INSERT INTO Setters SET
				Name = "'.trim(addslashes($name)).'",
				Sort = "'.$sort.'",
				Status = "'.$status.'"';
		if ($this->justQuery($sql)) {
			$this->msg[] = 'Setter added successfully.';
			return true;
		} else {
			$this->err[] = 'Failed to add setter: '.mysql_error($this->hdl_db);
			return false;
		}
	}//end add()
	
	function update($setterID, $newName, $newSort=0) {
		if (!preg_match('/^[0-9]+$/',$setterID) || $setterID == 0) {
			$this->err[] = 'Please supply a valid setter.';
			return false;
		}
		if (!preg_match('/^[a-zA-Z\.\-_ ,\'"]+$/', $newName)) {
			$this->err[] = 'Please supply a valid setter name.';
			return false;
		}
		if (!preg_match('/^[0-9]+$/',$newSort)) {
			$this->err[] = 'Please supply a valid sort (Must be numeric).';
			return false;
		}
		
		$sql = '
			UPDATE Setters SET
				Name = "'.trim(addslashes($newName)).'",
				Sort = "'.$newSort.'"
			WHERE SetterID = "'.$setterID.'"';
		if ($this->justQuery($sql)) {
			$this->msg[] = 'Setter added successfully.';
			return true;
		} else {
			$this->err[] = 'Failed to add setter: '.mysql_error($this->hdl_db);
			return false;
		}
	}//end update()
	
	function delete($setterID) {
		if (!preg_match('/^[0-9]+$/',$setterID) || $setterID == 0) {
			$this->err[] = 'Please supply a valid setter.';
			return false;
		}
		
		$sql = 'DELETE FROM Setters WHERE SetterID = "'.$setterID.'"';
		if ($this->justQuery($sql)) {
			$this->msg[] = 'Setter deleted successfully.';
			return true;
		} else {
			$this->err[] = 'Failed to delete setter: '.mysql_error($this->hdl_db);
			return false;
		}
	}//end delete()
	
	function updateStatus($setterID,$status='Inactive') {
		if (!preg_match('/^[0-9]+$/',$setterID) || $setterID == 0) {
			$this->err[] = 'Please supply a valid setter.';
			return false;
		}
		if (!in_array($status, $this->arrStatus)) {
			$this->err[] = 'Invalid Status change: Please call Active or Inactive.';
			return false;
		}

		$sql = '
			UPDATE Setters SET
				Status = "'.$status.'"
			WHERE SetterID = "'.$setterID.'"';
		if ($this->justQuery($sql)) {
			$this->msg[] = 'Setter status updated to '.$status.' successfully.';
			return true;
		} else {
			$this->err[] = 'Update Failed: '.mysql_error($this->hdl_db);
			return false;
		}
	}//end updateStatus()
	
	function getStatusArray() {
		$sql = "SHOW COLUMNS FROM Setters LIKE 'Status'";
		$this->runQuery($sql);
		if ($this->num_rows > 0) {
			$objRow = $this->nextObject();
			$arrEnumRanges = explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2",$objRow->Type));
			return $arrEnumRanges;
		}
	}//end getStatusArray()
	
	function msgToSession($err='err',$msg='msg') {
		if (is_array($_SESSION[$err])) {
			$_SESSION[$err] = array_merge($_SESSION[$err], $this->err);
		} else {
			$_SESSION[$err] = $this->err;
		}
		
		if (is_array($_SESSION[$msg])) {
			$_SESSION[$msg] = array_merge($_SESSION[$msg], $this->msg);
		} else {
			$_SESSION[$msg] = $this->msg;
		}
	}//end msgToSession
}

?>