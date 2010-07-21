<?php
	//set this var to false when we don't want people to randomly stumble onto this page
	$allow = true;

	if ($allow == true) {
		$PageTitle = "Create a new User Account";
		include_once('publicHeader.php');
		if (count($_POST)) {
			include_once('dbClass.php');
			$db = new dbClass();
			
			$user = addslashes($_POST['user']);
			$pass = $_POST['passwd'];
			$name = addslashes($_POST['name']);
			$accesslevel = '1';
			
			$sql = "
				INSERT INTO AdminUsers
					(
						UserName,
						PassWord,
						Name,
						AccessLevel
					)
				VALUES
					(
						'$user',
						ENCODE('" . $pass . "','wH!t3W|za4D'),
						'$name',
						'$accesslevel'
					)";
			
			$db->runQuery($sql);
			if ($db->affectedRows()) {
				print "\t\t\t<p class=\"Message\">User added Successfully. Click <a href=\"/admin/login.php\" title=\"Login Here\">Here</a> to Login</p>\n";
			}
			else {
				print "\t\t\t<p class=\"errorMessage\">Error while trying to add User to Database</p>\n";
			}
		}
		else {
?>
		<div class="clearfix">
			<h2>Create a New User</h2>

			<?= $errmsg; ?>
			<form method="post">
			<div class="row">
				<span class="label">Name:</span>
				<span class="val"><input type="text" name="name" size="15" /></span>
			</div>
			<div class="row">
				<span class="label">Username:</span>
				<span class="val"><input type="text" name="user" size="15" /></span>
			</div>
			<div class="row">
				<span class="label">Password:</span>
				<span class="val"><input type="password" name="passwd" size="15" maxlength="8" /></span>
			</div>
			<div class="row">
				<span class="label">&nbsp;</span>
				<span class="val"><input type="submit" name="submit" value="Login" /></span>
			</div>
			<div class="row">&nbsp;</div>
			</form>
		</div>
<?php
		}
		include_once('publicFooter.php');
	}
	else {
		header("Location: /error.php?error=other");
	}
?>