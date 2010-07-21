<?php

/**
* Image Parser and Automatic Resizer
*
* Example usage:
*     include_once("./clsImage.php");
*     $image = new Image();
*     $image->form_image_field = "img1";
*     $image->thumb_image_px = -1;
*     $image->resizeOrKeep = "keep";
*     $image->Initialize();
*     $image->Upload();
*
*/

class Image {
	
	/**
	* Available Class Variables:
	*
	* var form_image_field
	*     The name of the form input field (type 'file') which is used for uploading the image itself. Default: string 'image'
	* var max_file_size
	*     Maximum allowed file size (in bytes) for an uploaded image. Upload() returns false if file size is exceeded. Default: int '83886080'
	* var max_image_px
	*     Maximum allowed dimensions for an uploaded image. If image height or width exceeds this value, image will be automatically resized. (See also resizeOrKeep below.) Default: int '500'
	* var medium_image_px
	*     If set, a medium size image will be created with these dimensions and uploaded to imageMediumFolder (see below). Set to -1 to disable. Default: int '200'
	* var thumb_image_px
	*     If set, a thumbnail image will be created with these dimensions and uploaded to imageThumbFolder (see below). Set to -1 to disable. Default: int '64'
	* var imageRootFolder
	*     Path to the folder to which the uploaded (and possibly resized) image will be saved. Folder will be created if non-existant. Default: string './images/'
	* var imageMediumFolder
	*     Path to the folder where medium size images will be saved. Folder will be created if non-existant. Default: string './images/medium/'
	* var imageThumbFolder
	*     Path to the folder where thumbnail images will be saved. Folder will be created if non-existant. Default: string './images/thumb/'
	* var imageMediumSharpen
	*     Allows you to sharpen the shrunk medium size image. Expects a triplet of three values: Amount (typically 50 - 200), Radius (typically 0.5 - 1), Threshold (typically 0 - 5). Default: boolean 'FALSE'
	* var imageThumbSharpen
	*     Allows you to sharpen the shrunk thumbnail image. Expects a triplet of three values: Amount (typically 50 - 200), Radius (typically 0.5 - 1), Threshold (typically 0 - 5). Default: string '80 0.5 3'
	* var resizeOrKeep
	*     This enumerated directive ('resize', 'keep') allows you to choose whether a file exceeding the defined max_image_px should be allowed to be uploaded as-is ('keep'), or whether to resize the image ('resize'). Default: string 'resize'
	* var imageLargeCropSquare
	*     Set to 'TRUE' if you want to produce a square image without fill color padding. An as-large-as-possible square will be cropped from the center of the source image. Assumes 'resizeOrKeep' is set to 'resize' (= default). Incompatible with 'imageLargePreserveAspectRatio'. Default: boolean 'FALSE'
	* var imageLargePreserveAspectRatio
	*     Determines whether the aspect ratio should be preserved for the large image, or whether the image should be padded (See fillRGB) to acheive a square image. Medium size images and thumbnails are always padded. Incompatible with 'imageLargeCropSquare'. Default: boolean 'TRUE'
	* var fillRGB
	*     RGB triplet which determines the fill color used when padding images. Default: string '255 255 255'
	* var renameOnImageTypeChanged
	*     If the image type changes (e.g. from a non-standard web format such as BMP to a widely supported format such as JPG), should the file extension also be changed to reflect the change? Default: boolean 'TRUE'
	*
	* Returned Data of Interest
	*
	* function Upload()
	*     Returns TRUE on success; FALSE on failure.
	* var errorMessage
	*     Contains descriptive error information on 'Upload()' failure.
	* var imageResized
	*     Available after calling 'Upload()'. Contains TRUE if uploaded source image was successfully resized; FALSE otherwise.
	* var imageTypeChanged
	*     Available after calling 'Upload()'. If the image type changed (e.g. from a non-standard web format such as BMP to a widely supported format such as JPG) this variable will contain the type change in the format 'OLD->NEW' (e.g. 'BMP->JPG' or 'TIF[F]->JPG').
	*/
	
