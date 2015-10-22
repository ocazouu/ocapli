<?php

class FastReq 
{
	public static 
		$columns = Array();

	/* Return array with custom model structures */
	private static function get_model_and_columuns()
	{
		$model = strtr(get_called_class(), array(
			"UnitTest" => "unit_test",
			"ConfLang" => "conf_lang_b"
		));

		if(!array_key_exists($model, self::$columns))
		{
			self::$columns[$model] = new FastReqColumns($model);
		}

		return Array("model"=>$model, "columns"=>self::$columns[$model]);
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