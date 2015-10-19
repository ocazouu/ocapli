<?php
	/*
		FastReqBuildQueryString
	*/

class FastReqBuildQueryString
{
	private $configQuery;

	function __construct($configQuery)
	{
		$this->configQuery = $configQuery;
	}

	public function set_config_query($configQuery)
	{
		$this->configQuery = $configQuery;
	}
	public function build_query_select()
	{
		if(count($this->configQuery["select"]) !== 0)
		{
			$i = 0;
			$select = "SELECT ";
			foreach ($this->configQuery["select"] as $value)
			{
				$select .= ($i++ ? ", " : "") . $value;
			}
			$select .= " FROM";
		}
		else
		{
			$select = "SELECT * FROM";
		}
		return $select;
	}
	public function link_query_where($where)
	{
		if(substr($where, 0, 3) === "OR ")
		{
			$where = substr($where, 3);
			$link_query_where = " OR ";
		}
		elseif(substr($where, 0, 4) === "AND ")
		{
			$where = substr($where, 4);
			$link_query_where = " AND ";
		}
		else
		{
			$link_query_where = " AND ";
		}
		return  array($link_query_where, $where);
	}
	public function build_query_where()
	{
		$where = "";
		$i = 0;

		foreach ($this->configQuery["where"] as $linked_in_where)
		{
			if(is_array($linked_in_where))
			{
				foreach ($linked_in_where as $field => $value)
				{
					list($link_query_where, $field) = $this->link_query_where($field);

					$where .= ($i++ ? $link_query_where : "") . $field;
					$where .= (strpos($field, " ") !== false) ? " " : " = ";
					if($value !== false)
					{
						$where .= (is_integer($value)) ? $value : "'$value'";
					}
				}
			}
			else
			{
				list($link_query_where, $linked_in_where) = $this->link_query_where($linked_in_where);
				$where .= ($i++ ? $link_query_where : "") . "($linked_in_where)";
			}
		}

		$where = ($where == "") ? "" : "WHERE " . $where;

		return $where;
	}
	public function build_query_order()
	{
		$order = "";

		if(count($this->configQuery["order"]) !== 0)
		{
			$i = 0;
			$order = "ORDER BY ";
			foreach ($this->configQuery["order"] as $value)
			{
				$order .= ($i++ ? ", " : "") . $value;
			}
		}
		return $order;
	}
	public function build_query_group()
	{
		$group = "";

		if(count($this->configQuery["group"]) !== 0)
		{
			$i = 0;
			$group = "GROUP BY ";
			foreach ($this->configQuery["group"] as $value)
			{
				$group .= ($i++ ? ", " : "") . $value;
			}
		}
		return $group;
	}
	public function build_query_limit()
	{

		if(array_key_exists("all", $this->configQuery["limit"]))
		{
			if(array_key_exists("is_set", $this->configQuery["limit"]))
			{
				if($this->configQuery["limit"]["total"] && $this->configQuery["limit"]["total"] > 0)
				{
					$limit = "LIMIT " . $this->configQuery["limit"]["start"] . ", " . $this->configQuery["limit"]["total"];
				}
				else if($this->configQuery["limit"]["start"] > 0)
				{
					$limit = "LIMIT " . $this->configQuery["limit"]["start"];
				}
				else
				{
					return false;
				}
			}
			else
			{
				$limit = "";
			}
		}
		else if(array_key_exists("first", $this->configQuery["limit"]))
		{
			if(array_key_exists("is_set", $this->configQuery["limit"]))
			{
				if($this->configQuery["limit"]["total"] && $this->configQuery["limit"]["total"] > 0)
				{
					$limit = "LIMIT " . $this->configQuery["limit"]["start"] . ", 1";
				}
				else
				{
					if($this->configQuery["limit"]["start"] > 0)
					{
						$limit = "LIMIT " . $this->configQuery["limit"]["start"];
					}
					else
					{
						return false;
					}
				}
			}
			else
			{
				$limit = "LIMIT 1";
			}
		}
		else if(array_key_exists("last", $this->configQuery["limit"]))
		{
			if(array_key_exists("is_set", $this->configQuery["limit"]))
			{
				if($this->configQuery["limit"]["total"] && $this->configQuery["limit"]["total"] > 0)
				{
					$limit = "LIMIT " . ($this->configQuery["limit"]["start"] + $this->configQuery["limit"]["total"] - 1) . ", 1";	
				}
				else
				{
					if($this->configQuery["limit"]["start"] > 0)
					{
						$limit = "LIMIT " . $this->configQuery["limit"]["start"];
					}
					else
					{
						return false;
					}
				}
			}
			else
			{
				$this->configQuery["limit"]["all"] = true;
				$this->set_config_query($this->configQuery);

				$req_total = DbTool::get_requete($this->for_count());
				if($req_total)
				{
					$total = mysqli_fetch_array($req_total)[0];
					$limit = "LIMIT " . ($total - 1) . ", 1";
				}
			}
		}

		return $limit;
	}

