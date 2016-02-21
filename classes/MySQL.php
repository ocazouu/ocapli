<?php

class MySQL {

	private
		$mysql_id,
		$is_connect,
		$mysql_result = false;

	private static $_instance = null;

	private function __construct()
	{
		global $user,$host,$passwd,$databasename;

		$this->mysql_id	= $this->sql_connect($host, $user, $passwd, $databasename);
		if($this->mysql_id)
		{
			mysqli_query($this->mysql_id,"SET NAMES 'utf8'");
			mysqli_query($this->mysql_id,"SET lc_time_names = 'fr_FR'");
		}
	}
	public function get_mysql_id()
	{
		return $this->mysql_id;
	}
	private function sql_connect($host, $user, $passwd, $databasename)
	{

		$maxthread = 395;
		if(is_array($host)===true)
		{
			$host = $host[0];
		}
		else $host= $host;

		$mysql_id = mysqli_connect($host, $user, $passwd);
		if($mysql_id)
		{
			$status = explode('  ', mysqli_stat($mysql_id));
			$thread = explode(':',$status[1]);

			if($thread[1] > $maxthread)
			{
				$this->is_connect = false;
				return false;
			}
			elseif(mysqli_select_db($mysql_id,$databasename) == NULL)
			{
				$this->is_connect = false;
				return false;
			}	
			$this->is_connect = true;
		}
		return($mysql_id);
	}
	public static function is_connect()
	{
		return self::$_instance->is_connect;
	}
	public static function set_instance()
	{
		if(is_null(self::$_instance))
		{
			self::$_instance = new MySQL();  
		}
		return self::$_instance;
	}
	public static function get_instance()
	{
		return self::$_instance;
	}
	public static function get_request($requete)
	{
		$greenlogs = isset($_GET["greenlogs"]);

		if($greenlogs)
		{
			$start_time = microtime(true);
		}
		
		self::$_instance->mysql_result = mysqli_query(self::$_instance->mysql_id,$requete);
		
		if($greenlogs)
		{
			$time = (microtime(true) - $start_time);

			if(strpos($time, "-") !== false)
			{
				$time = "0.000100";
			}
		}

		if(self::$_instance->mysql_result == NULL)
		{
			error_log("Error with mysqli_query().\n" . $requete,0);
			return false;
		}
		elseif($greenlogs)
		{
			__($requete . " <br /><b>Time:</b> " . strtr(substr($time, 0, 5),array("." => ",")) . "." . substr($time, 5, 3) . " secondes");
		}
		return self::$_instance->mysql_result;
	}
	public static function free_result()
	{
		mysqli_free_result(self::$_instance->mysql_result);
	}
	public static function protect($var)
	{
		return mysqli_real_escape_string(self::$_instance->mysql_id, trim($var));
	}
	public static function sql_disconnect()
	{
		if(self::$_instance->mysql_id)mysqli_close(self::$_instance->mysql_id);
		self::$_instance->is_connect = false;
	}
}
?>