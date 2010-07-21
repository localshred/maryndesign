<?php

$CustomStyle = "<link rel=\"stylesheet\" href=\"adminstyle.css\" type=\"text/css\" />\n";
$PageTitle = "Choose Image";
include_once('popupHeader.php');

//Types that can be passed to this var: 'category/', 'product/',
// 'item/' (which is only used to print the title... all paths point to product/), and
// '' (for main site images)
$BaseDir = '/home/eamaiar/public_html';
$ImageDir = '/images/' . $_GET['imgType'] . 'tn/';
$FullDir = $BaseDir.$ImageDir;
$ImageType = str_replace('/','',$_GET['imgType']);

print "
	<h2>Choose " . ucfirst($ImageType) . " Image</h2>

	<form method=\"post\">\n";

$arrImageList = preg_split("/\s+/",`ls $FullDir`,-1,PREG_SPLIT_NO_EMPTY);
sort($arrImageList);
foreach ($arrImageList as $key => $FileName) {
	if ($_GET['type'] != 'color/') $onclick = "window.opener.updateImage(this.value, '" . $ImageDir . "' + this.value); window.close();";
	else $onclick = "window.opener.showSwatch('New Swatch','',this.value); window.opener.document.forms[0].elements['ColorImage'].value = this.value; window.close();";
	print "
	<div class=\"ImageBox left\">
		<label for=\"" . $FileName . "\">
			<img src=\"" . $ImageDir . $FileName . "\" alt=\"" . $FileName . "\" /><br />
			<span class=\"ImageName\"><input type=\"radio\" name=\"ChooseImage\" value=\"" . $FileName . "\" id=\"" . $FileName . "\" onclick=\"" . $onclick . "\" /> " . $FileName . "</span>
		</label>
	</div>\n";
}

print "\t\t<br class=\"clear\" />\n".
			"\t</form>\n".
			"\t\t\t<p>[ <a href=\"uploadImage.php?imgType=" . $ImageType . "\" title=\"Upload a New Image\">Upload New Image</a> ][ <a href=\"javascript: void(0);\" onclick=\"window.close();\" title=\"Close this Window\">Close</a> ]</p>\n";

include_once('popupFooter.php');
?>
