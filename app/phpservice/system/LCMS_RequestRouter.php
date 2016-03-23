<?php

class LCMS_RequestRouter {

	/**
	 * Function creates frontend urls for given files
	 * @param: str fileName
	 * @return: str url
	 */ 
	private function getFrontendUrl($fileName) {
		$urlProtocol = stripos($_SERVER["SERVER_PROTOCOL"],"https") === true ? "https://" : "http://";
		$urlHost     = $_SERVER["HTTP_HOST"];
		$urlFolder   = $GLOBALS["LCMS"]["externalFolder"];
		$urlPrefix   = $urlProtocol . $urlHost . "/" . $urlFolder. "/";
		$fullPath    = $urlPrefix . $fileName;

		return $fullPath;
	}

	/**
	 * Function will allow the user to login
	 * @return: Auth-Token || error 401
	 */
	private function login($incomingData) {

		if(isset($incomingData["username"]) && isset($incomingData["password"])) {

            $authService = new LCMS_AuthHandler();

            if(!($bearer = $authService->authenticate($incomingData["username"], $incomingData["password"]))) {
                return "ERROR401";
            }
            return $bearer;
        }
        else {
        	return "ERROR401";
        }
	}

	/**
	 * Function will check if the current request
	 * is arleady authenticated
	 */
	private function checkAuth($incomingData) {
		return "Admin authenticated!";
	}

	/**
	 * Function will load the content of a JSON
	 * file given in the local config.
	 * @return: JSON || error 404
	 */
	private function loadFEContent($incomingData) {
		if(isset($GLOBALS["LCMS"]["feContent"]) && is_file($GLOBALS["LCMS"]["feContent"])) {
			$fileSystem = new LCMS_FileHandler();
			return $fileSystem->load($GLOBALS["LCMS"]["feContent"]);
		}
		else {
			return "ERROR404";
		}
	}

	/**
	 * Function will save a JSON stream
	 * to a JSON file given in the local config.
	 * @return: JSON || error 404
	 */
	private function writeFEContent($incomingData) {
		if(isset($GLOBALS["LCMS"]["feContent"]) && is_file($GLOBALS["LCMS"]["feContent"])) {

			$arrJSON = json_decode($incomingData["content"]);

			// Load Image Engine
			$imageHandler = new LCMS_ImageHandler();

			// Iterate over sections
			for($i = 0; $i < count($arrJSON->content[1]->content); $i++) {
				// Iterate over elements
				for($j = 0; $j < count($arrJSON->content[1]->content[$i]); $j++) {
					if($arrJSON->content[1]->content[$i]->content[$j]->meta->type == "image" && $arrJSON->content[1]->content[$i]->content[$j]->file) {

						$response = '';

						if(isset($arrJSON->content[1]->content[$i]->content[$j]->meta->cropalgo) && $arrJSON->content[1]->content[$i]->content[$j]->meta->cropalgo &&
						   isset($arrJSON->content[1]->content[$i]->content[$j]->meta->width) && $arrJSON->content[1]->content[$i]->content[$j]->meta->width &&
						   isset($arrJSON->content[1]->content[$i]->content[$j]->meta->height) && $arrJSON->content[1]->content[$i]->content[$j]->meta->height) {

							// Crop Image and save to stream
							$response = $imageHandler->cropImage(
								$arrJSON->content[1]->content[$i]->content[$j]->file,
								$arrJSON->content[1]->content[$i]->content[$j]->meta->width,
								$arrJSON->content[1]->content[$i]->content[$j]->meta->height,
								$arrJSON->content[1]->content[$i]->content[$j]->meta->cropalgo
							);
						}

						// Add folder
						if($response) {
							$response = $this->getFrontendUrl($GLOBALS["LCMS"]["cachePath"] . "/" . $response);
						}

						$arrJSON->content[1]->content[$i]->content[$j]->croppedImage = $response;
					}
				}
			}

			$fileSystem = new LCMS_FileHandler();
			$fileSystem->save($GLOBALS["LCMS"]["feContent"], json_encode($arrJSON));
		}
		else {
			return "ERROR404";
		}
	}

	/**
	 * Function will load the content of a JSON
	 * config file given in the local config.
	 * @return: JSON || error 404
	 */
	private function loadBEConfig($incomingData) {
		if(isset($GLOBALS["LCMS"]["feContent"]) && is_file($GLOBALS["LCMS"]["feContent"])) {
			$fileSystem = new LCMS_FileHandler();
			return $fileSystem->load($GLOBALS["LCMS"]["beConfig"]);
		}
		else {
			return "ERROR404";
		}
	}

	private function writeBEConfig($incomingData) {
		// TEAUX DEAUX: Add functionality
	}

	private function uploadFile($incomingData) {

		$fileName = $incomingData["file"]["name"];
  		$folder   = $GLOBALS["LCMS"]["internalFolder"] . "/" . $GLOBALS["LCMS"]["originPath"] . "/" . $fileName;

  		move_uploaded_file($incomingData["file"]["tmp_name"], $folder);

  		// TEAUX DEAUX: Check if file has really been uploaded!

		return "File uploaded successfully";
	}

	private function showDir($incomingData) {

		// Load Files
		$fileSystem = new LCMS_FileHandler();
		$dirContent = $fileSystem->scanDir($GLOBALS["LCMS"]["internalFolder"] . "/" . $GLOBALS["LCMS"]["originPath"]);

		// Result
		$result = array();
		foreach($dirContent as $file) {
			$result[] = array(
				"url"  => $this->getFrontendUrl($GLOBALS["LCMS"]["originPath"] . "/" . $file),
				"name" => $file
			);
		}

		return json_encode($result);
	}

	/**
	 *	Function will route incoming requests.
	 */
	public function run($incomingData) {

		$response = "REQUEST_EMPTY";
		$request  = $incomingData["request"];

		// Route the requests
		switch($request) {
			case "login" 			: $response = $this->login($incomingData); break;
			case "checkAuth"		: $response = $this->checkAuth($incomingData); break;
			case "loadFEContent"	: $response = $this->loadFEContent($incomingData); break;
			case "writeFEContent"	: $response = $this->writeFEContent($incomingData); break;
			case "loadBEConfig"		: $response = $this->loadBEConfig($incomingData); break;
			case "writeBEConfig"	: $response = $this->writeBEConfig($incomingData); break;
			case "uploadFile"		: $response = $this->uploadFile($incomingData); break;
			case "showDir"			: $response = $this->showDir($incomingData); break;
		}

		// Handle responses
		if($response == "ERROR404") {
			header("HTTP/1.0 404 Not found");
		}
		elseif($response == "ERROR401") {
			header("HTTP/1.0 401 Unauthorized");
		}
		else {
			echo $response;
		}
	}
}