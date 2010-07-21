<?php

include_once('dbClass.php');
class User extends dbClass {

    //Properties
    var $UserID;
    var $objUser;
    
    //Methods
    function User($_UserID='',$_UserName='') {
        $this->dbClass();
        if ($_UserID != '') {
            $this->UserID = $_UserID;
            $sql = "SELECT * FROM AdminUsers WHERE UserID = '" . $this->UserID . "'";
            $this->objUser = $this->getObject($sql);
        }//end if
    }//end constructor
    
    function GetUsers() {
        $sql = "SELECT UserID, Name, UserName, Email FROM AdminUsers ORDER BY Name";
        $this->runQuery($sql);
        if ($this->numRows()) {
            $userHTML = "
            <table class=\"AdminTable\">
            <thead>
            <tr>
                <th>&nbsp;</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
            </tr>
            </thead>
            <tbody>";
            $i = 1;
            while ($objUser = $this->nextObject()) {
                $clr = ($i == 1 ? ' class="Color1"' : '');
                $userHTML .= "
            <tr" . $clr . ">";
				if ($_SESSION['UserAccess'] == 'Admin') {
					$userHTML .= "
                <td>" . ($_SESSION['UserID'] == $objUser->UserID ? "<span style=\"font-weight: bold;\">Logged In</span>" : "
                    <a href=\"users.php?action=EditUser&amp;id=" . $objUser->UserID . "\" title=\"Edit User Information\">Edit</a>
                    <a href=\"users.php?action=DeleteUser&amp;id=" . $objUser->UserID . "\" title=\"Delete User\">Delete</a>
                ") . "</td>";
				}
				else {
					$userHTML .= ($_SESSION['UserID'] == $objUser->UserID ? "<span style=\"font-weight: bold;\">Logged In</span>" : "");
				}
				$userHTML .= "
                <td>" . stripslashes($objUser->Name) . "</td>
                <td>" . stripslashes($objUser->UserName) . "</td>
                <td>" . $objUser->Email . "</td>
            </tr>";
                $i++;
                $i %= 2;
            }//end while
            $userHTML .= "
            </tbody>
            </table>";
            return $userHTML;
        }
        else {
            return "\t\t\t<p class=\"errMsg\">No Users Found in DB</p>\n";
        }//end if
    }//end GetUsers
    
