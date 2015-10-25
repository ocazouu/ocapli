<?php

class FastReqInstance
{
	function __construct($config)
	{
		$models_path      = $config["models_path"];
		$databasename     = $config["databasename"];

		$models           = array();
		$tables           = array();
		$models_columns   = array();
		$models_relations = array();

		$request = DbTool::get_request("SHOW TABLES");

		while ($obj = mysqli_fetch_object($request))
		{
			$table = $obj->{"Tables_in_" . $databasename};
			$tables[underscored_to_camelcase($table)] = $table;
		}

		if (is_dir($models_path))
		{
			$files = scandir($models_path);
			foreach ($files as $key => $value)
			{
				if(substr($value, -4,4) == ".php")
				{
					$model = substr($value, 0,-4);

					if(array_key_exists($model, $tables))
					{
						$configs[$tables[$model]]["model"]   = $tables[$model];
						$configs[$tables[$model]]["columns"] = new FastReqColumns($tables[$model]);
						require($models_path.$value);
					}
				}
			}
		}

		FastReq::$configs = $configs;
	}
}

?>