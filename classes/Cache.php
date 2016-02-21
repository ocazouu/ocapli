<?php

/*
		telnet localhost 11211
		Trying ::1...
		Trying 127.0.0.1...
		Connected to localhost.
		Escape character is '^]'.
		flush_all
		OK
		quit
*/

class Cache
{
	private static $_instance        = null;
	private static $_mcache          = null;
	private static $_moduletype      = '';
	private static $_is_actif_mcache = false;

	private function __construct()
	{
		if(@class_exists(Memcached)){self::$_mcache = new Memcached();self::$_moduletype = 'Memcached';}
		else if(@class_exists(Memcache)){self::$_mcache = new Memcache();self::$_moduletype = 'Memcache';}
	
		if(self::$_moduletype && self::$_mcache->addServer("127.0.0.1", 11211))
		{
			self::$_is_actif_mcache = true;
		}
	}
	
	public static function set($key,$value,$expiration = 0)
	{
		if(self::$_is_actif_mcache)
		{
			return self::$_mcache->set(SITE_NAME . '_' . $key,$value,$expiration);
		}
	}
	
	public static function get($key)
	{
		if(self::$_is_actif_mcache)
		{
			return self::$_mcache->get(SITE_NAME . '_' . $key);
		}
	}
	
	public static function set_instance()
	{
		if(is_null(self::$_instance))
		{
			self::$_instance = new Cache();  
		}
		return self::$_instance;
	}
	
	public static function get_instance()
	{
		return self::$_instance;
	}
}

?>