    function AddUserRender() {
        $addHTML = "
        <div class=\"clearfix\">
			<h4>Create New User</h4>
			" . $errmsg . "
			<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">
			<input type=\"hidden\" name=\"action\" value=\"AddUser\" />
			<div class=\"row\">
				<span class=\"label\">Name:</span>
				<span class=\"val\"><input type=\"text\" name=\"name\" size=\"15\" /></span>
			</div>
			<div class=\"row\">
				<span class=\"label\">Username:</span>
				<span class=\"val\"><input type=\"text\" name=\"user\" size=\"15\" /></span>
			</div>
			<div class=\"row\">
				<span class=\"label\">Password:</span>
				<span class=\"val\"><input type=\"password\" name=\"passwd\" size=\"15\" /></span>
			</div>
			<div class=\"row\">
				<span class=\"label\">Re-TypePassword:</span>
				<span class=\"val\"><input type=\"password\" name=\"passwd\" size=\"15\" /></span>
			</div>
			<div class=\"row\">
				<span class=\"label\">Email:</span>
				<span class=\"val\"><input type=\"text\" name=\"email\" size=\"15\" /></span>
			</div>" .
    	($_SESSION['UserAccess'] == 'Admin' ? "
			<div class=\"row\">
				<span class=\"label\">Access Level:</span>
				<span class=\"val\">
					<select name=\"AccessLevel\">
						<option value=\"Admin\">Admin</option>
						<option value=\"Blog\" selected=\"selected\">Blog</option>
					</select>
				</span>
			</div>" : "") . "
			<div class=\"row\">
				<span class=\"label\">&nbsp;</span>
				<span class=\"val\"><input type=\"submit\" name=\"submit\" value=\"Create User\" /></span>
			</div>
			<div class=\"row\">&nbsp;</div>
			</form>
		</div>";
		return $addHTML;
    }//end AddUserRender
    
    function EditUserRender() {
        $pass = false;
        if ($this->objUser->Name == '') {
            $sql = "SELECT UserID, Name, UserName, Email FROM AdminUsers WHERE UserID = '" . $this->UserID . "'";
            if ($this->objUser = $this->getObject($sql)) $pass = true;   
        }
        else $pass = true;
        
        if ($pass) {
            $editHTML = "
        <div class=\"clearfix\">
			<h4>Edit User</h4>
			<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">
			<input type=\"hidden\" name=\"id\" value=\"" . $this->UserID . "\" />
			<input type=\"hidden\" name=\"action\" value=\"EditUser\" />
			<div class=\"row\">
				<span class=\"label\">Name:</span>
				<span class=\"val\"><input type=\"text\" name=\"name\" size=\"15\" value=\"" . $this->objUser->Name . "\" /></span>
			</div>
			<div class=\"row\">
				<span class=\"label\">Username:</span>
				<span class=\"val\"><input type=\"text\" name=\"user\" size=\"15\" value=\"" . $this->objUser->UserName . "\" /></span>
			</div>
			<div class=\"row\">
				<span class=\"label\">Email:</span>
				<span class=\"val\"><input type=\"text\" name=\"email\" size=\"15\" value=\"" . $this->objUser->Email . "\" /></span>
			</div>" .
    	($_SESSION['UserAccess'] == 'Admin' ? "
			<div class=\"row\">
				<span class=\"label\">Access Level:</span>
				<span class=\"val\">
					<select name=\"AccessLevel\">
						<option value=\"Admin\">Admin</option>
						<option value=\"Blog\" selected=\"selected\">Blog</option>
					</select>
				</span>
			</div>" : "") . "
			<div class=\"row\">
				<span class=\"label\">&nbsp;</span>
				<span class=\"val\">
                    <input type=\"submit\" name=\"submit\" value=\"Save Changes\" /><br />
                    <input type=\"reset\" name=\"reset\" value=\"Reset\" />
                </span>
			</div>
			<div class=\"row\">&nbsp;</div>
			</form>
		</div>";
            return $editHTML;
		}
        else {
            return "\t\t\t<p class=\"errMsg\">Couldn't find the User you were looking for.</p>\n" . $this->GetUsers();
        }//end if
    }//end EditUserRender
    
    function DeleteUserRender() {
        $pass = false;
        if ($this->objUser->Name == '') {
            $sql = "SELECT UserID, Name FROM AdminUsers WHERE UserID = '" . $this->UserID . "'";
            if ($this->objUser = $this->getObject($sql)) $pass = true;   
        }
        else $pass = true;
        
        if ($pass) {
            $deleteHTML = "
        <div class=\"clearfix\">
			<h4>Delete User</h4>
			<h4 class=\"errMessage\">Delete User '<em>" . $this->objUser->Name ."</em>'</h4>
			<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">
			<input type=\"hidden\" name=\"id\" value=\"" . $this->UserID . "\" />
			<input type=\"hidden\" name=\"action\" value=\"DeleteUser\" />
			<div class=\"row\">
				<span class=\"label\">
					<input type=\"submit\" name=\"submit\" value=\"YES\" />
				</span>
				<span class=\"val\">
					<input type=\"button\" name=\"clear\" value=\"NO\" onclick=\"history.back();\" />
				</span>
			</div>
			<div class=\"row\">&nbsp;</div>
			</form>
		</div>";
            return $deleteHTML;
		}
        else {
            return "\t\t\t<p class=\"errMsg\">Couldn't find the User you were looking for.</p>\n" . $this->GetUsers();
        }//end if    
    }//end DeleteUserRender
    
    function AddUserSubmit() {
        if (count($_POST) && $_POST['action'] == 'AddUser') {
    		$user = addslashes($_POST['user']);
    		$pass = $_POST['passwd'];
    		$name = addslashes($_POST['name']);
    		$email = addslashes($_POST['email']);
    		$accesslevel = (isset($_POST['AccessLevel']) ? $_POST['AccessLevel'] : 'Blog');
    		
    		$sql = "
    			INSERT INTO AdminUsers (
    				UserName,
    				PassWord,
    				Name,
    				Email,
    				AccessLevel
    			) VALUES (
    				'" . $user . "',
    				ENCODE('" . $pass . "','wH!t3W|za4D'),
    				'" . $name . "',
    				'" . $email . "',
    				'" . $accesslevel . "'
    			)";
    		
    		if ($this->justQuery($sql)) return "\t\t\t<p class=\"Msg\">User added Successfully.</p>\n" . $this->GetUsers();
    		else return "\t\t\t<p class=\"errMsg\">Error while trying to add User to Database</p>\n" . $this->GetUsers();
    	}
    	else return "\t\t\t<p class=\"errMsg\">You do not have access to this functionality</p>\n" . $this->GetUsers();
    }//end AddUserSubmit
    
    function EditUserSubmit() {
        if (count($_POST) && $_POST['action'] == 'EditUser') {
    		$user = addslashes($_POST['user']);
    		$name = addslashes($_POST['name']);
    		$email = addslashes($_POST['email']);
    		$accesslevel = (isset($_POST['AccessLevel']) ? $_POST['AccessLevel'] : 'Blog');
    		
    		$sql = "
    			UPDATE AdminUsers SET
    				UserName = '" . $user . "',
    				Name = '" . $name . "',
    				Email = '" . $email . "',
    				AccessLevel = '" . $accesslevel . "'
                WHERE UserID = '" . $this->objUser->UserID . "'";
    		if ($this->justQuery($sql)) return "\t\t\t<p class=\"Msg\">User info saved successfully.</p>\n" . $this->GetUsers();
    		else return "\t\t\t<p class=\"errMsg\">Error while trying to save User info to Database</p>\n" . $this->GetUsers();
    	}
    	else return "\t\t\t<p class=\"errMsg\">You do not have access to this functionality</p>\n" . $this->GetUsers();
    }//end EditUserSubmit
    
    function DeleteUserSubmit() {
        if ($_POST['submit'] && $_POST['action'] == 'DeleteUser' && $this->UserID != '') {
			if ($this->justQuery("DELETE FROM AdminUsers WHERE UserID = '" . $this->UserID . "'")) return "\t\t\t<p class=\"Msg\">User Deleted Successfully</p>\n" . $this->GetUsers();
			else return "\t\t\t<p class=\"errMsg\">An Error occurred while attempting to delete this User</p>\n" . $this->GetUsers();
		}
		else return "<p class=\"errMsg\">Permission Denied to Delete this User</p>\n" . $this->GetUsers();
    }//end DeleteUserSubmit

}//end class

?>
