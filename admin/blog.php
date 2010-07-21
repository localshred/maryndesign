<?php
$PageTitle = "Only Nerds Blog";
if ($_GET['action'] == 'AddEntry' || $_GET['action'] == 'EditEntry') {
	$file = '/home/eamaiar/public_html/scripts/spellchecker.js';
	$script = fopen($file,'r');
	$CustomScript = fread($script, filesize($file));
	fclose($script);
}
include_once("adminHeader.php");
?>
		<div>
			<p>&nbsp;</p>

<?php
include_once('clsBlog.php');
$clsBlog = new Blog();
if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['id'] && $_GET['showEntry'] == 1) print $clsBlog->GetBlogEntry($_GET['id']);
else if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['v']) print $clsBlog->BlogEntryList('Admin','',$_GET['month'],$_GET['year'],$_GET['user']);
else if ($_REQUEST['action']) {
	$whatAction = $_REQUEST['action'] . ($_SERVER['REQUEST_METHOD'] == 'GET' ? 'Render' : 'Submit');
	print $clsBlog->$whatAction($_GET['id']);
}
if (count(${'_' . $_SERVER['REQUEST_METHOD']}) == 0) print $clsBlog->BlogEntryList('Admin');
?>
		</div>

<?php include_once("adminFooter.php"); ?>