	// User defined directives
	var $form_image_field = "uploadImage";
	var $max_file_size = 83886080;
	var $max_image_px = 500;
	var $medium_image_px = 200;
	var $thumb_image_px = 100;
	var $imageRoot = "/home/brnstone/public_html/images/";
	//next three must be reset to have a destination after images/
	var $imageRootFolder;
	var $imageMediumFolder;
	var $imageThumbFolder;
	var $imageMediumSharpen = FALSE;
	var $imageThumbSharpen = "80 0.5 3";
	var $resizeOrKeep = "resize";
	var $imageLargeCropSquare = FALSE;
	var $imageLargePreserveAspectRatio = TRUE;
	var $fillRGB = "255 255 255";
	var $renameOnImageTypeChanged = TRUE;
	
	// Image data
	var $imageName;
	var $imageWidth;
	var $imageHeight;
	var $imageSize;
	
	// Miscellaneous
	var $debug = FALSE;
	var $cmdline = FALSE;
	var $continue = FALSE;
	var $imageResized = FALSE;
	var $imageTypeChanged = FALSE;
	var $errorMessage = array();
	var $errorMessageCode = array(); // F# = Fatal, S# = Severe, W# = Warning
	/*
		Code   Message
		F001 = Incompatible Options: 'imageLargePreserveAspectRatio' and 'imageLargeCropSquare'
		S002 = Image Resize Failed
		F003 = File Upload Failed
		S004 = Creation of Medium Size Image Failed
		S005 = Creation of Thumbnail Image Failed
		F006 = Invalid Arguments Passed to ResizeImage() Function
		W007 = Invalid Image Type [#]
		S008 = File Resize Attempted; File Size Still Too Large After Resize
		F009 = Invalid Value for 'resizeOrKeep'
		F010 = Target Folder Creation Failed
	*/
	
	/* BEGIN CLASS FUNCTIONS */
	function Image() {
		$this->continue = TRUE;
	}// end function Image
	
	function Initialize() {
		// Create folders if they do not exist
		if(!is_dir($this->imageRootFolder)) {
			if(!$this->folderMakeRecursive($this->imageRootFolder)) {
				$this->ThrowError("Large Target Folder Creation Failed: $this->imageRootFolder", "F010");
				return FALSE;
			}// end if
		}// end if
		if(!is_dir($this->imageMediumFolder)) {
			if(!$this->folderMakeRecursive($this->imageMediumFolder) && $this->medium_image_px != -1) {
				$this->ThrowError("Medium Target Folder Creation Failed: $this->imageMediumFolder", "F010");
				return FALSE;
			}// end if
		}// end if
		if(!is_dir($this->imageThumbFolder) && $this->thumb_image_px != -1) {
			if(!$this->folderMakeRecursive($this->imageThumbFolder)) {
				$this->ThrowError("Thumbnail Target Folder Creation Failed: $this->imageThumbFolder", "F010");
				return FALSE;
			}// end if
		}// end if
		
		// Extract image information
		$this->imageName = $_FILES[$this->form_image_field]['name'];
		list($this->imageWidth, $this->imageHeight) = @getimagesize($_FILES[$this->form_image_field]['tmp_name']);
		$this->imageSize = $_FILES[$this->form_image_field]['size'];
		
		// Some like to spit out an error if the file name has bad characters; I prefer to just quietly rename the file
		$this->imageName = str_replace(
			array('/','\\','?','&','%','#','~',':','<','>','*','+','@','"',"'",'|',"\r","\n","\t"),
			'',
			$this->imageName);
		$this->imageName = str_replace(' ', '_', $this->imageName);
	}// end function Initialize
	
