<?php

class FastReqScope
{

 	public
		$configQuery =  Array(
			"select"  => Array(),
			"where"   => Array(),
			"group"   => Array(),
			"order"   => Array(),
			"limit"   => Array()
		);
	public
		$count = 3;

	private
		$model,
		$ClassModel,
		$configs,
		$query_string,
		$records = Array("unload" => true);

	function __construct($model, $configs)
	{
		$this->model      = $model;
		$this->ClassModel = $configs["model"];
		$this->configs    = $configs;
	}
	/*
		$merge_to_config_query: string or array, SQL expression: SELECT
			Work with 
				- ::select(["id", "title"])
				- ::select("id, title")

		Merge and keep unique into $this->configQuery["select"]
	*/
	function select($merge_to_config_query = array())
	{
		$merge_to_config_query = is_array($merge_to_config_query) ? 
									$merge_to_config_query : 
									explode(",",strtr($merge_to_config_query,array(" ,"=>",",", "=>",")));
		
		$this->configQuery["select"] = array_unique(array_merge($this->configQuery["select"], $merge_to_config_query));

		return $this;
	}
	/*
		$merge_to_config_query: string or array, SQL expression: GROUP BY
			Work with 
				- ::group(["id", "title"])
				- ::group("id, title")

		Merge and keep unique into $this->configQuery["group"]
	*/
	function group($merge_to_config_query = array())
	{
		$merge_to_config_query = is_array($merge_to_config_query) ? 
									$merge_to_config_query : 
									explode(",",strtr($merge_to_config_query,array(" ,"=>",",", "=>",")));
		
		$this->configQuery["group"] = array_unique(array_merge($this->configQuery["group"], $merge_to_config_query));

		return $this;
	}
	/*
		work in progress!!

		$merge_to_config_query: string or array, SQL expression: WHERE
			Work with 
				- ::where([
					"active" => 1,
					"created_at >" => "NOW",
					"OR updated >" => "NOW"
				])
				- ::group("created_at > NOW AND active = 1")


		every filter are preserved (does not keep unique)

		Actualy
			chain if receives an array
			puts parentheses if receives a chain

		Todo: 
	       parentheses recursive with multi-array, 
	       better strategy with "or, and, &" ...
	*/
	function where($merge_to_config_query = array())
	{
		if(empty($merge_to_config_query))
		{
			return $this;
		}

		$this->configQuery["where"][] = $merge_to_config_query;

		return $this;
	}
	/*
		$merge_to_config_query: string or array, SQL expression: ORDER BY
			Work with 
				- ::group(["id DESC", "title ASC"])
				- ::group("id DESC, title ASC")

		Merge and keep unique into $this->configQuery["order"]
	*/
	function order($merge_to_config_query = array())
	{
		$merge_to_config_query = is_array($merge_to_config_query) ? 
									$merge_to_config_query : 
									explode(",",strtr($merge_to_config_query,array(" ,"=>",",", "=>",")));

		$this->configQuery["order"] = array_unique(array_merge($this->configQuery["order"], $merge_to_config_query));

		return $this;
	}
	/*
		receives 2 integer argument (optional)
	*/
	function limit($start=0, $total = false)
	{
		$this->configQuery["limit"] = array(
			"start"  => $start, 
			"total"  => $total, 
			"is_set" => true
		);	
		return $this;
	}


	/*
	##########################################
	# Out point
	##########################################
	*/


