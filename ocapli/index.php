<?php

require('config.php');

function now()
{
	$objdate = DateTime::createFromFormat('U.u', microtime(true));
	return $objdate->format("H:i:s.u");
}

function __($info,$ok=true)
{
	$now = now();
	$log = ('### __(LOG_INFO): ' . $now . strtr(strip_tags(print_r($info,1)),array("\n"=>'',"\r\n"=>'',"\t"=>'')).' ###');
	echo "<div class='clear'></div><div class='log " . ($ok ? "ok" : "ko") . "''><b class=puce>☼</b><b class=time>$now</b> <p>" .strtr(print_r($info,1),array("\n"=>'',"\r\n"=>'',"\t"=>'')). "</p></div>";
	//error_log($log);
}

require('classes/DbTool.php');		// mysql
require('classes/FastReq.php');
require('classes/FastReqColumns.php');
require('classes/FastReqInstanceRecords.php');
require('classes/FastReqBuildQueryString.php');
require('classes/UnitTest.php');

require('../tests/UnitTestFastReq.php');

require('../models/UnitTest.php');		// mysql
require('../models/ConfLang.php');		// mysql

DbTool::set_instance();



?><!DOCTYPE html>
<html>
<head>
	<title>Unit select tests</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<style type="text/css">
		html, body, p{line-height:20px;color:#666;font:15px/1.6 Helvetica, arial, nimbussansl, liberationsans, freesans, clean, sans-serif, "Segoe UI Emoji", "Segoe UI Symbol";}
		body{margin:0 40px;background:#F2F2F2;}
		.clear{clear:both}
		.ellapsed{margin:15px 0 0 0;}
		.log{padding:0 20px;margin:15px 0 0 0;box-shadow:0 0 10px #CCC inset;background:#F3ECED;padding:15px 20px 5px 20px;color:#777;}
		.log b.puce{font-size:25px;display:block;line-height:25px;margin-bottom:3px;float:left}
		.log b.time{font-size:12px;display:block;float:right}
		.log p{clear:both;}
		.log.ok{background:#E8F3E5;<?=(isset($_GET["greenlogs"]) ? '' : 'display:none;')?>}
		.log.ok b{color:green;}
		.log.ko b{color:red;}
		.test_result{font-weight:bold;font-size:24px;margin: 40px 20px;text-align:center;color:red;}
		.test_result.done{color:green;}
		.test_result.fail{color:red;}
		.test_run h2{float:left;margin:0;}
		.test_run b.done{color:green;float:right;}
		.test_run b.fail{color:red;float:right}

		.li{padding:10px 0 10px 20px;margin:0;background:#FFF;border-bottom:1px dashed #C9C9C9;}
		.test_infos{<?=(isset($_GET["test_infos"]) ? '' : 'display:none;')?>}
		.records{max-height:300px;overflow-y:auto;<?=(isset($_GET["records"]) ? '' : 'display:none;')?>}
		hr{margin:40px; border:none; border-bottom:1px solid #CCC;}
		b{color:#666;}
		.red{color:#AA8888;}
		h1,h2{color:#444;}
	</style>
</head>
<body>
<h1>Unit select tests.</h1>

<?php

$unit_test = new UnitTestFastReq();
$unit_test->run_tests();


?>
</body>
</html>