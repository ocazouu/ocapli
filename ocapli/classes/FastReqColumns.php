<?php

class FastReqColumns
{
	public
	$structures = array(),
	$auto_increment_column = false,
	$model;

	function __construct($model)
	{
		$this->model = $model;

		$req = dbtool::get_requete("SHOW FULL COLUMNS FROM $model");
		while($obj = mysqli_fetch_object($req))
		{
			$type = strtolower(substr($obj->Type,0,4));

			switch($type)
			{
				case 'medi': case 'tiny' : case 'smal' : case 'int(' : case 'bigi':
					$is_int	= true;
				break;
				default:
					$is_int	= false;
				break;
			}

			$this->structures[$obj->Field] = Array(
				'name'    => $obj->Field,
				'type'    => $obj->Type,
				'is_int'  => $is_int,
				'default' => $obj->Default,
				'auto_increment' => $obj->Extra === "auto_increment"
			);

			if($obj->Extra === "auto_increment")
			{
				$this->auto_increment_column = $obj->Field;
			}
		}

		return $this;
	}

	public function get_default_config()
	{
		$default_config = Array();

		foreach ($this->structures as $structure)
		{
			$default_config[$structure["name"]] = $structure["default"];
		}

		return $default_config;
	}
}

?>