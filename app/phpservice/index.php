<?php

define("__ROOT__", dirname(dirname(__FILE__)));
require_once(__ROOT__."/phpservice/system/LCMS_LocalConfig.php");
require_once(__ROOT__."/phpservice/system/LCMS_CorsHandler.php");
require_once(__ROOT__."/phpservice/system/LCMS_AuthHandler.php");
require_once(__ROOT__."/phpservice/system/LCMS_FileHandler.php");
require_once(__ROOT__."/phpservice/system/LCMS_ImageHandler.php");
require_once(__ROOT__."/phpservice/system/LCMS_RequestRouter.php");

ini_set("enable_post_data_reading", "true");

// Allow CORS/Access
$corsHandler = new LCMS_CorsHandler();
$corsHandler->allowAccess();

// Get request type
$type = $_SERVER["REQUEST_METHOD"];
//echo $type;

// Load Data
if($type == "GET") {
	$incomingData = $_GET;
}
else {
	// Check if upload
    if($_FILES) {
        $incomingData = $_FILES;
        $incomingData["request"] = "uploadFile";
    }
    else {
        parse_str(file_get_contents("php://input"), $incomingData);
    }
}

// Processing
if(isset($incomingData["request"]) && isset($GLOBALS["LCMS"]["requests"][$incomingData["request"]])) {
    if($GLOBALS["LCMS"]["requests"][$incomingData["request"]] == 'allowed') {
        $apiService = new LCMS_RequestRouter();
        $apiService->run($incomingData);
    }
    elseif($GLOBALS["LCMS"]["requests"][$incomingData["request"]] == 'restricted') {
        // Authentication
        foreach (getallheaders() as $key => $value) {
            if($key == "Auth-Token") {
                $authBearer = $value;
            }
        }
        $authService = new LCMS_AuthHandler();

        if(isset($authBearer) && $authBearer && $authService->isAuthenticated($authBearer)) {
            $apiService = new LCMS_RequestRouter();
            $apiService->run($incomingData);
        }
        else {
            header("HTTP/1.0 401 Unauthorized");
        }
    }
    else {
        header("HTTP/1.0 404 Not found");
    }
}
else {
    header("HTTP/1.0 404 Not found");
}


?>