<?php

class FastReqInstance
{
	function __construct($config)
	{
		$models_path  = $config["models_path"];
		$databasename = $config["databasename"];
		$tables       = array();
//		$configs      = Cache::get("FastReqConfigs");
		$configs      = false; // reset configs, todo migration

/*		
		echo '<pre>' . print_r($configs, 1) .'</pre>';
		die;
*/

		if(!$configs)
		{
			$request = MySQL::get_request("SHOW TABLES");

			while ($obj = mysqli_fetch_object($request))
			{
				$table = $obj->{"Tables_in_" . $databasename};
				$tables[underscored_to_camelcase($table)] = $table;
			}
			
			MySQL::free_result();

			$configs = array();
			$configs["class_models"] = array();

			if (is_dir($models_path))
			{
				$files = scandir($models_path);
				foreach ($files as $key => $value)
				{
					if(substr($value, -4,4) == ".php")
					{
						$model = substr($value, 0,-4);

						if(isset($tables[$model]))
						{
							$FastReqColumns = new FastReqColumns($tables[$model]);

							if($FastReqColumns)
							{
								require($models_path.$value);

								$configs[$tables[$model]]               = array();
								$configs[$tables[$model]]               = array();
								$configs[$tables[$model]]["model"]      = $model;
								$configs[$tables[$model]]["columns"]    = $FastReqColumns->get_configs();


								$relations = array();

								if(isset($model::$has_many))
								{
									$relations["has_many"] = $model::$has_many;
								}
								if(isset($model::$belongs_to))
								{
									$relations["belongs_to"] = $model::$belongs_to;
								}

								$configs[$tables[$model]]["relations"] = $relations;


								$class_methods = array();

								if(method_exists($model, "before_save"))
								{
									$class_methods[] = "before_save";
								}

								$configs[$tables[$model]]["class_methods"] = $class_methods;


								$configs[$model] = $tables[$model];

								$configs["class_models"][] = $model;
							}
						}
						else
						{
							echo "Error between one model files and sql model, undefined model in database: " .$model;
							die;
						}
					}
				}

			//	Cache::set("FastReqConfigs",$configs);
			}
		}
		else
		{
			foreach ($configs["class_models"] as $model)
			{
				require($models_path.$model . ".php");
			}
		}

		FastReq::$configs = $configs;
	}
}
?>