<?php
error_reporting(E_ALL);

define('PAGE_LENGTH',30);			// Number of rows to display per page
define('EXPIRATION_LENGTH',120);	// Number of days to default a route's expiration to

include_once(CLASSES.'db.cls.php');
class Route extends DB {
	var $length;
	var $expiration_length;
	var $arrTypes = array();
	var $arrWhere = array();
	var $err = array();
	var $msg = array();
	
	function Route() {
		$this->DB();
		$this->arrTypes = $this->getTypeArray();
		$this->length = PAGE_LENGTH;
		$this->expiration_length = EXPIRATION_LENGTH;
	}//end constructor
	
	function getAll($arrSearch=NULL) {
		$this->arrWhere = array();
		if (isset($arrSearch) && !is_null($arrSearch) && is_array($arrSearch)) {
		### SetterID ###
			if (!is_null($arrSearch['setterID'])) {
				$this->arrWhere[] = 'st.SetterID = "'.$arrSearch['setterID'].'"';
				if (!is_null($arrSearch['keywords']) && preg_match('/^[a-zA-Z0-9 \-_\'"\.,]+$/', $arrSearch['keywords'])) {
					$txt = ' OR LOWER(st.Name) LIKE "%'.strtolower($arrSearch['keywords']).'%"';
					$this->addExpression($txt);
				}
			} elseif (!is_null($arrSearch['keywords']) && preg_match('/^[a-zA-Z0-9 \-_\'"\.,]+$/', $arrSearch['keywords'])) {
				$this->arrWhere['keywords'][] = 'LOWER(st.Name) LIKE "%'.strtolower($arrSearch['keywords']).'%"';
			}
			
		### SectionID ###
			if (!is_null($arrSearch['sectionID'])) {
				$this->arrWhere[] = 'r.SectionID = "'.$arrSearch['sectionID'].'"';
				if (!is_null($arrSearch['keywords']) && preg_match('/^[a-zA-Z0-9 \-_\'"\.,]+$/', $arrSearch['keywords'])) {
					$txt = ' OR LOWER(sc.Name) LIKE "%'.strtolower($arrSearch['keywords']).'%"';
					$this->addExpression($txt);
				}
			} elseif (!is_null($arrSearch['keywords']) && preg_match('/^[a-zA-Z0-9 \-_\'"\.,]+$/', $arrSearch['keywords'])) {
				$this->arrWhere['keywords'][] = 'LOWER(sc.Name) LIKE "%'.strtolower($arrSearch['keywords']).'%"';
			}
			
		### Type ###
			if (!is_null($arrSearch['type'])) {
				$this->arrWhere[] = 'r.Type = "'.$arrSearch['type'].'"';
				if (!is_null($arrSearch['keywords']) && preg_match('/^('.strtolower(implode('|', $this->arrTypes)).')$/', strtolower($arrSearch['keywords']))) {
					$txt = ' OR LOWER(r.Type) LIKE "%'.strtolower($arrSearch['keywords']).'%"';
					$this->addExpression($txt);
				}
			} elseif (!is_null($arrSearch['keywords']) && preg_match('/^('.strtolower(implode('|', $this->arrTypes)).')$/', strtolower($arrSearch['keywords']))) {
				$this->arrWhere['keywords'][] = 'LOWER(r.Type) LIKE "%'.strtolower($arrSearch['keywords']).'%"';
			}
			
		### Grade ###
			if (!is_null($arrSearch['grade'])) {
				$this->arrWhere[] = 'LOWER(r.Grade) LIKE "%'.strtolower($arrSearch['grade']).'%"';
				if (!is_null($arrSearch['keywords']) && preg_match('/^((5\.)?\d{1,2}[a-d+\-]?|V?\d{1,2})$/', strtolower($arrSearch['keywords']))) {
					$txt = ' OR LOWER(r.Grade) LIKE "%'.strtolower($arrSearch['keywords']).'%"';
					$this->addExpression($txt);
				}
			} elseif (!is_null($arrSearch['keywords']) && preg_match('/^((5\.)?\d{1,2}[a-d+\-]?|V?\d{1,2})$/', strtolower($arrSearch['keywords']))) {
				$this->arrWhere['keywords'][] = 'LOWER(r.Grade) LIKE "%'.strtolower($arrSearch['keywords']).'%"';
			}
			
		### Created Date, Before ###
			if (!is_null($arrSearch['createdBefore'])) {
				$created_date = preg_replace('/(\d{1,2}-\d{1,2})-(\d{4})/', "$2-$1", $arrSearch['createdBefore']);
				$this->arrWhere[] = 'r.Created <= "'.$created_date.'"';
			}
			
		### Created Date, On ###
			if (!is_null($arrSearch['createdOn'])) {
				$created_date = preg_replace('/(\d{1,2}-\d{1,2})-(\d{4})/', "$2-$1", $arrSearch['createdOn']);
				$this->arrWhere[] = 'r.Created = "'.$created_date.'"';
			}
			
		### Created Date, After ###
			if (!is_null($arrSearch['createdAfter'])) {
				$created_date = preg_replace('/(\d{1,2}-\d{1,2})-(\d{4})/', "$2-$1", $arrSearch['createdAfter']);
				$this->arrWhere[] = 'r.Created >= "'.$created_date.'"';
			}
			
		### Expire Date, Before ###
			if (!is_null($arrSearch['expiresBefore'])) {
				$expire_date = preg_replace('/(\d{1,2}-\d{1,2})-(\d{4})/', "$2-$1", $arrSearch['expiresBefore']);
				$this->arrWhere[] = 'r.Expires <= "'.$expire_date.'"';
			}
			
		### Expire Date, On ###
			if (!is_null($arrSearch['expiresOn'])) {
				$expire_date = preg_replace('/(\d{1,2}-\d{1,2})-(\d{4})/', "$2-$1", $arrSearch['expiresOn']);
				$this->arrWhere[] = 'r.Expires = "'.$expire_date.'"';
			}
			
		### Expire Date, After ###
			if (!is_null($arrSearch['expiresAfter'])) {
				$expire_date = preg_replace('/(\d{1,2}-\d{1,2})-(\d{4})/', "$2-$1", $arrSearch['expiresAfter']);
				$this->arrWhere[] = 'r.Expires >= "'.$expire_date.'"';
			}
			
		### Stripped ###
			if (!is_null($arrSearch['stripped'])) {
				$this->arrWhere[] = 'r.Stripped IS NULL';
				$this->arrWhere[] = 'r.Expires < NOW()';
			}
		}

		$sql = '
			SELECT
				r.*,
				DATE_FORMAT(r.Created, "%m-%d-%Y") as Created,
				DATE_FORMAT(r.Expires, "%m-%d-%Y") as Expires,
				sc.Name as SectionName,
				st.Name as SetterName
			FROM Routes r
				LEFT JOIN Sections sc
					ON sc.SectionID = r.SectionID
				LEFT JOIN Setters st
					ON st.SetterID = r.SetterID'."\n";
		if (count($this->arrWhere) > 0) {
			$sql .= 'WHERE ';
			foreach($this->arrWhere as $key => $where) {
				if ($key !== 'keywords') {
					$sql .= $where."\n";
					if ($this->arrWhere[count($this->arrWhere)-1] != $where) {
						$sql .= ' AND ';
					}
				} elseif (is_array($where) && count($where) > 0) {
					$sql .= '(';
					foreach ($where as $kword) {
						$sql .= $kword."\n";
						if ($this->arrWhere['keywords'][count($this->arrWhere['keywords'])-1] != $kword) {
							$sql .= ' OR ';
						}
					}
					$sql .= ')';
				}
			}
		} else {
			$sql .= 'WHERE r.stripped IS NULL';
		}
		$sql .= "\n".'ORDER BY r.Expires DESC, r.Type DESC, r.SectionID, r.Grade, r.Color';
		
//		$this->msg[] = '<pre>'.print_r($sql,true).'</pre>';
		
		$this->runQuery($sql);
		if ($this->num_rows > 0) {
			return $this->buildResult();
		} else {
			if (count($this->arrWhere) > 0) {
				$this->err[] = 'No Routes found matching that search criteria.';
				return false;
			} else {
				$this->err[] = 'No Routes Found.';
				return false;
			}
		}
	}//end getAll()
	
