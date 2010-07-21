<?php
//	if ($_GET['showEntry']) include_once("captcha.php");
	$PageTitle = "The Neilsen Blog";
	$WhichBlogMenu = "";
	$CustomStyle = "";
	$CustomScript = "";
	include_once('publicHeader.php');
	include_once('clsBlog.php');
	$clsBlog = new Blog();
?>

<!-- Above BLOG Text -->

<?php
	if ($_SERVER['REQUEST_METHOD'] == 'POST') print $clsBlog->AddComment($_POST['EntryID'],$_POST['Comments'],$_POST['Name'],$_POST['Email'],$_POST['Website']);
	else if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['id'] && $_GET['showEntry']) print $clsBlog->GetBlogEntry($_GET['id']);
	else if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['v']) print $clsBlog->BlogEntryList('Public','',$_GET['month'],$_GET['year'],$_GET['user']);
	else if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['tag']) print $clsBlog->BlogEntryList('Public','',$_GET['month'],$_GET['year'],'',$_GET['tag']);
	else print "\t\t\t<h2>The Latest Happenings</h2>\n\n" . $clsBlog->BlogEntryList('Public','',$_GET['month'],$_GET['year'],$_GET['user']);
	include_once('publicFooter.php');
?>
<!-- Below BLOG Text -->