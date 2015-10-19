<?php

class FastRec 
{
	private static 
		$model = Array();
	private static 
		$columns = Array();

	/* Return model name in a string */
	private static function get_model($get_called_class)
	{
		if(!array_key_exists($get_called_class, self::$model))
		{
			self::$model[$get_called_class] = Array(
				"UnitTest" => "unit_test",
				"ConfLang" => "conf_lang_b"
			)[$get_called_class];
		}
		return self::$model[$get_called_class];
	}
	/* Return array with custom model structures */
	private static function get_columns()
	{
		$get_called_class = get_called_class();

		if(array_key_exists($get_called_class, self::$columns))
		{
			return self::$columns[$get_called_class];
		}
		else
		{
			self::$columns[$get_called_class] = new FastReqColumns(self::get_model($get_called_class));
			return self::$columns[$get_called_class];
		}
	}
	/* Return model and structure in one array to instance FastReqInstanceRecords */
	private static function get_model_and_columuns()
	{
		$columns = self::get_columns();
		return Array("model"=>$columns->model, "columns"=>$columns);
	}
	/* Return a new instance of FastReqInstanceRecords */
	private static function new_instance_records()
	{
		return new FasReqInstanceRecords(self::get_model_and_columuns());
	}

	/* Set configQuerySelect with a new FasReqInstanceRecords() and return this */
	public static function select($config)
	{
		return self::new_instance_records()->select($config);
	}
	/* Set configQueryWhere with a new FasReqInstanceRecords() and return this */
	public static function where($config)
	{
		return self::new_instance_records()->where($config);
	}
	/* Set configQueryWhere with a new FasReqInstanceRecords() and return this */
	public static function order($config)
	{
		return self::new_instance_records()->order($config);
	}
	/* 
		(for SQL expression: GROUP BY)
		Set configQueryGroup with a new FasReqInstanceRecords() and return this 
	*/
	public static function group($config)
	{
		return self::new_instance_records()->group($config);
	}
	/* Set configQueryLimit with a new FasReqInstanceRecords() and return this */
	public static function limit($start=0,$total=false)
	{
		return self::new_instance_records()->limit($start,$total);
	}

	/* Return first SQL object with a new FasReqInstanceRecords() */
	public static function first()
	{
		return self::new_instance_records()->first();
	}
	/* Return last SQL object with a new FasReqInstanceRecords() */
	public static function last()
	{
		return self::new_instance_records()->last();
	}
	/* Return array with all SQL object with a new FasReqInstanceRecords() */
	public static function all()
	{
		return self::new_instance_records()->all();
	}
	/* Return integer with total of SQL object with a new FasReqInstanceRecords() */
	public static function count()
	{
		return self::new_instance_records()->count();
	}

	/* Create a new SQL record and return boolean with a new FasReqInstanceRecords() */
	public static function create($config)
	{
		return self::new_instance_records()->create($config);
	}
	/* Create a new SQL record and return record created with a new FasReqInstanceRecords() */
	public static function create_and_use($config)
	{
		return self::new_instance_records()->create_and_use($config);
	}
	/* Create a new SQL record and return boolean with a new FasReqInstanceRecords() */
	public static function update($config)
	{
		return self::new_instance_records()->update($config);
	}
	/* Create a new SQL record and return record updated with a new FasReqInstanceRecords() */
	public static function update_and_use($config)
	{
		return self::new_instance_records()->update_and_use($config);
	}
}

?>