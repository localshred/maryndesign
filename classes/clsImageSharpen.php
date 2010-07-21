<?php
/**
* phpUnsharpMask by Torstein Hønsi thoensi@netcom.no
*
*    Amount:    (typically 50 - 200)
*    Radius:    (typically 0.5 - 1)
* Threshold:    (typically 0 - 5)
*/

function UnsharpMask($img, $amount, $radius, $threshold) { // $img is an image handle returned by imgcreatetruecolor. MUST BE TRUECOLOR IMAGE.
	// Attempt to calibrate the parameters to Photoshop:
	if($amount > 500) $amount = 500;
	$amount = $amount * 0.016;
	if($radius > 50) $radius = 50;
	$radius = $radius * 2;
	if($threshold > 255) $threshold = 255;
	
	$radius = abs(round($radius));
	if($radius == 0) {
		return $img;
		imagedestroy($img);
		break;
	}
	
	$w = imagesx($img);
	$h = imagesy($img);
	$imgCanvas = imagecreatetruecolor($w, $h);
	$imgCanvas2 = imagecreatetruecolor($w, $h);
	$imgBlur = imagecreatetruecolor($w, $h);
	$imgBlur2 = imagecreatetruecolor($w, $h);
	imagecopy($imgCanvas, $img, 0, 0, 0, 0, $w, $h);
	imagecopy($imgCanvas2, $img, 0, 0, 0, 0, $w, $h);
	
	/*
		Gaussian blur matrix:
		                     
		     1    2    1     
		     2    4    2     
		     1    2    1     
		                     
		
		Move copies of the image around one pixel at the time and merge them with weight
		according to the matrix. The same matrix is simply repeated for higher radii.
	*/
	for($i = 0; $i < $radius; $i++) {
		imagecopy($imgBlur, $imgCanvas, 0, 0, 1, 1, $w - 1, $h - 1);            // up left
		imagecopymerge($imgBlur, $imgCanvas, 1, 1, 0, 0, $w, $h, 50);           // down right
		imagecopymerge($imgBlur, $imgCanvas, 0, 1, 1, 0, $w - 1, $h, 33.33333); // down left
		imagecopymerge($imgBlur, $imgCanvas, 1, 0, 0, 1, $w, $h - 1, 25);       // up right
		imagecopymerge($imgBlur, $imgCanvas, 0, 0, 1, 0, $w - 1, $h, 33.33333); // left
		imagecopymerge($imgBlur, $imgCanvas, 1, 0, 0, 0, $w, $h, 25);           // right
		imagecopymerge($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 20 );      // up
		imagecopymerge($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 16.666667);    // down
		imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 0, $w, $h, 50);          // center
		imagecopy($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h);
		/*
			During the loop above the blurred copy darkens, possibly due to a roundoff error.
			Therefore the sharp picture has to go through the same loop to produce a similar
			image for comparison. This is not a good thing, as processing time increases heavily.
		*/
		imagecopy($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h);
		imagecopymerge($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 50);
		imagecopymerge($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 33.33333);
		imagecopymerge($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 25);
		imagecopymerge($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 33.33333);
		imagecopymerge($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 25);
		imagecopymerge($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 20 );
		imagecopymerge($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 16.666667);
		imagecopymerge($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 50);
		imagecopy($imgCanvas2, $imgBlur2, 0, 0, 0, 0, $w, $h);
	}
	
	/*
		Calculate the difference between the blurred pixels and the original and set the pixels
	*/
	for($x = 0; $x < $w; $x++) {     // each row
		for($y = 0; $y < $h; $y++) { // each pixel
			$rgbOrig = imagecolorat($imgCanvas2, $x, $y);
			$rOrig = (($rgbOrig >> 16) & 0xFF);
			$gOrig = (($rgbOrig >> 8) & 0xFF);
			$bOrig = ($rgbOrig & 0xFF);
			
			$rgbBlur = ImageColorAt($imgCanvas, $x, $y);
			$rBlur = (($rgbBlur >> 16) & 0xFF);
			$gBlur = (($rgbBlur >> 8) & 0xFF);
			$bBlur = ($rgbBlur & 0xFF);
			
			/*
				When the masked pixels differ less from the original than the
				threshold specifies, they are set to their original value.
			*/
			$rNew = (abs($rOrig - $rBlur) >= $threshold && (($x+1) < $w && ($y+1) < $h))
				? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig))
				: $rOrig;
			$gNew = (abs($gOrig - $gBlur) >= $threshold && (($x+1) < $w && ($y+1) < $h))
				? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig))
				: $gOrig;
			$bNew = (abs($bOrig - $bBlur) >= $threshold && (($x+1) < $w && ($y+1) < $h))
				? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig))
				: $bOrig;
			
			if(($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) {
				$pixCol = imagecolorallocate($img, $rNew, $gNew, $bNew);
				imagesetpixel($img, $x, $y, $pixCol);
			}
		}
	}
	
	imagedestroy($imgCanvas);
	imagedestroy($imgCanvas2);
	imagedestroy($imgBlur);
	imagedestroy($imgBlur2);
	
	return $img;
}
?> 