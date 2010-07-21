<?php
	$arrViews = array('retro','grunge');
	if (isset($_GET['view']) && $_GET['view'] != '') {
		if (in_array($_GET['view'],$arrViews)) {
			$_SESSION['view'] = $_GET['view'];
			if (!$_COOKIE['MDTemplateView'] || $_COOKIE['MDTemplateView'] != $_SESSION['view']) setcookie('MDTemplateView',$_SESSION['view'],time()+1296000,'/','.maryndesign.com');
		}
	}
	elseif($_COOKIE['MDTemplateView'] != '') {
		if (in_array($_COOKIE['MDTemplateView'],$arrViews)) {
			$_SESSION['view'] = $_COOKIE['MDTemplateView'];
			if ($_COOKIE['MDTemplateView'] != $_SESSION['view']) setcookie('MDTemplateView',$_SESSION['view'],time()+1296000,'/','.maryndesign.com');
		}
	}
	else {
		if (!$_SESSION['view']) $_SESSION['view'] = 'grunge';
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>MarynDesign.com<?= (isset($PageTitle) && strlen($PageTitle) > 0 ? ' ~ ' . $PageTitle : ''); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<link rel="stylesheet" href="/style-<?= ($_SESSION['view'] != '' ? $_SESSION['view'] : 'retro'); ?>.css" type="text/css" />
	<link rel="stylesheet" href="/momentum/styles/master.css" type="text/css" />

	<!-- compliance patch for microsoft browsers -->
	<!--[if lt IE 7]><script src="/ie7/ie7-standard-p.js" type="text/javascript"></script><![endif]-->
	<?= (isset($CustomScript) ? $CustomScript : ''); ?>
	<?= (isset($CustomStyle) ? $CustomStyle : ''); ?>
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
		<h3>Momentum Route Administration</h3>
	</div>
	
	<div id="menu" class="clearfix">
		<ul class="publicMenu clearfix">
<?php
include_once('clsMenu.php');
if (strstr($_SERVER['SCRIPT_NAME'], 'momentum/login.php')) {
	print BuildMenu('Home');
} else {
	print BuildMenu('Momentum','momentum',true);
}
?>
		</ul>

	</div>
	
	<div id="guts" class="clearfix">