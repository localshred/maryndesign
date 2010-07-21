<?php
	$WhichBlog = $WhichBlogMenu == 'public' || !$WhichBlogMenu ? 'public' : 'uvsc';
	$arrViews = array('retro','grunge');
	if (isset($_GET['view']) && $_GET['view'] != '') {
		if (in_array($_GET['view'],$arrViews)) {
			$_SESSION['view'] = $_GET['view'];
			if (!$_COOKIE['MDTemplateView'] || $_COOKIE['MDTemplateView'] != $_SESSION['view']) setcookie('MDTemplateView',$_SESSION['view'],time()+1296000,'/','.maryndesign.com');
		}
	}
	elseif(isset($_COOKIE['MDTemplateView']) && $_COOKIE['MDTemplateView'] != '') {
		if (in_array($_COOKIE['MDTemplateView'],$arrViews)) {
			$_SESSION['view'] = $_COOKIE['MDTemplateView'];
			if ($_COOKIE['MDTemplateView'] != $_SESSION['view']) setcookie('MDTemplateView',$_SESSION['view'],time()+1296000,'/','.maryndesign.com');
		}
	}
	else {
		if (!isset($_SESSION['view'])) $_SESSION['view'] = 'retro';
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>MarynDesign.com<?= (strlen($PageTitle) > 0 ? ' ~ ' . $PageTitle : ''); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<link rel="stylesheet" href="/style-<?= ($_SESSION['view'] != '' ? $_SESSION['view'] : 'retro'); ?>.css" type="text/css" />
	<link rel="alternate" type="application/rss+xml" title="MarynDesign.com Blog" href="/feeds/<?= ($WhichBlog == 'uvsc' ? 'uvsc' : ''); ?>blog.xml" />
	<link rel="alternate" type="application/rss+xml" title="MarynDesign.com Blog Comments" href="/feeds/<?= ($WhichBlog == 'uvsc' ? 'uvsc' : ''); ?>comments.xml" />

	<!-- compliance patch for microsoft browsers -->
	<!--[if lt IE 7]><script src="/ie7/ie7-standard-p.js" type="text/javascript"></script><![endif]-->
	<?= $CustomScript; ?>
	<?= $CustomStyle; ?>
</head>

<body>

<div id="topbar">
	<h5>Change Design:</h5> 
<?php
foreach($arrViews as $view) {
	if ($view == $_SESSION['view'] || ($_SESSION['view'] == '' && $view == 'retro')) print "<span class=\"curView\">" . $view . "</span>";
	else print "<a href=\"" . $_SERVER['PHP_SELF'] . ($_SERVER['QUERY_STRING'] != '' ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) . '&amp;view=' . htmlspecialchars($view) : '?view=' . htmlspecialchars($view)) . "\" title=\"Restyle as '" . $view . "'\">" . $view . "</a>";
}
?>
</div>

<div id="wrapper">
	<div id="header">
<?php
if (count($_GET)) {
	if ($_GET['tag'] || $_GET['user'] || $_GET['showEntry']) {
		include_once('dbClass.php');
		$db = new dbClass();
	}
	print "<h3>";
	if ($_GET['showEntry'] == 1) {
		$objEntry = $db->getObject("SELECT Title FROM BlogEntries WHERE EntryID = " . $_GET['id']);
		print "MarynDesign.com :: " . $objEntry->Title;
	}
	if ($_GET['tag'] != '') {
		print "Blogs";
		$objTag = $db->getObject("SELECT Name FROM Tags WHERE TagID = " . $_GET['tag']);
		print " tagged as '" . $objTag->Name . "'<br /><span class=\"rmfilter\">[ <a href=\"" . ($WhichBlog == 'uvsc' ? '/blog' : '') . "/\">Clear Tag Filter</a> ]</span>";
	}
	if ($_GET['user'] != '') {
		if ($_GET['tag'] == '') print "Blogs";
		$objUser = $db->getObject("SELECT Name FROM AdminUsers WHERE UserID = " . $_GET['user']);
		print " posted by " . $objUser->Name;
	}
	if ($_GET['month'] != '') print " in " . $_GET['month'] . "/" . ($_GET['year'] != '' ? $_GET['year'] : date('Y'));
	print "</h3>";
}
elseif (!count($_GET) && (strstr('contact.php',$_SERVER['SCRIPT_NAME']) || strstr('admin',$_SERVER['SCRIPT_NAME']))) {
	//contact page header?
}
?>
	</div>
	
	<div id="menu" class="clearfix">
		<ul class="left <?= $WhichBlog . "Menu"; ?>"><?php
include_once('clsMenu.php');
print BuildMenu('Home',$WhichBlog);
?></ul>
	</div>
	
	<div id="guts" class="clearfix">
