<?php
/* 
Instance unique MYSQL
	
	- Cursor Static _instance :
		- set_instance() return _instance et demmare
		- get_instance() return _instance sans démarrer
	
	- Comporte les méthodes basique de traitement mysql 
	
Sera toujours déconnecté en fin de script via objet website
*/
class DbTool {
 
	private $mysql_id;
	private $mysql_result;

	private $is_connect;

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
		if(is_array($host)==TRUE)
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
		$self = DbTool::get_instance();
		return $self->is_connect;
	}
	public static function set_instance()
	{
		if(is_null(self::$_instance))
		{
			self::$_instance = new DbTool();  
		}
		return self::$_instance;
	}
	public static function get_instance()
	{
		return self::$_instance;
	}
	public static function get_request($requete)
	{
		$self = DbTool::get_instance();
		if(!$self->is_connect)return false;

		$self->mysql_result = mysqli_query($self->mysql_id,$requete);

		if($self->mysql_result == NULL)
		{
			__("<b class=red>Error with mysqli_query().</b><br /> " . $requete,0);
			return false;
		}
		else
		{
			__($requete);
		}
		return $self->mysql_result;
	}
	public static function protect($var)
	{
		$self = DbTool::get_instance();
		if(!$self->is_connect)return false;

		return mysqli_real_escape_string($self->mysql_id, trim($var));
	}
	public static function sql_deconnect()
	{
		$self = DbTool::get_instance();
		if(!$self->is_connect)return false;
/*		
	print_r($this->mysql_result);
	if($this->mysql_result)mysql_free_result($this->mysql_result);
*/
		if($self->mysql_id)mysqli_close($self->mysql_id);
		$self->is_connect = false;
	}
}
?>