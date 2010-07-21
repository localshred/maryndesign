<?php

include_once(CLASSES.'db.cls.php');
class Section extends DB {
	var $arrAreas = array();
	var $err = array();
	var $msg = array();
	
	function Section() {
		$this->DB();
		$this->arrAreas = $this->getAreaArray();
	}//end constructor
	
	function getAll($showArea=NULL) {
		$sql = 'SELECT * FROM Sections';
		if (!is_null($showArea) && $showArea != '') {
			$sql .= ' WHERE Area = "'.$showArea.'"';
		}
		$sql .= ' ORDER BY Area DESC, Name';
		$this->runQuery($sql);
		if ($this->num_rows > 0) {
			return $this->buildResult();
		} else {
			$this->err[] = 'No '.$showArea.' Sections found.';
			if (!is_null($showArea) && $showArea != '') {
				return $this->getAll();
			} else {
				return false;
			}
		}
	}//end getAll()
	
	function get($sectionID) {
		if (!preg_match('/^[0-9]+$/',$sectionID) || $sectionID == 0) {
			$this->err[] = 'Please supply a valid section.';
			return false;
		}

		$sql = 'SELECT * FROM Sections WHERE SectionID = "'.$sectionID.'"';
		if ($this->justQuery($sql)) {
			return $this->nextObject();
		} else {
			$this->err[] = 'Failed to retrieve section info: '.mysql_error($this->hdl_db);
			return false;
		}
	}//end get()
	
	function add($name, $area, $description='') {
		if (!preg_match('/^[a-zA-Z\.\-_ ,\'"]+$/', $name)) {
			$this->err[] = 'Please supply a valid section name.';
			return false;
		}
		if (!in_array($area, $this->arrAreas)) {
			$this->err[] = 'Invalid area: (Valid Values include '.implode(', ', $this->arrAreas).').';
			return false;
		}
		
		$sql = '
			INSERT INTO Sections SET
				Name = "'.trim(addslashes($name)).'",
				Area = "'.$area.'",
				Description = "'.trim(addslashes($description)).'"';
		if ($this->justQuery($sql)) {
			$this->msg[] = 'Section added successfully.';
			return true;
		} else {
			$this->err[] = 'Failed to add section: '.mysql_error($this->hdl_db);
			return false;
		}
	}//end add()
	
	function update($sectionID, $newName, $newArea, $newDescription) {
		if (!preg_match('/^[0-9]+$/',$sectionID) || $sectionID == 0) {
			$this->err[] = 'Please supply a valid section.';
			return false;
		}
		if (!preg_match('/^[a-zA-Z\.\-_ ,\'"]+$/', $newName)) {
			$this->err[] = 'Please supply a valid section name.';
			return false;
		}
		if (!in_array($newArea, $this->arrAreas)) {
			$this->err[] = 'Invalid area: (Valid Values include '.implode(', ', $this->arrAreas).').';
			return false;
		}
		
		$sql = '
			UPDATE Sections SET
				Name = "'.trim(addslashes($newName)).'",
				Area = "'.$newArea.'",
				Description = "'.trim(addslashes($newDescription)).'"
			WHERE SectionID = "'.$sectionID.'"';
		if ($this->justQuery($sql)) {
			$this->msg[] = 'Sections added successfully.';
			return true;
		} else {
			$this->err[] = 'Failed to add sections: '.mysql_error($this->hdl_db);
			return false;
		}
	}//end update()
	
	function delete($sectionID) {
		if (!preg_match('/^[0-9]+$/',$sectionID) || $sectionID == 0) {
			$this->err[] = 'Please supply a valid section.';
			return false;
		}
		
		$sql = 'DELETE FROM Sections WHERE SectionID = "'.$sectionID.'"';
		if ($this->justQuery($sql)) {
			$this->msg[] = 'Section deleted successfully.';
			return true;
		} else {
			$this->err[] = 'Failed to delete section: '.mysql_error($this->hdl_db);
			return false;
		}
	}//end delete()

	function getAreaArray() {
		$sql = "SHOW COLUMNS FROM Sections LIKE 'Area'";
		$this->runQuery($sql);
		if ($this->num_rows > 0) {
			$objRow = $this->nextObject();
			$arrEnumRanges = explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2",$objRow->Type));
			sort($arrEnumRanges);
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