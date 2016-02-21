<?php


date_default_timezone_set("Europe/Paris");

define("INDEX_GLOBAL","http://oca2h/");
define("INDEX", INDEX_GLOBAL . DIR_LANG);
define("INDEX_API", INDEX . "api");

define("SITE_ENV","dev");
define("SITE_NAME","oca2h");

define("VERSION_CSS", 0);
define("VERSION_JS", 0);
define("VERSION_SPRITES", 0);

$host         = "localhost";
$user         = "root";
$passwd       = "toto";
$databasename = "oca2h";

$fast_req_config    = array(

	"models_path"   => "models/",
	"databasename"  => $databasename

);


?>