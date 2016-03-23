<?php

class LCMS_AuthHandler {

	private function saltString($string) {

		$salt   = "LaylaCMSAngularCMS";
		$result = sha1($string.$salt);

		return $result;
	}

	private function guid(){
	    if (function_exists('com_create_guid')){
	        return com_create_guid();
	    }
	    else{
	        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
	        $charid = strtoupper(md5(uniqid(rand(), true)));
	        $hyphen = chr(45);// "-"
	        $uuid =  substr($charid, 0, 8).$hyphen
	                .substr($charid, 8, 4).$hyphen
	                .substr($charid,12, 4).$hyphen
	                .substr($charid,16, 4).$hyphen
	                .substr($charid,20,12);
	        return $uuid;
	    }
	}


	private function loadConfig() {

		// Check if auth config is available
		if(!is_file($GLOBALS["LCMS"]["authfile"])) {
			// echo "\nAuth File not readable.";
			return false;
		}

		require_once($GLOBALS["LCMS"]["authfile"]);

		return true;
	}

	private function writeBearerConfig($username, $password, $authBearer = null) {

		if(!$this->loadConfig()) {
			return false;
		}

		if(!$authBearer) {
			$authBearer = $this->guid();
		}

		$output  = "<?php";
		$output .= "\n\$GLOBALS[\"LCMS\"][\"administrator\"][\"username\"]   = \"" . $username . "\";";
		$output .= "\n\$GLOBALS[\"LCMS\"][\"administrator\"][\"password\"]   = \"" . $password . "\";";
		$output .= "\n\$GLOBALS[\"LCMS\"][\"administrator\"][\"lastlogin\"]  = \"" . time() . "\";";
		$output .= "\n\$GLOBALS[\"LCMS\"][\"administrator\"][\"bearer\"]     = \"" . $authBearer . "\";";
		$output .= "\n\$GLOBALS[\"LCMS\"][\"administrator\"][\"bearer_exp\"] = \"" . (time() + 300) . "\";";
		$output .= "\n?>";

		$fileHandler = new LCMS_FileHandler();
		$fileHandler->save($GLOBALS["LCMS"]["authfile"], $output);

		return $authBearer;
	}

	public function authenticate($username, $password) {

		if(!$this->loadConfig()) {
			return false;
		}

		// Check if UserInfo is set properly
		if(!isset($GLOBALS["LCMS"]["administrator"]["username"]) || !isset($GLOBALS["LCMS"]["administrator"]["password"])) {
			// echo "\nNo userdata in config file.";
			return false;
		}

		// Try to login
		$saltedPassword = $this->saltString($password);

		if($GLOBALS["LCMS"]["administrator"]["username"] == $username &&
		   $GLOBALS["LCMS"]["administrator"]["password"] == $saltedPassword) {

			// Write AuthConfig and get Bearer in return
			return $this->writeBearerConfig($username, $saltedPassword);
		}
		else {
			// echo "\nLogin Data wrong.";
			return false;
		}
	}

	public function isAuthenticated($bearer) {

		if(!$this->loadConfig()) {
			return false;
		}

		// Check if Bearer is set properly
		if(!isset($GLOBALS["LCMS"]["administrator"]["bearer"]) ||
		   !isset($GLOBALS["LCMS"]["administrator"]["bearer_exp"])) {
			// echo "\nNo bearer set on server.";
			return false;
		}

		if($GLOBALS["LCMS"]["administrator"]["bearer_exp"] < time()) {
			// echo "\nSession expired.";
			return false;
		}

		// Check if request Bearer == local Bearer
		if(isset($GLOBALS["LCMS"]["administrator"]["bearer"]) && $GLOBALS["LCMS"]["administrator"]["bearer"] == $bearer) {

			// Renew Bearer
			$this->writeBearerConfig($GLOBALS["LCMS"]["administrator"]["username"], $GLOBALS["LCMS"]["administrator"]["password"], $GLOBALS["LCMS"]["administrator"]["bearer"]);

			return true;
		}
		else {
			// echo "\nBearer not accepted. Redirect to login.";
			return false;
		}
	}
}