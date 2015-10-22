<?php

class FasReqInstanceRecords
{

 	public
		$configQuery =  Array(
			"columns" => Array(),
			"model"   => Array(),
			"select"  => Array(),
			"insert"  => Array(),
			"where"   => Array(),
			"group"   => Array(),
			"order"   => Array(),
			"limit"   => Array()
		);
	public
		$count = 3;

	private 
		$columns,
		$query_string,
		$buildSelectQueryString,
		$records = Array("unload" => true);

	function __construct($config)
	{
		$this->configQuery["columns"] = $config["columns"];
		$this->configQuery["model"]   = $config["model"];
	}
	/*
		# Receives string or array and set configQuery["select"] SQL expression: SELECT
		#
		# Separate and store every fields into array
		# Merge with previous config and keep unique
	*/
	function select($merge_to_config_query = array())
	{
		$merge_to_config_query = is_array($merge_to_config_query) ? $merge_to_config_query : explode(",",strtr($merge_to_config_query,array(" ,"=>",",", "=>",")));
		
		$this->configQuery["select"] = array_unique(array_merge($this->configQuery["select"], $merge_to_config_query));

		return $this;
	}
	/*
		# Receives string or array and set configQuery["group"] For SQL expression: GROUP BY
		#
		# Separate and store every fields into array
		# Merge with previous config and keep unique
	*/
	function group($merge_to_config_query = array())
	{
		$merge_to_config_query = is_array($merge_to_config_query) ? $merge_to_config_query : explode(",",strtr($merge_to_config_query,array(" ,"=>",",", "=>",")));
		
		$this->configQuery["group"] = array_unique(array_merge($this->configQuery["group"], $merge_to_config_query));

		return $this;
	}
	/*
		# Receives string or array and set configQuery["where"] For SQL expression: WHERE
		# 
		# Push string or array into configQuery["where"]
		# every filter are preserved
	*/
	function where($merge_to_config_query = array())
	{
		$this->configQuery["where"][] = $merge_to_config_query;

		return $this;
	}
	/*
		# Receives string or array and set configQuery["order"] For SQL expression: ORDER
		#
		# Separate and store every fields into array
		# Merge with previous config and keep unique
	*/
	function order($merge_to_config_query = array())
	{
		$merge_to_config_query = is_array($merge_to_config_query) ? $merge_to_config_query : explode(",",strtr($merge_to_config_query,array(" ,"=>",",", "=>",")));

		$this->configQuery["order"] = array_unique(array_merge($this->configQuery["order"], $merge_to_config_query));

		return $this;
	}
	/*
		# Receives 2 integer argument (optional)
		#
		# Separate and store every fields into array
		# Merge with previous config and keep unique
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
		# 
	*/
	function all()
	{
		$this->configQuery["limit"]["all"] = true;
		$this->load_records();
		return $this->get_records(0, count($this->records));
	}
	/*
		# 
	*/
	function count()
	{
		if(array_key_exists("unload", $this->records))
		{
			$this->configQuery["limit"]["all"] = true;
			$this->buildQueryString = new FastReqBuildQueryString($this->configQuery);
			$count = DbTool::get_request($this->buildQueryString->for_count($this->configQuery));
			if($count)
			{
				return mysqli_fetch_array($count)[0];
			}
		}
		else
		{
			return count($this->records);
		}
	}
	/*
		# 
	*/
	function first()
	{
		$this->configQuery["limit"]["first"] = true;
		$this->load_records();
		return $this->get_record();
	}
	/*
		# 
	*/
	function last()
	{
		$this->configQuery["limit"]["last"] = true;
		$this->load_records();
		return $this->get_record(count($this->records)-1);
	}

	function create($config)
	{
		$this->configQuery["insert"] = $config;

		$BuildQuery = new FastReqBuildQueryString($this->configQuery);
		$req_insert = dbtool::get_request($BuildQuery->for_insert());

		if($req_insert)
		{	
			return true;
		}
		else
		{
			return false;
		}
	}
	function create_and_use($config)
	{
		if($this->create($config))
		{
			$auto_increment_column = $this->configQuery["columns"]->auto_increment_column;

			if($auto_increment_column)
			{
				$mysqli_insert_id = mysqli_insert_id(dbtool::get_instance()->get_mysql_id());
				return $this->where([$auto_increment_column => $mysqli_insert_id])->first();
			}
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
	function update($config)
	{
		$this->configQuery["update"] = $config;

		$BuildQuery = new FastReqBuildQueryString($this->configQuery);
		$req_update = dbtool::get_request($BuildQuery->for_update());

		if($req_update)
		{	
			return true;
		}
		else
		{
			return false;
		}
	}
	function update_and_use($config)
	{
		if($this->update($config))
		{
			return $this->records;
		}
		else
		{
			return false;
		}
	}
	/*
		# 
	*/
	private function get_record($rang = 0)
	{
		return (
			array_key_exists($rang, $this->records) ? 
			$this->records[$rang] : 
			false
		);
	}
	/*
		return array of records
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
	/*
		Execute query_string and push records into $this->records
	*/
	private function load_records()
	{
		if(array_key_exists("unload", $this->records))
		{
			$this->records = Array();

			if($this->configQuery["model"])
			{
				$this->buildSelectQueryString = new FastReqBuildQueryString($this->configQuery);
				$this->query_string = $this->buildSelectQueryString->for_select();

				if($this->query_string)
				{
					$query = DbTool::get_request($this->query_string);

					if ($query)
					{
						while ($array = mysqli_fetch_array($query))
						{
							$this->records[] = $array;
						}

						return true;
					}
				}
			}
		}
		else
		{	
			$old_query = $this->buildSelectQueryString->for_select();

			$this->buildSelectQueryString->set_config_query($this->configQuery);			

			$new_query = $this->buildSelectQueryString->for_select();
			
			if($old_query != $new_query)
			{
				$this->records = Array("unload"=>true);
				return $this->load_records();
			}
			else
			{
				return true;
			}
		}

		return false;
	}
}

?>