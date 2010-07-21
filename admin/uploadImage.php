<?php

$CustomStyle = "<link rel=\"stylesheet\" href=\"adminstyle.css\" type=\"text/css\" />

	<style type=\"text/css\">
		pre {
			font: normal 1em Arial, Verdana, sans-serif;
		}

		.FileArray {
			background: #ccc;
			border: 1px solid red;
			padding: 10px;
		}

		.errorMessage {
			color: red;
			font-weight: bold;
			padding: 10px;
			border: 1px solid red;
		}

		.Message {
			font-weight: bold;
			padding: 10px;
			background: #D2BF99;
			border: 1px solid #916C50;
			margin-bottom: 15px;
		}

		.swatch {
			margin: 5px;
			width: 25px;
			border: 1px solid #000;
		}

		.off {
			display: none;
		}
	</style>
";

$CustomScript = "
	<script type=\"text/javascript\">
		function colorSample(which, e, newRGB) {
			if (newRGB == '') {
				if (e.keyCode == 38) which.value = (which.value * 1) + 1;
				else if (e.keyCode == 40) which.value = (which.value * 1) - 1;
				else if (e.keyCode == 33) which.value = (which.value * 1) + 10;
				else if (e.keyCode == 34) which.value = (which.value * 1) - 10;
				r = (document.getElementById('r').value.match(/\D/) ? 0 : document.getElementById('r').value);
				g = (document.getElementById('g').value.match(/\D/) ? 0 : document.getElementById('g').value);
				b = (document.getElementById('b').value.match(/\D/) ? 0 : document.getElementById('b').value);
				if (r > 255) document.getElementById('r').value = 255;
				else if (r < 0 || r == '') document.getElementById('r').value = 0;
				if (g > 255) document.getElementById('g').value = 255;
				else if (g < 0 || g == '') document.getElementById('g').value = 0;
				if (b > 255) document.getElementById('b').value = 255;
				else if (b < 0 || b == '') document.getElementById('b').value = 0;
				document.getElementById('rgb').style.background = 'rgb(' + r + ',' + g + ',' + b + ')';
				if(document.getElementById(document.getElementById('r').value + document.getElementById('g').value + document.getElementById('b').value)) {
					document.getElementById(document.getElementById('r').value + document.getElementById('g').value + document.getElementById('b').value).checked = true;
				}
				else {
					for (var i = 0; i < document.forms[0].elements['swatches'].length; i++) {
						if (document.forms[0].elements['swatches'][i].checked == true) document.forms[0].elements['swatches'][i].checked = false;
					}
				}
			}
			else {
				arrRGB = newRGB.split(',');
				document.getElementById('r').value = arrRGB[0];
				document.getElementById('g').value = arrRGB[1];
				document.getElementById('b').value = arrRGB[2];
				document.getElementById('rgb').style.background = 'rgb(' + newRGB + ')';
			}
		}
	</script>\n";
$PageTitle = "Upload Image";
include_once('popupHeader.php');

print "\t<h2>Upload a New Image</h2>\n\n\t<div id=\"guts\">\n";

