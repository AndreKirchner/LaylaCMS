<?php

class LCMS_ImageHandler {

	/**
	 * Function allows you to crop an image depending
	 * on some given parameters
	 * @params: target dimensions, cropAlgorithm
	 */
	public function cropImage($file, $thumb_width, $thumb_height, $cropAlgo = "full | auto") {

		// Get Filename
		$arrFileName = explode("/", $file);
		$fileName 	 = strtolower($arrFileName[count($arrFileName) - 1]);

		// Get Filetype
		$arrFileName = explode(".", $fileName);
		$fileType 	 = strtolower($arrFileName[count($arrFileName) - 1]);

		if($fileType == "jpg" || $fileType == "jpeg" || $fileType == "png") {

			//Create image instances
			$crpFileName     = time() . "_" . $fileName;
			$srcImage        = $GLOBALS["LCMS"]["internalFolder"] . "/" . $GLOBALS["LCMS"]["originPath"] . "/" . $fileName;
			$prcImage        = $GLOBALS["LCMS"]["internalFolder"] . "/" . $GLOBALS["LCMS"]["cachePath"] . "/" . $crpFileName;

			// Load $srcImage
			if($fileType == "jpg" || $fileType == "jpeg") {
				$src_image = imagecreatefromjpeg($srcImage);
			}
			elseif($fileType == "png") {
				$src_image = imagecreatefrompng($srcImage);
			}

			// Width / Height
			$width           = imagesx($src_image);
			$height          = imagesy($src_image);
			$original_aspect = $width / $height;
			$thumb_aspect    = $thumb_width / $thumb_height;

			// Calculate aspect ratio
			if ( $original_aspect >= $thumb_aspect )
			{
				// If image is wider than thumbnail (in aspect ratio sense)
				$new_height = $thumb_height;
				$new_width  = $width / ($height / $thumb_height);
			}
			else
			{
				// If the thumbnail is wider than the image
				$new_width  = $thumb_width;
				$new_height = $height / ($width / $thumb_width);
			}

			/* -------- CROP ALGORITHMS -------- */

			// Center | Center
			if($cropAlgo == "center | center") {
				$start_x = 0 - ($new_width - $thumb_width) / 2;
				$start_y = 0 - ($new_height - $thumb_height) / 2;
			}

			// Left | Top
			elseif($cropAlgo == "left | top") {
				$start_x = 0;
				$start_y = 0;
			}

			// Center | Top
			elseif($cropAlgo == "center | top") {
				$start_x = 0 - ($new_width - $thumb_width) / 2;
				$start_y = 0;
			}

			// Right | Top
			elseif($cropAlgo == "right | top") {
				$start_x = 0 - ($new_width - $thumb_width);
				$start_y = 0;
			}

			// Left | Center
			elseif($cropAlgo == "left | center") {
				$start_x = 0;
				$start_y = 0 - ($new_height - $thumb_height) / 2;
			}

			// Center | Center
			elseif($cropAlgo == "center | center") {
				$start_x = 0 - ($new_width - $thumb_width) / 2;
				$start_y = 0 - ($new_height - $thumb_height) / 2;
			}

			// Right | Center
			elseif($cropAlgo == "right | center") {
				$start_x = 0 - ($new_width - $thumb_width);
				$start_y = 0 - ($new_height - $thumb_height) / 2;
			}

			// Left | Bottom
			elseif($cropAlgo == "left | bottom") {
				$start_x = 0;
				$start_y = 0 - ($new_height - $thumb_height);
			}

			// Center | Bottom
			elseif($cropAlgo == "center | bottom") {
				$start_x = 0 - ($new_width - $thumb_width) / 2;
				$start_y = 0 - ($new_height - $thumb_height);
			}

			// Right | Bottom
			elseif($cropAlgo == "right | bottom") {
				$start_x = 0 - ($new_width - $thumb_width);
				$start_y = 0 - ($new_height - $thumb_height);
			}

			// full | auto
			elseif($cropAlgo == "full | auto") {
				$start_x 	= 0;
				$start_y 	= 0;
				$new_width 	= $thumb_width;
			    $new_height = $height * $new_width / $width;
			    $thumb_width  = $new_width;
				$thumb_height = $new_height;
			}

			// full | auto
			elseif($cropAlgo == "auto | full") {
				$start_x      = 0;
				$start_y      = 0;
				$new_height   = $thumb_height;
				$new_width    = $width * $new_height / $height;
				$thumb_width  = $new_width;
				$thumb_height = $new_height;
			}

			// Create thumbnail
			$thumb = imagecreatetruecolor($thumb_width, $thumb_height);

			// Make image transparent
			if($fileType == "png") {
				imagealphablending($thumb, true);
				imagesavealpha($thumb, true);
				imagefill($thumb,0 ,0, 0x7fff0000);
			}

			// Resize and crop
			imagecopyresampled(
				$thumb,
			    $src_image,
			    $start_x,
			    $start_y,
			    0,
			    0,
			    $new_width,
			    $new_height,
			    $width,
			    $height
			);

			// Create final image
			if($fileType == "jpg" || $fileType == "jpeg") {
				imagejpeg($thumb, $prcImage, 80);
			}
			elseif($fileType == "png") {
				imagepng($thumb, $prcImage, 0);
			}

			$cropped = $crpFileName;
		}
		else {
			$cropped = $file;
		}

		return $cropped;
	}
}