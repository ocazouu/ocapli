<?php

date_default_timezone_set('Europe/Paris');

$user 				= 'root';
$host				= 'localhost';
$passwd 			= 'toto';
$databasename 		= 'db_test';

$fast_req_config    = array(

	"models_path"   => "../models/",
	"databasename"  => $databasename

);
?>