if($_SERVER['REQUEST_METHOD'] == "POST") {
	$imgType = $_POST['imgType'];
	include_once("clsImage.php");
	//fix links if imgType is item
	$image = new Image();
	$image->form_image_field = "uploadImage";
	$image->imageRoot = '/home/eamaiar/public_html/images/' . strtolower($imgType);
	$image->imageRootFolder = $image->imageRoot . "/";
	$image->imageMediumFolder = $image->imageRoot . "/md/";
	$image->imageThumbFolder = $image->imageRoot . "/tn/";
	$image->max_image_px = 500;
	$image->medium_image_px = 200;
	$image->resizeOrKeep = $_POST['resizeOrKeep'];
	$image->imageLargePreserveAspectRatio = ($_POST['imageLargePreserveAspectRatio'] == "TRUE" ? TRUE : FALSE);
	$image->fillRGB = "'" . $_POST['r'] . " " . $_POST['g'] . " " . $_POST['b'] . "'";
	$image->imageLargeCropSquare = ($_POST['imageLargePreserveAspectRatio'] == "CROP" ? TRUE : FALSE);
	$image->imageMediumSharpen = "100 0.5 3";
	$image->Initialize();
	if($image->Upload()) {
		echo "
	<h3>$image->imageName</h3>

	<hr />
	<p class=\"Message\">Large, Medium, and Thumbnail Images created succesfully</p>
	<p>
		Large Image:<br /><img src=\"/images/" . strtolower($imgType) . "/$image->imageName?" . time() . "\" align=\"middle\" border=\"1\"><br />
		[ <a href=\"javascript: void(0);\" onclick=\"window.opener.updateImage('" . $image->imageName . "', '/images/" . $imgType . "/thumb/" . $image->imageName . "'); window.close();\" title=\"Use this Image as the current " . $_POST['oldImgType'] . " image\">Use This Image</a> ]
	<hr />\n";
	}
	else {
		echo "
	<p class=\"errorMessage\">" . implode($image->errorMessage, "</p>\n\t\t\t<p class=\"errorMessage\">") . "</p>

	<div class=\"FileArray\">
		<span style=\"font-weight: bold;\">\$_FILES Array:</span><br />
		<pre>\n";
	print_r($_FILES);
		echo "
		</pre>
	</div>

	<hr />\n";
	}
}
	?>
		<form action="./uploadImage.php?imgType=<?= $_GET['imgType']; ?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="oldImgType" value="<?= $_GET['imgType']; ?>">
		<div class="row">
		<span class="label">File:</span>
			<span class="val">
				<input type="file" name="uploadImage">
			</span>
		</div>
		<div class="row">
			<span class="label">Resize:</span>
			<span class="val">
				<input type="radio" name="resizeOrKeep" value="resize" checked="checked" id="checkResize" /> <label for="checkResize">Resize</label><br />
				<input type="radio" name="resizeOrKeep" value="keep" id="checkKeep"> <label for="checkKeep" />Do NOT Resize</label> *
			</span>
		</div>
		<div class="row">
			<span class="label">Image Handling:</span>
			<span class="val">
				<input type="radio" name="imageLargePreserveAspectRatio" value="TRUE" checked="checked" /> <label for="checkPAR">Preserve Aspect Ratio</label> *<br />
				<input type="radio" name="imageLargePreserveAspectRatio" value="FALSE" /> <label for="checkPAR">Pad With Color</label><br />
				<input type="radio" name="imageLargePreserveAspectRatio" value="CROP" id="checkCSC" /> <label for="checkCSC">Crop Square From Center</label>
			</span>
		</div>
		<div class="row">
			<span class="label">Padded Color:</span>
			<span class="val">
				R=<input type="text" name="r" id="r" onfocus="this.select();" onkeyup="colorSample(this,event,'');" value="229" style="width: 25px;">
				G=<input type="text" name="g" id="g" onfocus="this.select();" onkeyup="colorSample(this,event,'');" value="218" style="width: 25px;">
				B=<input type="text" name="b" id="b" onfocus="this.select();" onkeyup="colorSample(this,event,'');" value="193" style="width: 25px;">
				<input type="text" size="5" id="rgb" disabled="disabled" style="width: 25px; background: rgb(229,218,193); border: 1px solid #000;"><br />
				<span style="font-size: .8em; line-height: 100%;">PgUp/PgDn = Increment/Decrement value by 10 <br />
				ArrowUp/ArrowDn = Increment/Decrement value by 1<br />
				[ <a href="javascript: void(0);" onclick="if (document.getElementById('colorSwatches').className == 'row off') { document.getElementById('colorSwatches').className = 'row'; } else { document.getElementById('colorSwatches').className = 'row off'; }" title="Show/Hide Color Swatches">Show/Hide Color Swatches</a> ]</span>
			</span>
		</div>
		<div class="row">
			<span class="label">Image Type:</span>
			<span class="val">
				<input type="radio" name="imgType" value="Blog" id="checkblog"<?= ($_GET['imgType'] == 'blog' ? ' checked="checked"' : ''); ?> /> <label for="checkblog">Blog</label><br />
				<input type="radio" name="imgType" value="Gallery" id="checkgallery"<?= ($_GET['imgType'] == 'gallery' ? ' checked="checked"' : ''); ?> /> <label for="checkgallery">Gallery</label><br />
				<input type="radio" name="imgType" value="Portfolio" id="checkportfolio"<?= ($_GET['imgType'] == 'portfolio' ? ' checked="checked"' : ''); ?> /> <label for="checkportfolio">Portfolio</label><br />
			</span>
		</div>
		<div id="colorSwatches" class="row off">
			<span class="label">Color Swatches:</span>
			<span class="val">
				<h4>Brownstone Colors</h4>
				<input type="radio" name="swatches" onclick="colorSample(this,event,'249,247,245');" id="249247245" /> <label for="249247245"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(249,247,245);" /></label>
				<input type="radio" name="swatches" onclick="colorSample(this,event,'241,238,211');" id="241238211" /> <label for="241238211"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(241,238,211);" /></label>
				<input type="radio" name="swatches" onclick="colorSample(this,event,'229,218,193');" id="229218193" checked="checked" /> <label for="229218193"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(229,218,193);" /></label>
				<input type="radio" name="swatches" onclick="colorSample(this,event,'210,191,153');" id="210191153" /> <label for="210191153"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(210,191,153);" /></label><br />
				<input type="radio" name="swatches" onclick="colorSample(this,event,'145,108,80');" id="14510880" /> <label for="14510880"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(145,108,80);" /></label>
				<input type="radio" name="swatches" onclick="colorSample(this,event,'112,98,72');" id="1129872" /> <label for="1129872"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(112,98,72);" /></label>
				<input type="radio" name="swatches" onclick="colorSample(this,event,'171,45,0');" id="171450" /> <label for="171450"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(171,45,0);" /></label>
				<input type="radio" name="swatches" onclick="colorSample(this,event,'121,0,0');" id="12100" /> <label for="12100"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(121,0,0);" /></label>
				<hr />
				<h4>Default Colors</h4>
				<input type="radio" name="swatches" onclick="colorSample(this,event,'255,255,255');" id="255255255" /> <label for="255255255"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(255,255,255);" /></label>
				<input type="radio" name="swatches" onclick="colorSample(this,event,'255,0,0');" id="25500" /> <label for="25500"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(255,0,0);" /></label>
				<input type="radio" name="swatches" onclick="colorSample(this,event,'0,255,0');" id="02550" /> <label for="02550"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(0,255,0);" /></label>
				<input type="radio" name="swatches" onclick="colorSample(this,event,'0,0,255');" id="00255" /> <label for="00255"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(0,0,255);" /></label><br />
				<input type="radio" name="swatches" onclick="colorSample(this,event,'255,255,0');" id="2552550" /> <label for="2552550"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(255,255,0);" /></label>
				<input type="radio" name="swatches" onclick="colorSample(this,event,'255,0,255');" id="2550255" /> <label for="2550255"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(255,0,255);" /></label>
				<input type="radio" name="swatches" onclick="colorSample(this,event,'0,255,255');" id="0255255" /> <label for="0255255"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(0,255,255);" /></label>
				<input type="radio" name="swatches" onclick="colorSample(this,event,'200,200,200');" id="200200200" /> <label for="200200200"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(200,200,200);" /></label><br />
				<input type="radio" name="swatches" onclick="colorSample(this,event,'150,150,150');" id="150150150" /> <label for="150150150"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(150,150,150);" /></label>
				<input type="radio" name="swatches" onclick="colorSample(this,event,'100,100,100');" id="100100100" /> <label for="100100100"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(100,100,100);" /></label>
				<input type="radio" name="swatches" onclick="colorSample(this,event,'50,50,50');" id="505050" /> <label for="505050"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(50,50,50);" /></label>
				<input type="radio" name="swatches" onclick="colorSample(this,event,'0,0,0');" id="000" /> <label for="000"><input type="text" size="5" disabled="disabled" class="swatch" style="background: rgb(0,0,0);" /></label>
			</span>
		</div>
		<div class="row">
			<span class="label">&nbsp;</span>
			<span class="val">
				<input type="submit" value="Upload">
			</span>
		</div>
		<br class="clear" />
		</form>

		<p style="font-size: .8em; line-height: 100%;">* Setting any of these options will only affect the Large image. Medium and Thumbnail images will be resized, and padded with the default color (the current background color) to make the image square. To set the image padding color, change the R, G, and B Fields to the desired values. If you aren't sure which values you would like, there are multiple pre-made RGB combinations that are available by clicking 'Show/Hide Color Swatches'.</p>

	</div>

	<p>[ <a href="chooseImage.php?imgType=<?= $_GET['imgType']; ?>/" title="Choose your uploaded Image">Choose Uploaded Image</a> ][ <a href="javascript: void(0);" onclick="window.close();" title="Close this Window">Close</a> ]</p>

<?php include_once('popupFooter.php'); ?>
