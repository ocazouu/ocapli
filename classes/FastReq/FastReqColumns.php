<?php

class FastReqColumns
{
	public
	$default_config        = array(),
	$structures            = array(),
	$unique                = array(),
	$auto_increment        = false,
	$model;

	function __construct($model)
	{
		$this->model = $model;

		$req = MySQL::get_request("SHOW FULL COLUMNS FROM $model");

		if(!$req)
		{
			return false;
		}
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

			$unique = $obj->Key === "PRI" || $obj->Key === "PRI";

			$this->structures[$obj->Field] = Array(
				'name'    => $obj->Field,
				'type'    => $obj->Type,
				'is_int'  => $is_int,
				'default' => $obj->Default,
				'unique'  => $unique,
				'auto_increment' => $obj->Extra === "auto_increment"
			);

			$this->default_config = $this->get_default_config();

			if($obj->Extra === "auto_increment")
			{
				$this->auto_increment = $obj->Field;
			}
			if($unique)
			{
				$this->unique[] = $obj->Field;
			}
		}

		MySQL::free_result();

		return $this;
	}

	public function get_configs()
	{
		return Array(
			"structures"     => $this->structures,
			"unique"         => $this->unique,
			"auto_increment" => $this->auto_increment,
			"default_config" => $this->default_config
		);
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