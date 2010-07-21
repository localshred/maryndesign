<?php

include_once('dbClass.php');
class Blog extends dbClass {

	/* Properties */
	var $_UserID;
	var $_UserName;
	var $_UnicodeChar;
	var $_MonthEntryNum;
	
	/* Methods */
	
	function Blog($_Which='Public') {
		$this->dbClass();
		$this->MonthHasEntries();
		$this->_UserID = isset($_SESSION['UserID']) ? $_SESSION['UserID'] : 1;
		$this->_UserName = isset($_SESSION['UserName']) ? "'" . $_SESSION['_UserName'] . "'" : 'BJ';
		$this->_UserEmail = $this->getObject("SELECT Email FROM AdminUsers WHERE UserID = " . $this->_UserID);
		if (strstr($_SERVER['HTTP_USER_AGENT'],'MSIE')) $this->_UnicodeChar = '&#x25a1;';
		else $this->_UnicodeChar = '&#x25A3;';
	}//end constructor
	
	function MonthHasEntries($_Month='',$_Year='') {
		$_Month = $_Month == '' ? date('m') : $_Month;
		$_Year = $_Year == '' ? date('Y') : $_Year;
		$sql = "
            SELECT 
                COUNT(*) as Count
            FROM BlogEntries
            WHERE (MONTH(DATE(PostDate)) = '" . $_Month . "'
                AND YEAR(DATE(PostDate)) = '" . $_Year . "')";
		$objNum = $this->getObject($sql);
		$this->_MonthEntryNum = $objNum->Count;
	}
	
