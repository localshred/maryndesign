<?php
	$PageTitle = "Admin Images";
	include_once('adminHeader.php');
?>

<h3 class="errorMessage">This isn't working yet... so don't use it... yet...</h3>

<p>Do some kind of image stuff here</p>

<p>
	<a href="javascript: void(0);" onclick="window.open('uploadImage.php?imgType=blog','','top=100,left=100,width=800,height=600,menubar=yes,location=yes,resizable=yes,scrollbars=yes,status=yes');" /> <label for="new">Upload Blog Image</label><br />
	<a href="javascript: void(0);" onclick="window.open('uploadImage.php?imgType=gallery','','top=100,left=100,width=800,height=600,menubar=yes,location=yes,resizable=yes,scrollbars=yes,status=yes');" /> <label for="new">Upload Gallery Image</label><br />
	<a href="javascript: void(0);" onclick="window.open('uploadImage.php?imgType=portfolio','','top=100,left=100,width=800,height=600,menubar=yes,location=yes,resizable=yes,scrollbars=yes,status=yes');" /> <label for="new">Upload Portoflio Image</label>
</p>


<?php include_once('adminFooter.php'); ?>
