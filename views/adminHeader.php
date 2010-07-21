<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>MarynDesign.com Admin<?= (strlen($PageTitle) > 0 ? ' ~ ' . $PageTitle : ''); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<link rel="stylesheet" href="/style-retro.css" type="text/css" />
	<link rel="stylesheet" href="/admin/adminStyle.css" type="text/css" />
	<!-- compliance patch for microsoft browsers -->
	<!--[if lt IE 7]><script src="/ie7/ie7-standard-p.js" type="text/javascript"></script><![endif]-->
	<?= $CustomScript; ?>
	<?= $CustomStyle; ?>
</head>

<body>

<div id="wrapper">
	<div id="header">
		<h3>MarynDesign.com Administration</h3>
	</div>
	
	<div id="menu">
		<ul class="clearfix">
<?php
include_once('clsMenu.php');
print BuildMenu("Admin");
?>
		</ul>
	</div>

	<div id="sidebar">
		<h5>AdminNav</h5>
		<ul class="list">
<?php
include_once('clsMenu.php');
print BuildMenu('Admin','menu',TRUE);
?>
		</ul>
	</div>
	
	<div id="guts" class="clearfix">