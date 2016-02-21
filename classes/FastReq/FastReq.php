<?php

class FastReq 
{
	public static 
		$configs        = Array(),
		$queryBuilder   = Array();

	/* Return a new instance of FastReqInstanceRecords */
	private static function new_instance_records()
	{
		
		$ClassModel = get_called_class();
		$model = self::$configs[$ClassModel];

		if(!isset(self::$queryBuilder[$model]))
		{
			self::$queryBuilder[$model] = new FastReqQueryBuilder(
				self::$configs[$model]["columns"]["structures"],
				self::$configs[$model]["columns"]["default_config"],
				$model,
				self::$configs[$model]["relations"]
			);
		}

		return new FastReqScope($model, self::$configs[$model]);
	}

	/* Set configQuerySelect with a new FastReqScope() and return this */
	public static function select($config)
	{
		return self::new_instance_records()->select($config);
	}
	/* Set configQueryWhere with a new FastReqScope() and return this */
	public static function where($config)
	{
		return self::new_instance_records()->where($config);
	}
	/* Set configQueryWhere with a new FastReqScope() and return this */
	public static function order($config)
	{
		return self::new_instance_records()->order($config);
	}
	/* 
		(for SQL expression: GROUP BY)
		Set configQueryGroup with a new FastReqScope() and return this 
	*/
	public static function group($config)
	{
		return self::new_instance_records()->group($config);
	}
	/* Set configQueryLimit with a new FastReqScope() and return this */
	public static function limit($start=0,$total=false)
	{
		return self::new_instance_records()->limit($start,$total);
	}

	/* Return first SQL object with a new FastReqScope() */
	public static function first()
	{
		return self::new_instance_records()->first();
	}
	/* Return last SQL object with a new FastReqScope() */
	public static function last()
	{
		return self::new_instance_records()->last();
	}
	/* Return array with all SQL object with a new FastReqScope() */
	public static function all()
	{
		return self::new_instance_records()->all();
	}
	/* Return integer with total of SQL object with a new FastReqScope() */
	public static function count()
	{
		return self::new_instance_records()->count();
	}

	/* Return executed query */
	public static function get_query()
	{
		return self::new_instance_records()->get_query();
	}
	/* Create a new SQL record and return boolean with a new FastReqScope() */
	public static function create($config)
	{
		return self::new_instance_records()->create($config);
	}
	/* Create a new SQL record and return record created with a new FastReqScope() */
	public static function create_and_use($config)
	{
		return self::new_instance_records()->create_and_use($config);
	}
	/* Create a new SQL record and return boolean with a new FastReqScope() */
	public static function update_all($config)
	{
		return self::new_instance_records()->update_all($config);
	}
	/* Create a new SQL record and return record updated with a new FastReqScope() */
	public static function update_all_and_use($config)
	{
		return self::new_instance_records()->update_all_and_use($config);
	}
	/* Delete SQL records  */
	public static function delete_all($config)
	{
		return self::new_instance_records()->delete_all();
	}
}

?>