	function Upload() {
		// Quick error check for incompatible options
		if($this->imageLargePreserveAspectRatio && $this->imageLargeCropSquare) {
			$this->ThrowError("Incompatible Options: 'imageLargePreserveAspectRatio' and 'imageLargeCropSquare'", "F001");
			return FALSE;
		}
		else if(count($this->errorMessage)) {
			return FALSE;
		}// end if
		
		// Is the file too big?
		if($this->imageSize >=  $this->max_file_size ||
		   $this->imageWidth >  $this->max_image_px  ||
		   $this->imageHeight > $this->max_image_px  ||
		  !$this->imageLargePreserveAspectRatio        ) {
			// ... then resize the image
			if(!$this->ResizeImage($_FILES[$this->form_image_field], $this->max_image_size, $this->max_image_px)) {
				// Image resize failed!
				$this->continue = FALSE;
				$this->ThrowError("Image Resize Failed", "S002");
			}// end if
		}// end if
		
		// Everything went well so far
		if($this->continue) {
			// Did the image type change (e.g. from BMP to JPG) and, if so, should we change the file extension?
			if($this->renameOnImageTypeChanged && $this->imageTypeChanged) {
				// ... seems like it; so, rename the file
				list($old_ext, $new_ext) = preg_split("/->/", strtolower($this->imageTypeChanged));
				$this->imageName = preg_replace("/\." . $old_ext . "$/i", "", $this->imageName) . "." . $new_ext;
			}// end if
			
			// ... so, we will now move the uploaded file to its appropriate destination folder
			if(!$this->SaveLargeImage($_FILES[$this->form_image_field]['tmp_name'], $this->imageRootFolder . $this->imageName)) {
				$this->ThrowError("File Upload Failed: ".$this->imageRootFolder . $this->imageName, "F003");
				return FALSE;
			}
			else {
				// Medium size images and thumbnails should always be resized
				$this->resizeOrKeep = "resize";
				
				// Create a medium size image?
				if($this->medium_image_px > 1) {
					if(!$this->ResizeImage($this->imageName, FALSE, $this->medium_image_px, $this->imageMediumFolder, $this->imageMediumSharpen)) {
						$this->ThrowError("Creation of Medium Size Image Failed", "S004");
						return FALSE;
					}// end if
				}// end if
				
				// Create a thumbnail image?
				if($this->thumb_image_px > 1) {
					if(!$this->ResizeImage($this->imageName, FALSE, $this->thumb_image_px, $this->imageThumbFolder, $this->imageThumbSharpen)) {
						$this->ThrowError("Creation of Thumbnail Image Failed", "S005");
						return FALSE;
					}// end if
				}// end if
				
				return TRUE;
			}// end if
		}// end if
	}// end function Upload
	
	function SaveLargeImage($_from, $_to) {
		if($this->cmdline) {
			if(!(copy($_from, $_to) && unlink($_from))) return false;
		}
		else {
			if(!move_uploaded_file($_from, $_to)) return false;
		}// end if
		return true;
	}// end function SaveLargeImage
	
