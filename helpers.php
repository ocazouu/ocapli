<?php
function dir_to_array($dir) { 

	$result = array(); 

	$cdir = scandir($dir); 
	foreach ($cdir as $key => $value) 
	{
		if (!in_array($value,array(".",".."))) 
		{
			if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) 
			{
				array_push($result, dir_to_array($dir . DIRECTORY_SEPARATOR . $value));
			} 
			else 
			{ 
				$result[] = $dir. DIRECTORY_SEPARATOR .$value; 
			} 
		} 
	}
	return array_flatten($result); 
}

function array_flatten($array) {

   $return = array();
   foreach ($array as $key => $value) {
       if (is_array($value)){ $return = array_merge($return, array_flatten($value));}
       else {$return[$key] = $value;}
   }
   return $return;

}

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