	function BlogEntryList($_Which='Public',$_Limit='',$_Month='',$_Year='',$_User='',$_Tag='') {

		$_Limit = $_Limit != '' ? 'LIMIT 0,' . $_Limit : '';
		if ($_Tag != '') {
			$_Month = $_Month == '' ? date('m') : $_Month;
			$_Year = $_Year == '' ? date('Y') : $_Year;
			$_WhereClause = "WHERE FIND_IN_SET(" . $_Tag . ",b.Tags) > 0";			
		}
		elseif ($_User != '') {
			$_Month = $_Month == '' ? date('m') : $_Month;
			$_Year = $_Year == '' ? date('Y') : $_Year;
			$_WhereClause = 'WHERE b.UserID = ' . $_User;
		}
		elseif ($_User != '' && $_Month != '') {
			$_Month = $_Month == '' ? date('m') : $_Month;
			$_Year = $_Year == '' ? date('Y') : $_Year;
			$this->MonthHasEntries($_Month,$_Year);
			if ($this->_MonthEntryNum > 0) $_WhereClause = "WHERE MONTH(DATE(PostDate)) = '" . $_Month . "' AND YEAR(DATE(PostDate)) = '" . $_Year . "' AND b.UserID = " . $_User;
		}
		else {
			$_Month = $_Month == '' ? date('m') : $_Month;
			$_Year = $_Year == '' ? date('Y') : $_Year;
			$this->MonthHasEntries($_Month,$_Year);
			if ($this->_MonthEntryNum > 0) $_WhereClause = "WHERE MONTH(DATE(PostDate)) = '" . $_Month . "' AND YEAR(DATE(PostDate)) = '" . $_Year . "'";
		}
/*
		if ($_SERVER['SCRIPT_NAME'] == '/index.php' || $_Which == 'Admin') {
			//Print an archive indexer formamajigger
			$m = !$_GET['month'] ? date('m') : $_GET['month'];
			$y = !$_GET['year'] ? date('Y') : $_GET['year'];
			print "
			<div class=\"searchOrders\">
				<h4>Blog Filter</h4>
				<p>User the filter below to search by Entry User and/or Date</p>
				<form method=\"get\">
				<strong>User:</strong>
				<select name=\"user\">
					<option value=\"\">All</option>";
			$sql = "SELECT Name, UserID FROM AdminUsers";
			$this->runQuery($sql);
			if (@$this->numRows()) {
				while ($objUser = $this->nextObject()) {
					$selected = $_GET['user'] == $objUser->UserID ? ' selected="selected"' : '';
					print "\n\t\t\t\t\t<option value=\"" . $objUser->UserID . "\"" . $selected . ">" . $objUser->Name . "</option>";
				}
			}
			print "
				</select><br />
				<strong>Date:</strong>
				<select name=\"month\">
					<option value=\"01\"" . ($m == '01' ? ' selected="selected"' : '') . ">January</option>
					<option value=\"02\"" . ($m == '02' ? ' selected="selected"' : '') . ">February</option>
					<option value=\"03\"" . ($m == '03' ? ' selected="selected"' : '') . ">March</option>
					<option value=\"04\"" . ($m == '04' ? ' selected="selected"' : '') . ">April</option>
					<option value=\"05\"" . ($m == '05' ? ' selected="selected"' : '') . ">May</option>
					<option value=\"06\"" . ($m == '06' ? ' selected="selected"' : '') . ">June</option>
					<option value=\"07\"" . ($m == '07' ? ' selected="selected"' : '') . ">July</option>
					<option value=\"08\"" . ($m == '08' ? ' selected="selected"' : '') . ">August</option>
					<option value=\"09\"" . ($m == '09' ? ' selected="selected"' : '') . ">September</option>
					<option value=\"10\"" . ($m == '10' ? ' selected="selected"' : '') . ">October</option>
					<option value=\"11\"" . ($m == '11' ? ' selected="selected"' : '') . ">November</option>
					<option value=\"12\"" . ($m == '12' ? ' selected="selected"' : '') . ">December</option>
				</select>
				<select name=\"year\">";
			for ($i = '2006'; $i <= date('Y'); $i++) {
				$selected = $y == $i ? ' selected="selected"' : '';
				print "\n\t\t\t\t\t<option value=\"" . $i . "\"" . $selected . ">" . $i . "</option>";
			}
			print "
				</select>
				<input type=\"submit\" name=\"submit\" value=\"GO\" />
				</form>
			</div>";
			print $archiveForm;
		}
*/
		if ($_Which != 'Admin') {
			$entryHTML .= "\t\t\t<div class=\"blogMenu\">\n";
	
			// Get a tag cloud of current Tags
			$sql = "
				SELECT
					t.*,
					(
						SELECT COUNT(b.EntryID)
						FROM BlogEntries b
						WHERE FIND_IN_SET(t.TagID, b.Tags)
					) AS numUsed
				FROM Tags t
				ORDER BY t.Name";
			
			$this->runQuery($sql);
			if ($this->numRows() > 0) {
				//print "\t\t<h4>Article Categories</h4>\n";
				$entryHTML .= "\t\t\t\t<ul class=\"clearfix\">\n";
				$entryHTML .= "\t\t\t\t<li class=\"title\">Article Tags:</li>\n";
				$entryHTML .= "\t\t\t\t<li>\n";
				while ($objTag = $this->nextObject()) {
					switch (true) {
						case $objTag->numUsed == 0: $linkSize = ".6em"; break;
						case $objTag->numUsed < 2: $linkSize = ".8em"; break;
						case $objTag->numUsed >= 2 && $objTag->numUsed < 10: $linkSize = "1.1em"; break;
						case $objTag->numUsed >= 10: $linkSize = "1.4m"; break;
					}
					$cur = ($_GET['tag'] == $objTag->TagID ? " class=\"curPage\"" : "");
					$entryHTML .= "&nbsp;<a href=\"?tag=" . $objTag->TagID . "\" title=\"View all entries Categorized as " . stripslashes($objTag->Name) . "\" style=\"font-size: " . $linkSize . ";\" " . $cur . ">" . stripslashes($objTag->Name) . "</a>&nbsp;\n";
				}
				$entryHTML .= "\t\t\t\t\t</li>\n";
				$entryHTML .= "\t\t\t\t</ul>\n";
			}
			
			// Get an Archive List
			$sql = "
				SELECT
					DATE_FORMAT(PostDate, '%M %Y') as ArchiveText,
					MONTH(PostDate) as Month,
					YEAR(PostDate) as Year
				FROM BlogEntries 
				GROUP BY ArchiveText";
			$this->runQuery($sql);
			if ($this->numRows() > 0) {
				//print "\t\t<h4>Blog Archives</h4>\n";
				$entryHTML .= "\t\t\t\t<ul class=\"clearfix\">\n";
				$entryHTML .= "\t\t\t\t<li class=\"title\">Blog Archives:</li>\n";
				while ($objArchive = $this->nextObject()) {
					$cur = ($_GET['month'] == $objArchive->Month && $_GET['year'] == $objArchive->Year ? " class=\"curPage\"" : "");
					$entryHTML .= "\t\t\t\t\t<li><a href=\"?month=" . $objArchive->Month . "&amp;year=" . $objArchive->Year . "\" title=\"View all entries archived in " . $objArchive->ArchiveText . "\"" . $cur . ">" . $objArchive->ArchiveText . "</a></li>\n";
				}
				$entryHTML .= "\t\t\t\t</ul>\n";
			}
			$entryHTML .= "\t\t\t</div>\n";
		}

		$sql = "
			SELECT
				b.EntryID,
				DATE_FORMAT((b.PostDate - INTERVAL 1 HOUR),'%M %D, %Y at %l:%i %p (MST)') as PostDate,
				b.Title,
				b.ShortDesc,
				b.Tags,
				u.UserID,
				u.Name
			FROM
				BlogEntries b
			LEFT JOIN AdminUsers u
				ON b.UserID = u.UserID
			" . $_WhereClause . "
			ORDER BY  b.PostDate DESC
			" . $_Limit;

		$this->runQuery($sql);

		if (@$this->numRows()) {
			switch($_Which) {
				case "Public":
					while ($objEntry = $this->nextObject()) {
						$userEntryLink = "<a href=\"./?user=" . $objEntry->UserID . ($_GET['month'] != '' ? '&amp;amp;month=' . $_GET['month'] : '') . ($_GET['year'] != '' ? '&amp;year=' . $_GET['year'] : '') . "\" title=\"View entries by " . stripslashes($objEntry->Name) . "\">" . stripslashes($objEntry->Name) . "</a>";
						$numComments = $this->CountComments($objEntry->EntryID)->Count;
						$entryHTML .= "
			<div class=\"BlogShort\">
				<h3 class=\"EntryTitle\"><a href=\"./?id=" . $objEntry->EntryID . "&amp;showEntry=1\" title=\"View this Blog Entry\">" . $this->_UnicodeChar . " " . stripslashes($objEntry->Title) . "</a></h3>
				<p class=\"smalltext\">posted by " . $userEntryLink . " on " . stripslashes($objEntry->PostDate) . "</p>
				<p class=\"smalltext\"><a href=\"./?id=" . $objEntry->EntryID . "&amp;showEntry=1#" . ($numComments < 1 ? 'add' : '') . "comments\" title=\"Read User Comments, or add your own\">" . $this->CountComments($objEntry->EntryID)->Count . " Comments</a> added to this post</p>
				<div class=\"tagList clearfix\">
" . $this->GetBlogTags('links',$objEntry->Tags,TRUE) . "
				</div>
				<p class=\"shortText\">" . nl2br(stripslashes($objEntry->ShortDesc)) . "</p>
				<p><a href=\"./?id=" . $objEntry->EntryID . "&amp;showEntry=1\" title=\"View this Blog Entry\">Read More ...</a></p>
			</div>";
					}
				break;
				
				case "Admin":
					$i = 1;
					$c = 1;
					$entryHTML .= "
			<table cellspacing=\"0\" cellpadding=\"0\" class=\"BlogTable\">
			<thead>
				<tr>
					<th class=\"title\" colspan=\"4\">Recent Blog Entries</th>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<th style=\"width: 45%;\">Title</th>
					<th>Posted Date</th>
					<th>Post By</th>
				</tr>			
			</thead>
			<tbody>";
					while ($objEntry = $this->nextObject()) {
						$Color = 'Color' . $c;
						$entryHTML .= "
				<tr class=\"" . $Color . "\">";
						if ($_SESSION['UserAccess'] == 'Admin' || ($objEntry->UserID == $_SESSION['UserID'] && $_SESSION['UserAccess'] == 'Blog')) {
							$entryHTML .= "
					<td><a href=\"./blog.php?action=EditEntry&amp;id=" . $objEntry->EntryID . "\" title=\"Edit this Entry\">Edit</a>  <a href=\"./blog.php?action=DeleteEntry&amp;id=" . $objEntry->EntryID . "\" title=\"Delete this Entry\">Delete</a></td>";
						}
						else {
							$entryHTML .= "
					<td>&nbsp;</td>";
						}
						$entryHTML .= "
					<td><a href=\"./blog.php?id=" . $objEntry->EntryID . "&amp;showEntry=1\" title=\"View this Blog Entry\">" . stripslashes($objEntry->Title) . "</a></td>
					<td>" . stripslashes($objEntry->PostDate) . "</td>
					<td>" . stripslashes($objEntry->Name) . "</td>
				</tr>";
						$c %= 2;
						$c++;
					}//end while
					$entryHTML .= "
			</tbody>
			</table>\n";
				break;
			}
		}
		else {
			if ($_User != '') {
				$objUser = $this->getObject("SELECT Name FROM AdminUsers WHERE UserID = " . $_User);
				$entryHTML .= "\t\t\t" . $archiveForm . "\t\t\t<p class=\"errMsg\">No Blog Entries posted by " . $objUser->Name . " in " . $_Month . "/" . $_Year . "</p>\n";
			}
			else $entryHTML .= "\t\t\t" . $archiveForm . "\t\t\t<p class=\"errMsg\">No Blog Entries Found in " . $_Month . "/" . $_Year . "</p>\n";
		}
		
		return $entryHTML;
	}//end BlogEntryList
	