	function get($routeID) {
		if (!preg_match('/^[0-9]+$/',$routeID) || $routeID == 0) {
			$this->err[] = 'Please supply a valid route to update.';
			return false;
		}

		$sql = 'SELECT * FROM Routes WHERE RouteID = "'.$routeID.'"';
		if ($this->justQuery($sql)) {
			return $this->nextObject();
		} else {
			$this->err[] = 'Failed to retrieve section info: '.mysql_error($this->hdl_db);
			return false;
		}
	}//end get()
	
	function add($setterID, $sectionID, $type, $grade, $color, $created=NULL, $expires=NULL) {
		if (!preg_match('/^[0-9]+$/',$setterID) || $setterID == 0) {
			$this->err[] = 'Please supply a valid setter.';
			return false;
		} elseif (!$this->checkSetter($setterID)) {
			$this->err[] = 'Setter doesn\'t exist.';
			return false;
		}
		if (!preg_match('/^[0-9]+$/',$sectionID) || $sectionID == 0) {
			$this->err[] = 'Please supply a valid section.';
			return false;
		} elseif (!$this->checkSection($sectionID)) {
			$this->err[] = 'Section doesn\'t exist.';
			return false;
		}
		if (!in_array($type, $this->arrTypes)) {
			$this->err[] = 'Invalid route type: (Valid types include '.implode(', ', $this->arrTypes).').';
			return false;
		}
		if ((!preg_match('/^5\.\d{1,2}[a-d+\-]?$/', $grade) && $type != 'Boulder') ||
			(!preg_match('/^V\d{1,2}$/', $grade) && $type == 'Boulder')) {
			$this->err[] = 'Please supply a valid '.$type.' grade.';
			return false;
		}
		if (trim($color) == '') {
			$this->err[] = 'Please supply a valid route color.';
			return false;
		}
		if (!is_null($created) && !preg_match('/^20\d{2}-\d{1,2}-\d{1,2}$/', $created)) {
			$this->err[] = 'Please supply a valid creation date.';
			return false;
		} elseif (is_null($created)) {
			$created_date = 'NOW()';
		} elseif (preg_match('/^20\d{2}-\d{1,2}-\d{1,2}$/', $created)) {
			$created_date = '"'.$created.'"';
		} else {
			$this->err[] = 'Creation Date is in an unknown format.';
			return false;
		}
		if (!is_null($expires) && !preg_match('/^\d+$/', $expires) && !preg_match('/^20\d{2}-\d{1,2}-\d{1,2}$/', $expires)) {
			$this->err[] = 'Please supply a valid expiration date or interval.';
			return false;
		} elseif (is_null($expires)) {
			$expires_date = 'NOW() + INTERVAL '.$this->expiration_length.' DAY';
		} elseif (preg_match('/^\d+$/', $expires)) {
			$expires_date = 'NOW() + INTERVAL '.$expires.' DAY';
		} elseif (preg_match('/^20\d{2}-\d{1,2}-\d{1,2}$/', $expires)) {
			$expires_date = '"'.$expires.'"';
		} else {
			$this->err[] = 'Expiration Date is in an unknown format.';
			return false;
		}
		
		$sql = '
			INSERT INTO Routes SET
				SetterID = "'.$setterID.'",
				SectionID = "'.$sectionID.'",
				Type = "'.$type.'",
				Grade = "'.$grade.'",
				Color = "'.trim(addslashes($color)).'",
				Created = '.$created_date.',
				Expires = '.$expires_date;
		if ($this->justQuery($sql)) {
			$this->msg[] = 'Route added successfully.';
			return true;
		} else {
			$this->err[] = 'Failed to add route: '.mysql_error($this->hdl_db);
			return false;
		}
	}//end add()
	