	/* 
		execute a count query if return unload
		return number total of records
	*/
	function count()
	{
		if(isset($this->records["unload"]))
		{
			$this->configQuery["limit"]["all"] = true;

			$buildQuery = FastReq::$queryBuilder[$this->model];
			$buildQuery->set_config_query($this->configQuery);

			$count = MySQL::get_request($buildQuery->for_count());

			if($count)
			{
				return (int)mysqli_fetch_array($count)[0];
			}
		}
		else
		{
			return count($this->records);
		}
	}
	/* first all records of scope	*/
	function all()
	{
		$this->configQuery["limit"]["all"]   = true;
		$this->configQuery["limit"]["first"] = null; // check with isset, dont set false

		$this->load_records();
		return $this->get_records(0, count($this->records));
	}
	/* first record of scope	*/
	function first()
	{
		$this->configQuery["limit"]["first"] = true;
		$this->configQuery["limit"]["all"]   = null; // checked with isset, dont set false

		$this->load_records();
		return $this->get_record();
	}
	/* last record of scope	*/
	function last()
	{
		$this->configQuery["limit"]["last"] = true;
		$this->load_records();
		return $this->get_record(count($this->records)-1);
	}
	/* Create one record */
	function create($config)
	{
		$this->configQuery["insert"] = $config;

		$buildQuery = FastReq::$queryBuilder[$this->model];
		$buildQuery->set_config_query($this->configQuery);

		$req_insert = MySQL::get_request($buildQuery->for_insert());

		if($req_insert)
		{	
			return true;
		}
		else
		{
			return false;
		}
	}
	/* Return one created record */
	function create_and_use($config)
	{
		if($this->create($config))
		{
			$unique         = FastReq::$configs[$this->model]["columns"]["unique"];
			$auto_increment = FastReq::$configs[$this->model]["columns"]["auto_increment"];

			/* Try to match record an unique increment field (id) */
			if($auto_increment)
			{
				$mysqli_insert_id = mysqli_insert_id(MySQL::get_instance()->get_mysql_id());

				return $this->where([$auto_increment => $mysqli_insert_id])->first();
			}
			/* Else try to match if unique fields exist */
			else if(count($unique) > 0)
			{
				foreach ($unique as $key => $value)
				{
					if(isset($config[$key]))
					{
						$this->where([$key => $config[$key]]);
					}
				}
				return $this->first();
			}
			/* Else try to match other field */
			else
			{
				return $this->where($config)->first();
			}
		}
		else
		{
			return false;
		}
	}
	/* 
		Receives one simple array of model data
		try to update all records of scope and return true or false 
	*/
	function update_all($config)
	{
		$this->configQuery["update"] = $config;

		if(in_array("before_save", $this->configs["class_methods"]))
		{
			$ClassModel = $this->ClassModel;
			$ClassModel::before_save($this);
		}

		$buildQuery = FastReq::$queryBuilder[$this->model];
		$buildQuery->set_config_query($this->configQuery);

		$req_update = MySQL::get_request($buildQuery->for_update());

		if($req_update)
		{	
			return true;
		}
		else
		{
			return false;
		}
	}
	/* 
		Receives one simple array of model data, like update_all
		Execute update and return all updated records
	*/
	function update_all_and_use($config)
	{
		if($this->update_all($config))
		{
			return $this->all();
		}
		else
		{
			return false;
		}
	}
	/*
		Try to delete all records of scope and return true or false
	*/
	function delete_all()
	{
		$buildQuery = FastReq::$queryBuilder[$this->model];
		$buildQuery->set_config_query($this->configQuery);

		$req_delete = MySQL::get_request($buildQuery->for_delete());

		if($req_delete)
		{	
			return true;
		}
		else
		{
			return false;
		}
	}


	/*
	##########################################
	# Private methods
	##########################################
	*/


	/*
		return one record (first, last)
	*/
	private function get_record($rang = 0)
	{
		return (
			isset($this->records[$rang]) ? 
			$this->records[$rang] : 
			false
		);
	}
	/*
		return array of records (all)
	*/
	private function get_records($start = 0, $total = 0)
	{
		$records = [];

		for($i=$start;$i<$total;$i++)
		{
			$records[] = $this->get_record($i);
		}

		return $records;
	}

	function get_query()
	{
		$this->configQuery["limit"]["all"]   = true;
		$this->configQuery["limit"]["first"] = null; // check with isset, dont set false

		return $this->load_records(true);
	}

	/*
		Execute query_string 
		push records into $this->records
	*/
	private function load_records($return_query = false)
	{
		$this->records = Array();

		$buildQuery = FastReq::$queryBuilder[$this->model];
		$buildQuery->set_config_query($this->configQuery);

		$this->query_string = $buildQuery->for_select();

		if($this->query_string)
		{
			$query = MySQL::get_request($this->query_string);

			if ($query)
			{
				if($return_query === false)
				{
					while ($array = mysqli_fetch_array($query, MYSQL_ASSOC))
					{
						$this->records[]  = $array;
					}
				}
				else
				{
					return $query;
				}

				MySQL::free_result();

				return true;
			}
			return false;
		}
		return false;
	}
}

?>