	function GetBlogEntry($_EntryID,$_Which='Public') {
		$sql = "
			SELECT
				b.EntryID,
				DATE_FORMAT((b.PostDate - INTERVAL 1 HOUR),'%M %D, %Y at %l:%i %p (MST)') as PostDate,
				DATE_FORMAT((b.EditDate - INTERVAL 1 HOUR),'%M %D, %Y at %l:%i %p (MST)') as EditDate,
				b.Title,
				b.ShortDesc,
				b.LongDesc,
				b.Tags,
				b.UseHTML,
				u.UserName,
				u.UserID,
				u.Name
			FROM
				BlogEntries b
			LEFT JOIN AdminUsers u
				ON b.UserID = u.UserID
			WHERE b.EntryID = " . $_EntryID;
		
		if ($objEntry = $this->getObject($sql)) {
			include_once('clsSecureImage.php');
			/* can't think of a better way to do this... */
			$pos = false;
			for ($i = 0; $i <= strlen($_SERVER['PATH_TRANSLATED']); $i++) {
                if ($_SERVER['PATH_TRANSLATED'][$i] == '/') $pos = $i;
			}//end for
			$pathToImg = preg_replace(array('/(#|\?).*/','/\/admin/'),'',substr($_SERVER['PATH_TRANSLATED'],0,$pos));
			$seedstr = substr(md5(microtime()*mktime()),0,5);
			$validImage = new PWImage($pathToImg . "/images/random.png", $seedstr, 2, array(0, 0, 0), array(255, 128, 200));
			$_SESSION['secureForm'] = $seedstr;
			$numComments = $this->CountComments($objEntry->EntryID)->Count;	
			$entryText = nl2br(stripslashes($objEntry->ShortDesc)) . ($objEntry->UseHTML == 'yes' ? stripslashes($objEntry->LongDesc) : nl2br(stripslashes($objEntry->LongDesc)));
			$blogHTML = "
            <script type=\"text/javascript\">
                var xmlHttp;
                function doRequest() {
                    xmlHttp = GetXmlHttpObject();
                    if (xmlHttp == null) {
                        alert (\"Browser does not support HTTP Request\");
                        return;
                    }
                    var url = \"reloadImage.php\";
                    xmlHttp.onreadystatechange = stateChanged;
                    xmlHttp.open(\"GET\",url,true);
                    xmlHttp.send(null);
                }//end doRequest()
                
                function stateChanged() {
                    if (xmlHttp.readyState == 4 || xmlHttp.readyState == \"complete\") {
                        document.getElementById(\"randomImg\").firstChild.src = document.getElementById(\"randomImg\").firstChild.src + '?' + (new Date()).getTime();
                    }
                }//end stateChanged()
                
                function GetXmlHttpObject() {
                    var objXMLHttp = null;
                    if (window.XMLHttpRequest) {
                        objXMLHttp = new XMLHttpRequest();
                    }
                    else if (window.ActiveXObject) {
                        objXMLHttp = new ActiveXObject(\"Microsoft.XMLHTTP\");
                    }
                    return objXMLHttp;
                }//end GetXmlHttpObject()
            </script>
			<p>&#x25c4; <a href=\"./\" title=\"Go back to the Blog's Main Page\">Blog Home</a></p>
			<div class=\"BlogLong\">
				<h3 class=\"EntryTitle\">" . $this->_UnicodeChar . " " . stripslashes($objEntry->Title) . "</h3>
				<p class=\"smalltext\">posted by <a href=\"./?user=" . $objEntry->UserID . ($_GET['month'] != '' ? '&amp;month=' . $_GET['month'] : '') . ($_GET['year'] != '' ? '&amp;year=' . $_GET['year'] : '') . "\" title=\"View entries by " . stripslashes($objEntry->Name) . "\">" . stripslashes($objEntry->Name) . "</a> on " . $objEntry->PostDate . "</p>
				<p class=\"smalltext\"><a href=\"#" . ($numComments < 1 ? 'add' : '') . "comments\" title=\"Read User Comments, or add your own\">" . $numComments . " Comments</a> added to this post</p>
				<div class=\"tagList clearfix\">
" . $this->GetBlogTags('links',$objEntry->Tags,TRUE) . "
				</div>
				<div class=\"entryText\">
" . $entryText . "
				</div>
" . ($objEntry->EditDate != '' ? "\t\t\t\t<p class=\"smalltext\">last edited on " . $objEntry->EditDate . "</p>" : "") . "
				
				<div class=\"UserComments clearfix\">
					<h4>Comments</h4>
					<a name=\"comments\"></a>
" . $this->GetEntryComments($objEntry->EntryID) . "
					<h4>Add a comment</h4>
					<a name=\"addcomments\"></a>
					<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">
					<div style=\"display: none;\"><input type=\"hidden\" name=\"EntryID\" value=\"" . $objEntry->EntryID . "\" /></div>
					<div class=\"row\">
						<span class=\"label\">Name:</span>
						<span class=\"val\"><input type=\"text\" size=\"25\" name=\"Name\"" . (count($_POST) ? ' value="' . stripslashes($_POST['Name']) . '"' : '') . " /></span>
					</div>
					<div class=\"row\">
						<span class=\"label\">Email:</span>
						<span class=\"val\"><input type=\"text\" size=\"25\" name=\"Email\"" . (count($_POST) ? ' value="' . stripslashes($_POST['Email']) . '"' : '') . " /> <span class=\"smalltext\">(Will not be displayed)</span></span>
					</div>
					<div class=\"row\">
						<span class=\"label\">Website:</span>
						<span class=\"val\"><input type=\"text\" size=\"25\" name=\"Website\"" . (count($_POST) ? ' value="' . stripslashes($_POST['Website']) . '"' : '') . " /></span>
					</div>
					<div class=\"row\">
						<span class=\"label\">Comments:</span>
						<span class=\"val\"><textarea name=\"Comments\" rows=\"5\" cols=\"30\">" . (count($_POST) ? stripslashes($_POST['Comments']) : '') . "</textarea></span>
					</div>
					<div class=\"row\">
						<span class=\"label\">Verify Post:</span>
						<span class=\"val\"><input type=\"text\" size=\"25\" name=\"verify\" /></span>
					</div>
					<div class=\"row\">
						<span class=\"label\">&nbsp;</span>
						<span class=\"val\" id=\"randomImg\"><img src=\"" . (strstr($_SERVER['PATH_TRANSLATED'],'/admin/') ? '../' : './') . "images/random.png\" alt=\"Verify the contents of this image in the box provided above to successfully submit this form\" /></span>
					</div>
					<div class=\"row\">
						<span class=\"label\">&nbsp;</span>
						<span class=\"val\"><a href=\"javascript: void(0);\" onclick=\"doRequest();\" title=\"Reload the Verification Image so I can read it!\">I can't read it!</a></span>
					</div>
					<div class=\"row\">
						<span class=\"label\">&nbsp;</span>
						<span class=\"val\"><input type=\"submit\" name=\"Submit\" value=\"Add Comment\" /></span>
					</div>
					</form>
				</div>
			
			</div>
			<p>&#x25c4; <a href=\"./\" title=\"Go back to the Blog's Main Page\">Blog Home</a></p>";
			
		}
		else {
			return "\t\t\t<p class=\"errMsg\">Couldn't find the article you were looking for!</p>\n" . $this->BlogEntryList($_Which);
		}
		return $blogHTML;			
	}//end GetBlogEntry

	function GenerateHTML($_Which='html') {
		switch($_Which) {
			case "html":
				$html = "";
				return $html;
			break;
			
			case "script":
				$script = "";
				return $script;
			break;
		}
	}//end GenerateHTML
	
	function GetBlogTags($_DisplayType='links',$_csTags='',$_useCSTags=FALSE) {
		$db = new dbClass();
		$db->runQuery("SELECT * FROM Tags " . ($_useCSTags == TRUE ? 'WHERE FIND_IN_SET(TagID,\'' . $_csTags . '\') > 0 ' : '') . "ORDER BY Name");
		if ($db->numRows() > 0) {
			if (strtolower($_DisplayType) == 'adminedit') $_arrCSTags = explode(',',$_csTags);
			$i = 1;
			while ($objTags = $db->nextObject()) {
				switch(strtolower($_DisplayType)) {
					case "links":
						if ($i == 1) $tagHTML .= "\t\t\t\t\t<ul class=\"tagLinkList\">\n".
																			"\t\t\t\t\t\t<li style=\"font-weight: bold;\">Tags:</li>\n";
						$tagHTML .= "\t\t\t\t\t\t<li><a href=\"./?tag=" . $objTags->TagID . "\" title=\"View Blogs Tagged as " . $objTags->Name . "\">" . $objTags->Name . "</a></li>\n";
						if ($i == $db->numRows()) $tagHTML .= "\t\t\t\t\t</ul>\n";
					break;
					case "adminadd":
					case "adminedit":
						if ($i == 1) $tagHTML .= "\t\t\t\t<ul class=\"tagFormList\">\n";
						if (strtolower($_DisplayType) == 'adminadd') $tagHTML .= "\t\t\t\t\t\t<li><input type=\"checkbox\" name=\"Tags[]\" value=\"" . $objTags->TagID . "\" id=\"tag" . $objTags->TagID . "\"" . (count($_POST) && $_POST['Tags'][$objTags->TagID] ? ' checked="checked"' : '') . " /> <label for=\"tag" . $objTags->TagID . "\">" . $objTags->Name . "</label></li>";
						if (strtolower($_DisplayType) == 'adminedit') $tagHTML .= "\t\t\t\t\t\t<li><input type=\"checkbox\" name=\"Tags[]\" value=\"" . $objTags->TagID . "\" id=\"tag" . $objTags->TagID . "\"" . (@in_array($objTags->TagID,$_arrCSTags) ? ' checked="checked"' : '') . " /> <label for=\"tag" . $objTags->TagID . "\">" . $objTags->Name . "</label></li>";
						if ($i == $db->numRows()) {
							$tagHTML .= "\t\t\t\t\t<li><span class=\"smalltext\">[Add New Tag]</span> <input type=\"text\" name=\"NewTag\" size=\"15\" /></span></li>\n".
													"\t\t\t\t</ul>\n";
						}
					break;
				}
				$i++;
			}
			return $tagHTML;
		}
		else return "<span class=\"smalltext\">No Tags Currently Defined</span>";
	}//end GetBlogTags

/*****************************
	Entry Comment Functions
******************************/

	function CountComments($_EntryID) {
		return $objCount = $this->getObject("SELECT COUNT(*) as Count FROM BlogComments WHERE EntryID = " . $_EntryID);
	}

	function GetEntryComments($_EntryID) {
		$sql = "
			SELECT
				CommentID,
				Name,
				Website,
				Comments,
				DATE_FORMAT((CommentTime - INTERVAL 1 HOUR),'%M %D, %Y at %l:%i %p (MST)') as FormattedTime
			FROM BlogComments
			WHERE EntryID = " . $_EntryID . "
				ORDER BY CommentTime DESC";
		$this->runQuery($sql);
		if ($this->numRows() > 0) {
			$i = 1;
			while ($objComment = $this->nextObject()) {
				$name = $objComment->Name != '' ? $objComment->Name : 'Anonymous';
				$entryComments .= "
					<div class=\"comment" . ($i == 0 ? ' clr' : '') . "\">
						<a name=\"comment" . $objComment->CommentID . "\"></a>" . (strstr($_SERVER['PHP_SELF'],'/admin/') && $_SESSION['UserAccess'] == 'Admin' ? "
                        <p>
                            [<a href=\"/admin/blog.php?action=EditComment&amp;id=" . $objComment->CommentID . "\" title=\"Edit this Comment\">EDIT</a>] 
                            [<a href=\"/admin/blog.php?action=DeleteComment&amp;id=" . $objComment->CommentID . "\" title=\"Delete this Comment\">DELETE</a>]
                        </p>" : '') . "
						<p><span class=\"userName\">" . ($objComment->Website != '' ? "<a href=\"" . $objComment->Website . "\" title=\"This link will open a new window\" target=\"_blank\">" . $name . "</a>" : $name) . "</span> says:</p>
						<p>" . nl2br(stripslashes($objComment->Comments)) . "</p>
						<p class=\"commentDate\">" . $objComment->FormattedTime . "</p>
					</div>\n";
					$i++;
					$i %= 2;
			}
			return $entryComments;
		}
		else {
			return "\t\t\t\t\t<p class=\"noComments\">No Comments Here. Add yours below!</p>\n";
		}
	}//end GetEntryComments

	function AddComment($_EntryID,$_Comments,$_Name='',$_Email='',$_Website='') {
		return; // DISABLE ALL COMMENTS

		if (strtolower($_SESSION['secureForm']) == strtolower($_POST['verify']) && !strstr($_Comments, "[url=")) {
			$sql = "INSERT INTO BlogComments (EntryID, Name, Website, Email, Comments, CommentTime) VALUES (" . $_EntryID . ",'" . $_Name . "','" . $_Website . "','" . $_Email . "','" . addslashes($_Comments) . "',NOW())";
			$this->runQuery($sql);
			if ($this->affectedRows() > 0) {
				$_CommentID = $this->getID();
				if ($objUpdates = $this->getObject("SELECT Title, EmailUpdatesTo as Email FROM BlogEntries WHERE EntryID = " . $_EntryID)) {
					$to = $objUpdates->Email;
					$subject = "New Comment Posted to '" . $objUpdates->Title . "'";
					date_default_timezone_set("America/Denver");
					$body = "
<html>
<head>
	<title></title>
	<style type=\"text/css\">
		div.comment {
			margin: 5px;
			padding: 5px;
			background: #E9EBE3;
			font-size: .9em;
			border-left: 10px solid #825B2F;
		}		
		div.comment p span.userName {
			font-weight: bold;
		}		
		div.comment p.commentDate {
			font-size: .8em;
			color: #777;
		}
	</style>
</head>
<body>
<h3>New Comment Posted to '" . $objUpdates->Title . "'</h3>

<div class=\"comment\">
	<p><span class=\"userName\">" . $_Name . "</span> says:</p>
	<p>" . nl2br(stripslashes($_Comments)) . "</p>
" . ($_Email != '' ? "<p><a href=\"mailto:" . $_Email . "\" title=\"Reply To " . $_Name . "\">" . $_Email . "</a></p>" : "" ) . "
" . ($_Website != '' ? "<p><a href=\"" . $_Website . "\" title=\"Visit the website " . $_Website . "\">" . $_Website . "</a></p>" : "" ) . "
	<p class=\"commentDate\">" . date('F jS, Y \a\t g:i A (\M\S\T)') . "</p>
	<p class=\"commentDate\">IP: " . $_SERVER['REMOTE_ADDR'] . "</p>
</div>

<p><a href=\"http://www." . $_SERVER['HTTP_HOST'] . "/?id=" . $_EntryID . "&amp;showEntry=1#comment" . $_CommentID . "\">Click Here</a> to view this comment with the original post.</p>
</body>
</html>";
					$headers = 'MIME-Version: 1.0' . "\r\n";
					$headers .=	'Content-type: text/html; charset=iso-8859-1' . "\r\n";
					$headers .=	"From: blog.comments@" . $_SERVER['HTTP_HOST'] . "\r\n";
					$headers .=	"Reply-To: " . $_Email . "\r\n";
					mail($to,$subject,$body,$headers);
				}
				return $this->getBlogEntry($_EntryID);
			}
			else return "\t\t\t<p class=\"errMsg\">An error was encoutered while trying to add your comment. Please try again later.</p>\n" . $this->getBlogEntry($_EntryID);
		}
		else {
			return "\t\t\t<p class=\"errMsg\">Form submission invalid. Please verify the text in the image (note: form validation is case-insensitive), and if you got that right, quit spamming my site!</p>\n" . $this->getBlogEntry($_EntryID);
		}
	}//end AddComent
	
	function EditCommentRender($_CommentID) {
	   if ($objComment = $this->getObject("SELECT * FROM BlogComments WHERE CommentID = '" . $_CommentID . "'")) {
	       $editHTML = "
                <div class=\"UserComments clearfix\">
					<h4>Edit Entry Comment</h4>
					<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">
				    <input type=\"hidden\" name=\"action\" value=\"EditComment\" />
					<div style=\"display: none;\"><input type=\"hidden\" name=\"CommentID\" value=\"" . $_CommentID . "\" /></div>
					<div class=\"row\">
						<span class=\"label\">Name:</span>
						<span class=\"val\"><input type=\"text\" size=\"25\" name=\"Name\" value=\"" . stripslashes($objComment->Name) . "\" /></span>
					</div>
					<div class=\"row\">
						<span class=\"label\">Email:</span>
						<span class=\"val\"><input type=\"text\" size=\"25\" name=\"Email\" value=\"" . stripslashes($objComment->Email) . "\" /></span>
					</div>
					<div class=\"row\">
						<span class=\"label\">Website:</span>
						<span class=\"val\"><input type=\"text\" size=\"25\" name=\"Website\" value=\"" . stripslashes($objComment->Website) . "\" /></span>
					</div>
					<div class=\"row\">
						<span class=\"label\">Comments:</span>
						<span class=\"val\"><textarea name=\"Comments\" rows=\"5\" cols=\"30\">" . stripslashes($objComment->Comments) . "</textarea></span>
					</div>
					<div class=\"row\">
						<span class=\"label\">&nbsp;</span>
						<span class=\"val\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" /></span>
					</div>
					</form>
				</div>\n";
	   	return $editHTML;
	   }
	   else {
	       return "\t\t<p class=\"errMsg\">Comment Not Found</p>\n" . $this->BlogEntryList('Admin');
	   }		
	}//end EditCommentRender
	
	function DeleteCommentRender($_CommentID) {
		if ($objComment = $this->getObject("SELECT Name FROM BlogComments WHERE CommentID = " . $_CommentID)) {
			$deleteHTML = "
			<h3>Delete Entry Comment</h3>

			<h4 class=\"errorMessage\">Delete Entry Comment '<em>" . $objComment->Name ."</em>'</h4>

			<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">
		    <input type=\"hidden\" name=\"action\" value=\"DeleteComment\" />
			<div style=\"display: none;\"><input type=\"hidden\" name=\"CommentID\" value=\"" . $_CommentID . "\" /></div>
			<div class=\"row\">
				<span class=\"label\">
					<input type=\"submit\" name=\"submit\" value=\"YES\" />
				</span>
				<span class=\"val\">
					<input type=\"button\" name=\"clear\" value=\"NO\" onclick=\"history.back();\" />
				</span>
			</div>
			</form>\n";
			return $deleteHTML;
		}
		else {
			return "<p class=\"errorMessage\">Comment Not Found</p>\n" . $this->BlogEntryList('Admin');
		}		
	}//end DeleteCommentRender

	function EditCommentSubmit($_CommentID) {
		if (count($_POST)) {
	            $sql = "
	                UPDATE BlogComments SET
	                    Name = '" . addslashes($_POST['Name']) . "',
	                    Email = '" . addslashes($_POST['Email']) . "',
	                    Website = '" . addslashes($_POST['Website']) . "',
	                    Comments = '" . addslashes($_POST['Comments']) . "'
	                WHERE CommentID = '" . $_POST['CommentID'] . "'";
	            $this->runQuery($sql);
	            if ($this->affectedRows()) return "\t\t\t<p class=\"Message\">Comment Changes Saved Successfully!</p>\n" . $this->BlogEntryList('Admin');
	            else return "\t\t\t<p class=\"errorMessage\">An error occurred while saving changes to Comment</p>\n" . $this->BlogEntryList('Admin');
	        }
	        else {
        	    return "\t\t\t<p class=\"errorMessage\">Invalid form submission</p>\n" . $this->BlogEntryList('Admin');
	        }//end if
	}//end EditCommentSubmit
	
	function DeleteCommentSubmit() {
        if ($_POST['submit'] && $_POST['action'] == 'DeleteComment') {
			if ($this->justQuery("DELETE FROM BlogComments WHERE CommentID = " . $_POST['CommentID'])) {
				return "\t\t\t<p class=\"Message\">Comment Deleted Successfully</p>\n" . $this->BlogEntryList('Admin');
			}
			else {
				return "\t\t\t<p class=\"errorMessage\">Error deleting this Comment: <br /><br />" . mysql_error() . "</p>\n" . $this->BlogEntryList('Admin');
			}
		}
		else {
			return "<p class=\"errorMessage\">Permission Denied to Delete this Comment</p>\n" . $this->BlogEntryList('Admin');
		}
	}//end DeleteCommentSubmit
	
/*****************************
	Entry Admin Functions
******************************/

	function AddEntryRender() {
		if (count($_POST)) {
			$errTitle = ' value="' . stripslashes($_POST['Title']) . '"';
			$errShortDesc = stripslashes($_POST['ShortDesc']);
			$errLongDesc = stripslashes($_POST['LongDesc']);
		}
		$addHTML .= "
			<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">
			<input type=\"hidden\" name=\"action\" value=\"AddEntry\" />
			<div class=\"row\">
				<span class=\"label\">Entry Title:</span>
				<span class=\"val\"><input type=\"text\" size=\"45\" name=\"Title\"" . $errTitle . " /></span>
			</div>
			<div class=\"row\">
				<span class=\"label\">Synopsis:</span>
				<span class=\"val\"><textarea name=\"ShortDesc\" rows=\"2\" cols=\"34\">" . $errShortDesc . "</textarea></span>
			</div>
			<div class=\"row\">
				<span class=\"label\">Entry:</span>
				<span class=\"val\"><textarea name=\"LongDesc\" rows=\"20\" cols=\"34\">" . $errLongDesc . "</textarea></span>
			</div>
			<div class=\"row\">
				<span class=\"label\">Entry Tags:</span>
				<span class=\"val\">" . $this->GetBlogTags('adminadd') . "</span>
			</div>
			<div class=\"row\">
				<span class=\"label\">Email:</span>
				<span class=\"val\"><input type=\"checkbox\" name=\"EmailUpdates\" value=\"" . $this->_UserEmail->Email . "\" /> Keep me informed about comments to this post</span>
			</div>
			<div class=\"row\">
				<span class=\"label\">&nbsp;</span>
				<span class=\"val\"><input type=\"submit\" name=\"Submit\" value=\"Add Entry\" /></span>
			</div>
			</form>";
		return $addHTML;
	}//end AddEntryRender

	function EditEntryRender($_EntryID) {
		if (count($_POST)) {
			$_Title = $_POST['Title'];
			$_ShortDesc = $_POST['ShortDesc'];
			$_LongDesc = $_POST['LongDesc'];
			$_Tags = implode(',',$_POST['Tags']);
			$_Email = $_POST['EmailUpdates'];
		}
		else {
			if ($objEdit = $this->getObject("SELECT * FROM BlogEntries WHERE EntryID = " . $_EntryID)) {
				$_Title = $objEdit->Title;
				$_ShortDesc = $objEdit->ShortDesc;
				$_LongDesc = $objEdit->LongDesc;
				$_Tags = $objEdit->Tags;
				$_Email = $objEdit->EmailUpdatesTo;
			}
			else {
				return "\t\t\t<p class=\"errMsg\">Couldn't find the entry you were looking for</p>\n" . $this->BlogEntryList('Admin');
			}
		}
		$editHTML .= "
			<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">
			<input type=\"hidden\" name=\"action\" value=\"EditEntry\" />
			<div style=\"display: none;\"><input type=\"hidden\" name=\"EntryID\" value=\"" . $_EntryID . "\" /></div>
			<div class=\"row\">
				<span class=\"label\">Entry Title:</span>
				<span class=\"val\"><input type=\"text\" size=\"45\" name=\"Title\" value=\"" . stripslashes($_Title) . "\" /></span>
			</div>
			<div class=\"row\">
				<span class=\"label\">Synopsis:</span>
				<span class=\"val\"><textarea name=\"ShortDesc\" rows=\"2\" cols=\"34\">" . stripslashes($_ShortDesc) . "</textarea></span>
			</div>
			<div class=\"row\">
				<span class=\"label\">Entry:</span>
				<span class=\"val\"><textarea name=\"LongDesc\" rows=\"20\" cols=\"34\">" . stripslashes($_LongDesc) . "</textarea></span>
			</div>
			<div class=\"row\">
				<span class=\"label\">Entry Tags:</span>
				<span class=\"val\">" . $this->GetBlogTags('adminedit',$_Tags) . "</span>
			</div>
			<div class=\"row\">
				<span class=\"label\">Email:</span>
				<span class=\"val\"><input type=\"checkbox\" name=\"EmailUpdates\" value=\"" . $this->_UserEmail->Email . "\"" . ($this->_UserEmail->Email != '' ? " checked=\"checked\"" : "") . " /> Keep me informed about comments to this post</span>
			</div>
			<div class=\"row\">
				<span class=\"label\">&nbsp;</span>
				<span class=\"val\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" />&nbsp;&nbsp;<input type=\"reset\" value=\"Reset\" /></span>
			</div>
			</form>";
		return $editHTML;
	}//end EditEntryRender

	function DeleteEntryRender($_EntryID) {
		if ($objEntry = $this->getObject("SELECT * FROM BlogEntries WHERE EntryID = " . $_EntryID)) {
			$deleteHTML = "
			<h3>Delete Blog Entry</h3>

			<h4 class=\"errMsg\">Delete Blog Entry '<em>" . $objEntry->Title ."</em>'</h4>

			<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">
	        <input type=\"hidden\" name=\"action\" value=\"DeleteEntry\" />
			<div style=\"display: none;\"><input type=\"hidden\" name=\"EntryID\" value=\"" . $_EntryID . "\" /></div>
			<div class=\"row\">
				<span class=\"label\">
					<input type=\"submit\" name=\"submit\" value=\"YES\" />
				</span>
				<span class=\"val\">
					<input type=\"button\" name=\"clear\" value=\"NO\" onclick=\"history.back();\" />
				</span>
			</div>
			</form>\n";
			return $deleteHTML;
		}
		else {
			return "<p class=\"errMsg\">Blog Entry Not Found</p>\n" . $this->BlogEntryList('Admin');
		}
	}//end DeleteEntryRender

	function AddEntrySubmit() {
		if ($_POST['Submit']) {
			if ($_POST['NewTag'] != '') {
				//check for duplicate Tag Name already in DB
				$objCurTag = $this->getObject("SELECT TagID FROM Tags WHERE Name = '" . $_POST['NewTag'] . "'");
				if ($objCurTag->TagID != '') $NewTagID = $objCurTag->TagID;
				else {
					$sql = "INSERT INTO Tags (Name) VALUES('" . addslashes($_POST['NewTag']) . "')";
					$this->runQuery($sql);
					if ($this->affectedRows()) $NewTagID = $this->getID();
				}
			}
			$CSTags = @implode(',',$_POST['Tags']);
			$CSTags .= ($NewTagID != '' ? (strlen($CSTags) > 0 ? "," : '') . $NewTagID : '');
			$sql = "
				INSERT INTO BlogEntries
				(
					UserID,
					PostDate,
					Title,
					ShortDesc,
					LongDesc,
					Tags,
					EmailUpdatesTo
				)
				VALUES (
					" . $this->_UserID . ",
					NOW(),
					'" . addslashes($_POST['Title']) . "',
					'" . addslashes($_POST['ShortDesc']) . "',
					'" . addslashes($_POST['LongDesc']) . "',
					'" . $CSTags . "',
					'" . $_POST['EmailUpdates'] . "'
				)";
				
			if ($this->justQuery($sql)) {
				return "\t\t\t<p class=\"Msg\">Blog Entry '<em>" . stripslashes($_POST['Title']) . "</em>' Added Successfully!</p>\n" . $this->BlogEntryList('Admin');
			}
			else {
				return "\t\t\t<p class=\"errMsg\">Could not add this Entry to the Database: <br /><br />" . mysql_error() . "</p>\n" . $this->AddEntryRender();
			}
		}
		else {
			return "\t\t\t<p class=\"errMsg\">You do not have permission to access this feature</p>\n" . $this->BlogEntryList('Admin');
		}
	}//end AddEntrySubmit

	function EditEntrySubmit() {
		if ($_POST['Submit']) {
			$sql = "
				UPDATE BlogEntries
				SET
					Title = '" . addslashes($_POST['Title']) . "',
					ShortDesc = '" . addslashes($_POST['ShortDesc']) . "',
					LongDesc = '" . addslashes($_POST['LongDesc']) . "',
					WhichBlog = '" . $_POST['WhichBlog'] . "',
					Tags = '" . @implode(',',$_POST['Tags']) . "',
					EditDate = NOW(),
					EmailUpdatesTo = '" . $_POST['EmailUpdates'] . "'
				WHERE EntryID = " . $_POST['EntryID'];
				
			if ($this->justQuery($sql)) {
				return "\t\t\t<p class=\"Msg\">Blog Entry '<em>" . stripslashes($_POST['Title']) . "</em>' Edited Successfully!</p>\n" . $this->BlogEntryList('Admin');
			}
			else {
				return "\t\t\t<p class=\"errMsg\">Could not save changes to this entry: <br /><br />" . mysql_error() . "</p>\n" . $this->EditEntryRender($_POST['EntryID']);
			}
		}
		else {
			return "\t\t\t<p class=\"errMsg\">You do not have permission to access this feature</p>\n" . $this->BlogEntryList('Admin');
		}
	}//end EditEntrySubmit

	function DeleteEntrySubmit() {
		if ($_POST['submit'] && $_POST['action'] == 'DeleteEntry') {
			if ($this->justQuery("DELETE FROM BlogEntries WHERE EntryID = " . $_POST['EntryID'])) {
				return "\t\t\t<p class=\"Msg\">Entry Deleted Successfully</p>\n" . $this->BlogEntryList('Admin');
			}
			else {
				return "\t\t\t<p class=\"errMsg\">Error deleting this Entry: <br /><br />" . mysql_error() . "</p>\n" . $this->BlogEntryList('Admin');
			}
		}
		else {
			return "<p class=\"errMsg\">Permission Denied to Delete this Entry</p>\n" . $this->BlogEntryList('Admin');
		}
	}//end DeleteEntryRender

}//end class

?>
