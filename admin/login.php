<?php
include_once('dbClass.php');
$db = new dbClass();
if ($_POST['submit']) {
	$errmsg = $_POST['user'] ? '' : "<p class=\"errorMessage\">Please Specify a User Name</p>\n\n";
	$errmsg .= $_POST['passwd'] ? '' : "<p class=\"errorMessage\">Please Specify a Password</p>\n\n";
	if ($errmsg == '') {
		$sql = "
		SELECT
			UserName,
			UserID,
			AccessLevel
		FROM AdminUsers
			WHERE UserName = '" . $_POST['user'] ."'
				AND DECODE(PassWord,'wH!t3W|za4D') = '" . $_POST['passwd'] . "'";
//			print "<pre>$sql</pre>";
//			exit;
		if ($objUser = $db->getObject($sql)) {
			$_SESSION['UserName'] = $objUser->UserName;
			$_SESSION['UserID'] = $objUser->UserID;
			$_SESSION['UserAccess'] = $objUser->AccessLevel;
			session_write_close();
			header("Location: /admin/blog.php");
			exit;
		}
		else {
			$errmsg .= "<p class=\"errorMessage\">Authentication Failed, Please Try Again.</p>\n\n";
		}
	}
}
$PageTitle = "Login to Admin Section";
include_once('publicHeader.php');
?>
		<div>
			<p><img src="/images/adminlogin.gif" alt="Login to the Admin Section" /></p>

			<?= $errmsg; ?>
			<?php if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['logout'] == 1) print "<p class=\"Message\">Logout Successful</p>\n\n"; ?>
			<form method="post">
			<div class="row">
				<span class="label">User:</span>
				<span class="val"><input type="text" name="user" size="15" /></span>
			</div>
			<div class="row">
				<span class="label">Pass:</span>
				<span class="val"><input type="password" name="passwd" size="15" /></span>
			</div>
			<div class="row">
				<span class="label">&nbsp;</span>
				<span class="val"><input type="submit" name="submit" value="Login" /></span>
			</div>
			<div class="row">&nbsp;</div>
			</form>
		</div>
		<br class="clear" />

<?php include_once('publicFooter.php'); ?>
