<?php

function underscored_to_camelcase($string)
{
	return strtr(
		ucwords(strtr($string,array("_"=>" ")))
		,array(" "=>"")
	);
}

function camelcase_to_underscored($string)
{
	return strtolower(preg_replace('/([A-Z]+)/', '_$1', lcfirst($string)));
}

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

?>