	function ResizeImage($_tempImage, $_max_sz, $_max_px, $_folder = FALSE, $_sharpen = FALSE) {
		// Define source and target image
		if(is_array($_tempImage)) {
			$this->imageResized = FALSE;
//			$source = $tempdir . $_tempImage['tmp_name'];
//			$target = $tempdir . $_tempImage['tmp_name'];
			$source = $_tempImage['tmp_name'];
			$target = $_tempImage['tmp_name'];
		}
		elseif($_folder) {
			$source = $this->imageRootFolder . $_tempImage;
			$target = $_folder . $_tempImage;
		}
		else {
			// $_tempImage is not an array (i.e. we're not dealing with the source image) nor is $_folder set (i.e. we're not dealing with a medium size or thumbnail image)
			$this->ThrowError("Invalid Arguments Passed to ResizeImage() Function", "F006");
			return FALSE;
		}// end if
		
		// Set some default values
//		$tempdir = @getenv('upload_tmp_dir') . "/";
		$imageData = @getimagesize($source); // array(0 => width, 1 => height, 2 => type, 3 => attr) = getimagesize()
		
		// Are we supposed to resize the image?
		if($this->resizeOrKeep == "resize") {
			/*
			Image Types:
				 1 = GIF
				 2 = JPG
				 3 = PNG
				 4 = SWF
				 5 = PSD
				 6 = BMP
				 7 = TIFF(intel byte order)
				 8 = TIFF(motorola byte order)
				 9 = JPC
				10 = JP2
				11 = JPX
				12 = JB2
				13 = SWC
				14 = IFF
			*/
			// Which image type are we dealing with? (See list above.)
			if($imageData[2] == 1) {
				// GIF
				$old_im = imagecreatefromgif($source);
			}
			elseif($imageData[2] == 2) {
				// JPG
				$old_im = imagecreatefromjpeg($source);
			}
			elseif($imageData[2] == 3) {
				// PNG
				$old_im = imagecreatefrompng($source);
			}
			elseif($imageData[2] == 6) {
				// BMP
				include_once("clsImageBMP.php");
				$old_im = imagecreatefrombmp($source);
			}
//			elseif($imageData[2] == 7 || $imageData[2] == 8) {
//				// TIFF
//				include_once("clsImageTIFF.php");
//				$old_im = imagecreatefromtiff($source);
//			}
			else {
				// We do not support uploading of '???' type images
				$this->ThrowError("Invalid Image Type [" . $imageData[2] . "]", "W007");
				return FALSE;
			}// end if
			
			// Set default image dimensions
			$old_im_w = imagesx($old_im);
			$old_im_h = imagesy($old_im);
			$old_im_x = 0;
			$old_im_y = 0;
			$new_im_w = $_max_px;
			$new_im_h = $_max_px;
			$new_im_x = 0;
			$new_im_y = 0;
			
			// Do some figuring, based on source image dimensions
			if($source == $target && $imageData[0] >= $imageData[1] && $imageData[1] > $_max_px && $this->imageLargeCropSquare) {
				// Source image, landscape, short side exceeding px limit, crop square from center
				$old_im_w = $imageData[1];
				$old_im_x = ($imageData[0] - $imageData[1]) / 2;
				$this->imageResized = TRUE;
			}
			elseif($source == $target && $imageData[1] >= $imageData[0] && $imageData[0] > $_max_px && $this->imageLargeCropSquare) {
				// Source image, portrait, short side exceeding px limit, crop square from center
				$old_im_h = $imageData[0];
				$old_im_y = ($imageData[1] - $imageData[0]) / 2;
				$this->imageResized = TRUE;
			}
			elseif($source == $target && $imageData[0] >= $imageData[1] && $this->imageLargeCropSquare) {
				// Source image, landscape, short side smaller than px limit, crop square from center
				$old_im_w = $imageData[1];
				$_max_px = $imageData[1];
				$new_im_w = $_max_px;
				$new_im_h = $_max_px;
				$old_im_x = ($imageData[0] - $imageData[1]) / 2;
				$this->imageResized = TRUE;
			}
			elseif($source == $target && $imageData[1] >= $imageData[0] && $this->imageLargeCropSquare) {
				// Source image, portrait, short side smaller than px limit, crop square from center
				$old_im_h = $imageData[0];
				$_max_px = $imageData[0];
				$new_im_w = $_max_px;
				$new_im_h = $_max_px;
				$old_im_y = ($imageData[1] - $imageData[0]) / 2;
				$this->imageResized = TRUE;
			}
			elseif($imageData[0] >= $imageData[1] && $imageData[0] > $_max_px) {
				// Landscape, exceeding px limit
				$factor = $_max_px / $imageData[0];
				$new_im_h = $imageData[1] * $factor;
				
				if($source == $target) {
					// The image will be resized
					$this->imageResized = TRUE;
				}
				else {
					// We always create square medium size images and thumbnails
					$this->imageLargePreserveAspectRatio = FALSE;
				}// end if
				if(!$this->imageLargePreserveAspectRatio) {
					$new_im_y = ($_max_px - $new_im_h) / 2;
				}// end if
			}
			elseif($imageData[1] >= $imageData[0] && $imageData[1] > $_max_px) {
				// Portrait, exceeding px limit
				$factor = $_max_px / $imageData[1];
				$new_im_w = $imageData[0] * $factor;
				
				if($source == $target) {
					// The image will be resized
					$this->imageResized = TRUE;
				}
				else {
					// We always create square medium size images and thumbnails
					$this->imageLargePreserveAspectRatio = FALSE;
				}// end if
				if(!$this->imageLargePreserveAspectRatio) {
					$new_im_x = ($_max_px - $new_im_w) / 2;
				}// end if
			}
			elseif($imageData[0] >= $imageData[1] && !$this->imageLargePreserveAspectRatio) {
				// Landscape, smaller than px limit, ignore aspect ratio
				$_max_px = $imageData[0];
				$new_im_w = $_max_px;
				$new_im_h = $imageData[1];
				$new_im_y = ($_max_px - $imageData[1]) / 2;
			}
			elseif($imageData[1] >= $imageData[0] && !$this->imageLargePreserveAspectRatio) {
				// Portrait, smaller than px limit, ignore aspect ratio
				$_max_px = $imageData[1];
				$new_im_h = $_max_px;
				$new_im_w = $imageData[0];
				$new_im_x = ($_max_px - $imageData[0]) / 2;
			}
			else {
				// Either orientation, smaller than px limit, preserve aspect ratio
				$new_im_w = $imageData[0];
				$new_im_h = $imageData[1];
			}// end if
			
			// We will use gd 2.0 functions (if available)
			$gd2 = TRUE;
			if($this->imageLargePreserveAspectRatio) {
				$new_im = @imagecreatetruecolor($new_im_w, $new_im_h);
			}
			else {
				$new_im = @imagecreatetruecolor($_max_px, $_max_px);
			}// end if
			
			// Apparently gd 2.0 is not available
			if(!$new_im) {
				$gd2 = FALSE;
				if($this->imageLargePreserveAspectRatio) {
					$new_im = imagecreate($new_im_w, $new_im_h);
				}
				else {
					$new_im = imagecreate($_max_px, $_max_px);
				}// end if
			}// end if
			
			// Define fill color, and fill background
			list($fillR__, $fill_G_, $fill__B) = preg_split("/\D+/", $this->fillRGB, -1, PREG_SPLIT_NO_EMPTY);
			imagefill($new_im, 0, 0, imagecolorallocate($new_im, $fillR__, $fill_G_, $fill__B));
			
			// Copy and resize image, based on uploaded image source
			if($gd2) {
				imagecopyresampled($new_im, $old_im, $new_im_x, $new_im_y, $old_im_x, $old_im_y, $new_im_w, $new_im_h, $old_im_w, $old_im_h);
			}
			else {
				imagecopyresized($new_im, $old_im, $new_im_x, $new_im_y, $old_im_x, $old_im_y, $new_im_w, $new_im_h, $old_im_w, $old_im_h);
			}// end if
			
			// Are we supposed to sharpen the image first?
			if($gd2 && $_sharpen) {
				include_once("clsImageSharpen.php");
				list($amount, $radius, $threshold) = preg_split("/[^0-9\.]+/", $_sharpen, -1, PREG_SPLIT_NO_EMPTY);
				$new_im = UnsharpMask($new_im, $amount, $radius, $threshold);
			}// end if
			
			// Output image (based on image type)
			if($imageData[2] == 1) {
				// GIF
				imagepng($new_im, $target);
				/* use imagegif when GD is updated */
			}
			elseif($imageData[2] == 2) {
				// JPG
				imagejpeg($new_im, $target);
			}
			elseif($imageData[2] == 3) {
				// PNG
				imagepng($new_im, $target);
			}
			elseif($imageData[2] == 6) {
				// BMP, but we output it as JPG
				imagejpeg($new_im, $target);
				$this->imageTypeChanged = "BMP->JPG";
			}
			elseif($imageData[2] == 7 || $imageData[2] == 8) {
				// TIFF, but we output it as JPG
				imagejpeg($new_im, $target);
				$this->imageTypeChanged = "TIF[F]->JPG";
			}// end if
			
			// Does it fall within the maximum allowed file size?
			if($_max_sz && filesize($target) > $_max_sz) {
				$this->imageResized = FALSE;
				$this->ThrowError("File Resize Attempted; File Size Still Too Large After Resize", "S008");
				return FALSE;
			}// end if
			return TRUE;
		}
		elseif($this->resizeOrKeep == "keep") {
			// Do not resize the original
			return TRUE;
		}
		else {
			// resizeOrKeep is not properly set
			$this->ThrowError("Invalid Value for 'resizeOrKeep'", "F009");
			return FALSE;
		}// end if
	}// end function ResizeImage
	
	function folderMakeRecursive($_path) {
		$mode = "0777";
		$stack = array(basename($_path));
		$dir = NULL;
		while($d = dirname($_path)) {
			if(!is_dir($d)) {
				$stack[] = basename($d);
				$_path = $d;
			}
			else {
				$dir = $d;
				break;
			}// end if
		}// end while
		if(($dir = realpath($dir)) === FALSE) return FALSE;
		$created = array();
		for($n = count($stack) - 1; $n >= 0; $n--) {
			$s = $dir . '/' . $stack[$n];
			if(!mkdir($s, $mode)) {
				for($m = count($created) - 1; $m >= 0; $m--) rmdir($created[$m]);
				return FALSE;
			}// end if
			$created[] = $s;
			$dir = $s;
		}// end for
		return TRUE;
	}// end function folderMakeRecursive
	
	function ThrowError($_errmsg, $_errcode = FALSE) {
		$this->errorMessage[] = $_errmsg;
		if($_errcode) $this->errorMessageCode[] = $_errcode;
	}// end function ThrowError
	
	function _debug() {
		
	}// end function _debug
	
}// end class Image
?>
