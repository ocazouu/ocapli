<?php
	/*
		FastReqQueryBuilder
	*/

class FastReqQueryBuilder
{
	private 
		$configQuery,
	 	$structures,
	 	$model,
	 	$relations;

	function __construct($structures, $default_config, $model, $relations)
	{
		$this->structures     = $structures;
		$this->default_config = $default_config;
		$this->model          = $model;
		$this->relations      = $relations;
	}
	public function set_config_query($configQuery)
	{
		$this->configQuery = $configQuery;
	}
	public function for_insert()
	{

		$sets   = '';
		$values = '';
		$i      = false;


		if(isset($this->default_config["updated_at"]))
		{
			$this->configQuery["insert"]["updated_at"] = date("Y-m-d H:i:s");;
		}
		if(isset($this->default_config["created_at"]))
		{
			$this->configQuery["insert"]["created_at"] = date("Y-m-d H:i:s");;
		}
		foreach ($this->default_config as $key => $value)
		{
			if($i)
			{
				$sets   .= ", {$key}";
				$values .= ', ';
			}
			else
			{
				$sets .= $key;
				$i     = true;
			}

			if(isset($this->configQuery["insert"][$key]))
			{
				$value   = $this->configQuery["insert"][$key];
			}

			if($this->structures[$key]["is_int"])
			{
				$values .= $this->structures[$key]["auto_increment"] ? "NULL" : (int)$value;
			}
			else
			{
				$value 	 = $this->escape($value);
				$values .= "'$value'";
			}
		}

		return "INSERT INTO $this->model ($sets) VALUES ($values)";
	}
	public function for_update()
	{
		$limit         = $this->build_query_limit("update");
		$where         = $this->build_query_where();

		$fields_values = '';
		$i             = false;

		if(isset($this->default_config["updated_at"]))
		{
			$this->configQuery["update"]["updated_at"] = date("Y-m-d H:i:s");;
		}
		foreach ($this->default_config as $key => $value)
		{

			if(isset($this->configQuery["update"][$key]))
			{
				if($i)
				{
					$fields_values .= ", {$key} = ";
				}
				else
				{
					$fields_values .= "{$key} = ";
					$i = true;
				}

				$value = $this->configQuery["update"][$key];

				if($this->structures[$key]["is_int"])
				{
					$fields_values .= (int)$value;
				}
				else
				{
					$value          = $this->escape($value);
					$fields_values .= "'$value'";
				}
			}
		}

		//__("UPDATE $this->model SET $fields_values $where $limit");

		return "UPDATE $this->model SET $fields_values $where $limit";
	}
	public function for_select()
	{
		$limit           = $this->build_query_limit();

		if($limit !== false)
		{
			list($select, $relations) = $this->build_query_select();

			$where = $this->build_query_where($relations);
			$group = $this->build_query_group();
			$order = $this->build_query_order();

			if(count($relations) !== 0)
			{
				$query_relations = ", " . implode(", ", $relations);
			}
			else
			{
				$query_relations = '';
			}

			return "{$select} FROM {$this->model}{$query_relations} {$where} {$group} {$order} {$limit}";
		}
		else
		{
			return false;
		}
	}
	public function for_delete()
	{
		$limit         = $this->build_query_limit("delete");
		$where         = $this->build_query_where();

		return "DELETE FROM $this->model $where $limit";
	}
	public function for_count($limit = false)
	{
		$limit = $this->build_query_limit();

		list($select, $relations) = $this->build_query_select(true);

		if($limit !== false)
		{
			$where  = $this->build_query_where($relations);
			$group  = $this->build_query_group();

			if(count($relations) !== 0)
			{
				$query_relations = ", " . implode(", ", $relations);
			}
			else
			{
				$query_relations = '';
			}

			return "{$select} FROM {$this->model}{$query_relations} {$where} {$group} {$limit}";
		}
		else
		{
			return false;
		}
	}
	private function build_query_select($for_count = false)
	{
		$relations = array();

		if(count($this->configQuery["select"]) !== 0)
		{
			$query_select = implode(", ", $this->configQuery["select"]);

			if(count($this->relations) !== 0 && strpos($query_select, ".") !== false)
			{
				$selects      = explode(",", $query_select);
				$query_select = "";
				$i            = false;

				foreach ($selects as $value)
				{
					$value = trim($value);
					if(strpos($value, ".") === false)
					{
						if(isset($this->structures[$value]))
						{
							$value = $this->model . "." . $value;
						}
					}
					else
					{
						$model_col = explode(".", $value);
						if($model_col[0] !== $this->model)
						{
							array_push($relations, $model_col[0]);
							array_unique($relations);
						}
					}

					if($i)
					{
						$query_select .= ", ";
					}
					else
					{
						$i = true;
					}

					$query_select .= $value;
				}
			}

			$select = $for_count ? "SELECT COUNT(*), $query_select" : "SELECT $query_select";
		}
		else
		{
			$select = $for_count ? "SELECT COUNT(*) " : "SELECT *";
		}
		return array($select, $relations);
	}
	private function build_query_where($relations = array())
	{
		$where          = "";
		$i              = false;
		$adapt_relation = false;

		if(count($relations) !== 0)
		{
			foreach ($this->relations as $type => $relations_by_type)
			{
				foreach ($relations_by_type as $relation)
				{
					if(in_array($relation, $relations))
					{
						$adapt_relation = true;

						if($type === "has_many")
						{
							$where .= ($i ? " AND" : "") . " {$relation}.id_{$this->model} = {$this->model}.id";
							$i = true;
						}
						elseif($type === "belongs_to")
						{
							$where .= ($i ? " AND" : "") . " {$relation}.id = {$this->model}.id_{$relation}";
							$i = true;
						}
					}
				}
			}
		}

		$where .= $this->build_filter_where($this->configQuery["where"], $adapt_relation, $i);

		$where = ($where === "") ? "" : "WHERE " . $where;

		return $where;
	}
	private function set_boolean_instruction($sql)
	{
		$boolean = " AND ";
		if(substr($sql, 0, 3) === "OR ")
		{
			$sql = substr($sql, 3);
			$boolean = " OR ";
		}
		elseif(substr($sql, 0, 4) === "AND ")
		{
			$sql = substr($sql, 4);
			$boolean = " AND ";
		}
		else
		{
			$boolean = " AND ";
		}
		return  array($boolean, $sql);
	}
	private function filter_with_key($key, $value, $adapt_relation, $i)
	{
		$boolean = "";
		$operator = "";

		list($new_boolean, $key) = $this->set_boolean_instruction($key);

		if($i)
		{
			$boolean = $new_boolean;
		}

		if($adapt_relation && strpos($key, ".") === false)
		{
			$format_field = trim(explode(" ", $key)[0]);

			if(isset($this->structures[$format_field]))
			{
				$key = ($this->model . "." . $key);
			}
		}

		if(strpos($key, " ") !== false)
		{
			$operator = " ";
		}
		else
		{
			$operator = " = ";
		}

		$value = "'$value'";
		return $boolean.$key.$operator.$value;
	}
	private function filter_with_string($string, $i)
	{
		list($boolean, $string) = $this->set_boolean_instruction($string);
		if($i)
		{
			$string = $boolean.$string;
		}
		return "$string";
	}
	private function build_filter_where($config_query_where, $adapt_relation, $i)
	{
		$where = "";

		foreach ($config_query_where as $filters)
		{
			if(is_array($filters))
			{
				foreach($filters as $key => $value)
				{
					if(is_int($key)) 
					{
						if(is_array($value)) /* recursive mode... crazy usage */
						{
							$where .= $this->build_filter_where($value, $adapt_relation, $i);
						}
						else
						{
							$where .= $this->filter_with_string($value, $i);
							$i = true;
							/* mode: only sql string */
						}
					}
					else
					{
						/* mode: key => value */
						$where .= $this->filter_with_key($key, $value, $adapt_relation, $i);
						$i = true;
					}
				}
			}
			else
			{
				$where .= $this->filter_with_string($filters, $i);
				$i = true;
			}
		}

		return $where;
	}
	private function build_query_order()
	{
		$order = "";

		if(count($this->configQuery["order"]) !== 0)
		{
			$order = "ORDER BY " . implode(", ", $this->configQuery["order"]);
		}
		return $order;
	}
	private function build_query_group()
	{
		$group = "";
		
		if(count($this->configQuery["group"]) !== 0)
		{
			$group = "GROUP BY " . implode(", ", $this->configQuery["group"]);
		}
		return $group;
	}
	private function build_query_limit($update_or_delete=false)
	{
		$limit = false;
		$start =& $this->configQuery["limit"]["start"];
		$total =& $this->configQuery["limit"]["total"];

		if($update_or_delete)
		{
			if(isset($total) && $total > 0)
			{
				$limit = "LIMIT " . $start . ", " . $total;
			}
			else if(isset($start) && $start > 0)
			{
				$limit = "LIMIT " . $start;
			}
			return $limit;
		}

		if(isset($this->configQuery["limit"]["first"]))
		{
			if(isset($this->configQuery["limit"]["is_set"]))
			{
				if(isset($total) && $total > 0)
				{
					$limit = "LIMIT " . $start . ", 1";
				}
				else
				{
					if($start > 0)
					{
						$limit = "LIMIT " . $start;
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
		elseif(isset($this->configQuery["limit"]["all"]))
		{
			if(isset($this->configQuery["limit"]["is_set"]))
			{
				if(isset($total) && $total > 0)
				{
					$limit = "LIMIT " . $start . ", " . $total;
				}
				else if($start > 0)
				{
					$limit = "LIMIT " . $start;
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
		else if(isset($this->configQuery["limit"]["last"]))
		{
			if(isset($this->configQuery["limit"]["is_set"]))
			{
				if(isset($total) && $total > 0)
				{
					$limit = "LIMIT " . ($start + $total - 1) . ", 1";	
				}
				else
				{
					if($start > 0)
					{
						$limit = "LIMIT " . ( $start - 1) . ", 1";
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

				$req_total = MySQL::get_request($this->for_count());
				if($req_total)
				{
					$total = mysqli_fetch_array($req_total)[0];
					$limit = "LIMIT " . ($total - 1) . ", 1";
				}
			}
		}

		return $limit;
	}
	private function escape($value)
	{
		return mysqli_real_escape_string(MySQL::get_instance()->get_mysql_id(), $value);
	}
}
?>
