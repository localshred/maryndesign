<?php

//include_once('/home/goprintdir/www/goprintdir/includes/db.conf.php');
define('DB_SERV','localhost');
define('DB_USER','eamaiar_feanor');
define('DB_PASS','ol0r!N');

class DB {
	var $hdl_db;
	var $result;
	var $num_rows;
	var $affected_rows;
	var $insert_id;
	var $err_msg = array();
	
	function DB () {
		$this->hdl_db = mysql_connect(DB_SERV, DB_USER, DB_PASS);
		if (!$this->hdl_db) {
			$this->dbError('Connection to database failed');
		}
	}
	
	function runQuery($sql) {
		$this->resetVars();
		$this->result = mysql_query($sql);
		if ($this->result != '' || $this->result != false) {
			$this->num_rows = @mysql_num_rows($this->result);
		}
	}
	
	function justQuery($sql) {
		$this->resetVars();
		$this->result = mysql_query($sql);
		$this->affected_rows = mysql_affected_rows($this->hdl_db);
		if (strtoupper(substr(trim($sql), 0, 6)) == 'INSERT') {
			$this->insert_id = mysql_insert_id($this->hdl_db);
		}
		return $this->result;
	}
	
	function nextObject() {
		return mysql_fetch_object($this->result);
	}
	
	function getArray($type=MYSQL_ASSOC) {
		return mysql_fetch_array($this->result, $type);
	}
	
	function buildResult($type='object') {
		if ($this->result == '' || $this->result == false) {
			$this->dbError('MySQL Result set is invalid');
			return false;
		}
		
		$arr_result = array();
		if ($type == 'array') {
			while ($row = $this->getArray()) {
				$arr_result[] = $row;
			}
		} elseif ($type == 'object') {
			while ($row = $this->nextObject()) {
				$arr_result[] = $row;
			}
		}
		return $arr_result;
	}
	
	function getRow() {
		return mysql_fetch_row($this->result);
	}
	
	function getField($row_num, $field) {
		return mysql_result($this->result, $row_num, $field);
	}
	
	function resetVars() {
		$this->num_rows = 0;
		$this->affected_rows = 0;
		$this->insert_id = 0;
	}
	
	function resetMsgs() {
		$this->err_msg = array();
	}
	
	function dbError($msg) {
		if (mysql_error($this->hdl_db) != '') {
			$msg .= "\n".'MySQL Error #'.mysql_errno($this->hdl_db).': '.mysql_error($this->hdl_db);
		}
		$this->err_msg[] = $msg;
	}
		
}


?>