	public function for_insert()
	{

		$default_config = $this->configQuery["columns"]->get_default_config();
		$model = $this->configQuery["model"];

		$sets = '';
		$values = '';

		$i = 0;
		foreach ($default_config as $key => $value)
		{
			$sets .= ( $i++ ? ', ' :  '' ) . $key;
			$values .= ( $i !== 1 ? ', ' :  '' );

			if(array_key_exists($key, $this->configQuery["insert"]))
			{
				$value = $this->configQuery["insert"][$key];
			}
			else
			{
				switch ($key)
				{
					case 'updated_at' : case 'created_at' :
						$value = date("Y-m-d H:i:s");
					break;
				}
			}

			if($this->configQuery["columns"]->structures[$key]["is_int"])
			{
				if($this->configQuery["columns"]->structures[$key]["auto_increment"])
				{
					$values .= "NULL";
				}
				else
				{
					$values .= (int)$value;
				}
			}
			else
			{
				$values .= "'$value'";
			}
		}

		return "INSERT INTO $model ($sets) VALUES ($values)";
	}
	public function for_update()
	{
		$limit = $this->build_query_limit();

		if($limit !== false)
		{
			$select = $this->build_query_select();
			$where  = $this->build_query_where();
		}
		else
		{
			return false;
		}

		$default_config = $this->configQuery["columns"]->get_default_config();
		$model = $this->configQuery["model"];

		$fields_values = '';

		$i = 0;
		foreach ($default_config as $key => $value)
		{

			if(array_key_exists($key, $this->configQuery["update"]))
			{
				$fields_values .= ( $i++ ? ', ' :  '' ) . $key . " = ";
				
				$value = $this->configQuery["update"][$key];

				if($this->configQuery["columns"]->structures[$key]["is_int"])
				{
					$fields_values .= (int)$value;
				}
				else
				{
					$fields_values .= "'$value'";
				}
			}
			else
			{
				switch ($key)
				{
					case 'updated_at' :
						$value = date("Y-m-d H:i:s");
					break;
				}
			}
		}

		return "UPDATE $model SET $fields_values $where $limit";
	}
	public function for_select()
	{
		$limit  = $this->build_query_limit();

		if($limit !== false)
		{
			$select = $this->build_query_select();
			$where  = $this->build_query_where();
			$group  = $this->build_query_group();
			$order  = $this->build_query_order();

			return "{$select} {$this->configQuery['model']} {$where} {$group} {$order} {$limit}";
		}
		else
		{
			return false;
		}
	}
	public function for_count($limit = false)
	{

		$select = "SELECT COUNT(*) FROM";
		$limit  = $this->build_query_limit();

		if($limit !== false)
		{
			$where  = $this->build_query_where();
			$group  = $this->build_query_group();
			return "{$select} {$this->configQuery['model']} {$where} {$group} {$limit}";
		}
		else
		{
			return false;
		}
	}
}
?>