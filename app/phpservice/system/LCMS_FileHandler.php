<?php

class LCMS_FileHandler {

	/**
	 *	Function will save the incoming data
	 *  to a given file.
	 */
	public function save($path, $data) {

		$file = fopen($path, "w") or die("Unable to open file!");
		fwrite($file, $data);
		fclose($file);
	}

	/**
	 *	Function will read and return
	 *  the contents of a file.
	 */
	public function load($path) {

		if(is_file($path)) {
			$response = file_get_contents($path);
		}
		else {
			$response = "ERROR::File not found";
		}

		return $response;
	}

	/**
	 *	Function will scan directory
	 *  and return an files array || false
	 */
	public function scanDir($folder) {
		$arrFiles = array();

		if ($handle = opendir($folder)) {

		    $arrFiles = array();
		    while (false !== ($file = readdir($handle))) {
		        if($file != "." && $file != "..") {
		        	$arrFiles[] = $file;
		        }
		    }

		    closedir($handle);
		}

		return $arrFiles;
	}
}