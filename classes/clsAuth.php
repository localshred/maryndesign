<?php

include_once('db.cls.php');
class Authenticate extends DB {
	var $time_limit = 45; // minutes
	var $auth_key = 'pn^4%afO|';
	var $auth_hash;
	var $user_id;
	var $user_name;
	var $user_disp_name;
	var $user_access;
	var $session_key;
	var $msg;
	var $err;
	
	function authenticate() {
		$this->DB();
		$this->auth_hash = md5($this->auth_key);
	}//end authenticate()
	
	function login($user,$pass) {
		$sql = '
			SELECT
				UserID,
				PassWord
			FROM AdminUsers
			WHERE LOWER(UserName) = "'.strtolower($user).'"
			AND PassWord = MD5("'.$pass.'")';
			
		$this->runQuery($sql);
		if ($this->num_rows == 1) {
			$this->user_id = $this->getField(0,'UserID');
			$this->user_name = $user;
			
			$_SESSION['auth']['time'] = time();
			$_SESSION['auth']['user'] = $user;
			$this->session_key = md5($this->auth_hash.$this->user_id.$_SESSION['auth']['time'].$_SESSION['auth']['user']);
			$this->updateKey();
			
			$this->msg[] = 'Login Successful. Session length is '.$this->time_limit.' minutes.	';
			return true;
		} else {
			$this->err[] = 'User authentication failed ['.$this->num_rows.']. '.mysql_error($this->hdl_db);
			return false;
		}
	}//end login()
	
	function securePage($page_access='Blog') {
		if (!isset($_SESSION['auth'])) {
			return false;
		}
		
		if (isset($_SESSION['auth']) && time() > $_SESSION['auth']['time'] + $this->time_limit*60) {
			$this->err[] = 'You have been inactive for more than '.$this->time_limit.' minutes. Please login again.';
			$this->logout();
			return false;
		}
		
		$sql = '
			SELECT
				UserID,
				UserName,
				AuthKey,
				AccessLevel,
				Name
			FROM AdminUsers
			WHERE UserName = "'.$_SESSION['auth']['user'].'"';

		$this->runQuery($sql);
		if ($this->num_rows == 1) {
			$this->user_id = $this->getField(0,'UserID');
			$this->user_name = $this->getField(0,'UserName');
			$this->user_disp_name = $this->getField(0,'Name');
			$this->user_access = $this->getField(0,'AccessLevel');
			
			$db_key = $this->getField(0,'AuthKey');
			
			if ($page_access == 'Momentum' && ($this->user_access != 'Momentum' && $this->user_access != 'Admin')) {
				$this->err[] = 'You do not have accces to view '.basename($_SERVER['SCRIPT_NAME']).'.';
				return false;
			}
			
			$this->session_key = md5($this->auth_hash.$this->user_id.$_SESSION['auth']['time'].$_SESSION['auth']['user']);
			if ($db_key != $this->session_key) {
				$this->err[] = 'Authentication Failed. The session has been tampered with.';
				$this->logout();
				return false;
			}
						
			$_SESSION['auth']['time'] = time();
			$_SESSION['auth']['user'] = $this->user_name;
			$this->session_key = md5($this->auth_hash.$this->user_id.$_SESSION['auth']['time'].$_SESSION['auth']['user']);
			$this->updateKey();
			return true;
		} else {
			$this->err[] = 'Authentication Failed. User not found.';
			$this->logout();
			return false;
		}
	}//end securePage()
	
	function updateKey() {
		$sql = 'UPDATE AdminUsers SET AuthKey = "'.$this->session_key.'" WHERE UserID = "'.$this->user_id.'"';
		return $this->justQuery($sql);		
	}
	
	function logout() {
		unset($_SESSION['auth']);
		return true;
	}//end logout()
	
	function msgToSession($err='error',$msg='msg') {
		$_SESSION[$err] = $this->err;
		$_SESSION[$msg] = $this->msg;
	}
	
}//end class
?>