	function update($routeID, $setterID, $sectionID, $type, $grade, $color, $created=NULL, $expires=NULL) {
		if (!preg_match('/^[0-9]+$/',$routeID) || $routeID == 0) {
			$this->err[] = 'Please supply a valid route to update.';
			return false;
		}
		if (!preg_match('/^[0-9]+$/',$setterID) || $setterID == 0) {
			$this->err[] = 'Please supply a valid setter.';
			return false;
		} elseif (!$this->checkSetter($setterID)) {
			$this->err[] = 'Setter doesn\'t exist.';
			return false;
		}
		if (!preg_match('/^[0-9]+$/',$sectionID) || $sectionID == 0) {
			$this->err[] = 'Please supply a valid section, add.';
			return false;
		} elseif (!$this->checkSection($sectionID)) {
			$this->err[] = 'Section doesn\'t exist.';
			return false;
		}
		if (!in_array($type, $this->arrTypes)) {
			$this->err[] = 'Invalid route type: (Valid types include '.implode(', ', $this->arrTypes).').';
			return false;
		}
		if ((!preg_match('/^5\.\d{1,2}[a-d+\-]?$/', $grade) && $type != 'Boulder') ||
			(!preg_match('/^V\d{1,2}$/', $grade) && $type == 'Boulder')) {
			$this->err[] = 'Please supply a valid '.$type.' grade.';
			return false;
		}
		if ($color == '') {
			$this->err[] = 'Please supply a valid route color.';
			return false;
		}
		if (!is_null($created) && !preg_match('/^20\d{2}-\d{1,2}-\d{1,2}$/', $created)) {
			$this->err[] = 'Please supply a valid creation date.';
			return false;
		} elseif (is_null($created)) {
			$created_date = 'NOW()';
		} elseif (preg_match('/^20\d{2}-\d{1,2}-\d{1,2}$/', $created)) {
			$created_date = '"'.$created.'"';
		} else {
			$this->err[] = 'Creation Date is in an unknown format: "'.$created.'"';
			return false;
		}
		if (!is_null($expires) && !preg_match('/^\d+$/', $expires) && !preg_match('/^20\d{2}-\d{1,2}-\d{1,2}$/', $expires)) {
			$this->err[] = 'Please supply a valid expiration date or interval.';
			return false;
		} elseif (is_null($expires)) {
			$expires_date = 'NOW() + INTERVAL '.$this->expiration_length.' DAY';
		} elseif (preg_match('/^\d+$/', $expires)) {
			$expires_date = 'NOW() + INTERVAL '.$expires.' DAY';
		} elseif (preg_match('/^20\d{2}-\d{1,2}-\d{1,2}$/', $expires)) {
			$expires_date = '"'.$expires.'"';
		} else {
			$this->err[] = 'Expiration Date is in an unknown format: "'.$expires.'"';
			return false;
		}
		
		$sql = '
			UPDATE Routes SET
				SetterID = "'.$setterID.'",
				SectionID = "'.$sectionID.'",
				Type = "'.$type.'",
				Grade = "'.$grade.'",
				Color = "'.addslashes($color).'",
				Created = '.$created_date.',
				Expires = '.$expires_date.'
			WHERE RouteID = "'.$routeID.'"';

		if ($this->justQuery($sql)) {
			$this->msg[] = 'Route added successfully.';
			return true;
		} else {
			$this->err[] = 'Failed to add route: '.mysql_error($this->hdl_db);
			return false;
		}
	}//end update()
	
