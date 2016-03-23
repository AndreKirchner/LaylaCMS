<?php
$GLOBALS["LCMS"]["internalFolder"] 	= "files";
$GLOBALS["LCMS"]["externalFolder"] 	= "phpservice/files";
$GLOBALS["LCMS"]["originPath"]      = "origin";
$GLOBALS["LCMS"]["cachePath"]       = "_cache";
$GLOBALS["LCMS"]["feContent"]   	= "json/content.json";
$GLOBALS["LCMS"]["beConfig"]    	= "json/config.json";
$GLOBALS["LCMS"]["authfile"]    	= "system/LCMS_AuthConfig.php";
$GLOBALS["LCMS"]["requests"]    	= array(
	"login"  	        => "allowed",
	"checkAuth"  	    => "restricted",
	"loadFEContent"  	=> "allowed",
	"writeFEContent" 	=> "restricted",
	"loadBEConfig"		=> "restricted",
	"writeBEConfig" 	=> "restricted",
	"uploadFile" 		=> "restricted",
	"showDir" 			=> "restricted"
);

?>