<?php
$PageTitle = "My User Administration";
$CustomScript = "";
$CustomStyle = "";
include_once("adminHeader.php");
?>
		<div>
			<h2>User Administration</h2>
<?php
include_once('../classes/clsUser.php');
$clsBlog = new User($_REQUEST['id']);
if ($_REQUEST['action']) {
	$whatAction = $_REQUEST['action'] . ($_SERVER['REQUEST_METHOD'] == 'GET' ? 'Render' : 'Submit');
	print $clsBlog->$whatAction($_GET['id']);
}
if (count(${'_' . $_SERVER['REQUEST_METHOD']}) == 0) print $clsBlog->GetUsers();
?>
		</div>

<?php include_once("adminFooter.php"); ?>