	function delete($routeID) {
		if (!preg_match('/^[0-9]+$/',$routeID) || $routeID == 0) {
			$this->err[] = 'Please supply a valid route.';
			return false;
		}
		
		$sql = 'DELETE FROM Routes WHERE RouteID = "'.$routeID.'"';
		if ($this->justQuery($sql)) {
			$this->msg[] = 'Route deleted successfully.';
			return true;
		} else {
			$this->err[] = 'Failed to delete route: '.mysql_error($this->hdl_db);
			return false;
		}
	}//end delete()

	function stripped($routeID) {
		if (!preg_match('/^[0-9]+$/',$routeID) || $routeID == 0) {
			$this->err[] = 'Please supply a valid route.';
			return false;
		}
		
		$sql = 'UPDATE Routes SET Stripped = NOW() WHERE RouteID = "'.$routeID.'"';
		if ($this->justQuery($sql)) {
			$this->msg[] = 'Route marked as stripped successfully.';
			return true;
		} else {
			$this->err[] = 'Failed to set route as stripped: '.mysql_error($this->hdl_db);
			return false;
		}
	}//end stripped()
	
	function checkSetter($setterID) {
		if (!preg_match('/^\d+$/', $setterID)) {
			$this->err[] = 'Please supply a valid Setter.';
			return false;
		}
		
		$sql = 'SELECT SetterID FROM Setters WHERE SetterID = "'.$setterID.'"';
		if ($this->justQuery($sql)) {
			return true;
		} else {
			return false;
		}
	}//end checkSetter()
	
	function checkSection($sectionID) {
		if (!preg_match('/^\d+$/', $sectionID)) {
			$this->err[] = 'Please supply a valid Section.';
			return false;
		}
		
		$sql = 'SELECT SectionID FROM Sections WHERE SectionID = "'.$sectionID.'"';
		if ($this->justQuery($sql)) {
			return true;
		} else {
			return false;
		}
	}//end checkSection
	
	function getTypeArray() {
		$sql = "SHOW COLUMNS FROM Routes LIKE 'Type'";
		$this->runQuery($sql);
		if ($this->num_rows > 0) {
			$objRow = $this->nextObject();
			$arrEnumRanges = explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2",$objRow->Type));
			sort($arrEnumRanges);
			return $arrEnumRanges;
		}
	}//end getStatusArray()
	
	function addExpression($txt) {
		$this->msg[] = $txt;
		$this->arrWhere[count($this->arrWhere)-1] = '('.$this->arrWhere[count($this->arrWhere)-1].$txt.')';
	}//end addExpression()
	
	function msgToSession($err='err',$msg='msg') {
		if (isset($_SESSION[$err]) && is_array($_SESSION[$err])) {
			$_SESSION[$err] = array_merge($_SESSION[$err], $this->err);
		} else {
			$_SESSION[$err] = $this->err;
		}
		
		if (isset($_SESSION[$msg]) && is_array($_SESSION[$msg])) {
			$_SESSION[$msg] = array_merge($_SESSION[$msg], $this->msg);
		} else {
			$_SESSION[$msg] = $this->msg;
		}
		$this->err = array();
		$this->msg = array();
	}//end msgToSession
}

?>