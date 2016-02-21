<?php

class ApiController
{
	private $model;
	private $ClassModel;

	function __construct()
	{

		if(!is_admin())
		{
			$this->return_error("Invalid session.");
			return false;
		}

		$action = $_GET['action'];
		$this->model  = isset($_GET['model']) ? $_GET['model'] : false;

		if($this->model)
		{
			$this->ClassModel = FastReq::$configs[$this->model]["model"];	
		}

		if(!method_exists($this, $action))
		{
			$this->return_error("Action is undefined");
			return false;
		}

		$this->$action();

		return true;
	}
	/* Action methods */
	private function get_configs()
	{
		$this->set_response(FastReq::$configs);
	}
	/* Return methods */
	private function return_error($text)
	{
		$this->set_response([
			"error"       => true,
			"error_text"  => $text
		]);
		return true;
	}

	private function get_attributs($type = "GET")
	{
		$attributs = $type === "POST" ? $_POST : $_GET;
		$valid_attributs = array();

		foreach (FastReq::$configs[$this->model]["columns"]["structures"] as $key => $field)
		{
			if(isset($attributs[$key]))
			{
				$valid_attributs[$key] = $attributs[$key];
			}
		}
		return (count($valid_attributs) !== 0) ? $valid_attributs : false;
	}
	private function get_conditions_attributs()
	{
		$conditions_attributs = array();

		if(isset($_GET["conditions"]))
		{
			foreach ($_GET["conditions"] as $key => $value)
			{
				$conditions_attributs[] = $value["field"] . " " . $value["operator"] . " '" . $value["value"] . "'";
			}
		}

		return $conditions_attributs;
	}
	private function get_select_attributs()
	{
		$select = "*";
		$i=false;

		if(isset($_GET["select"]))
		{
			$select = "";
			foreach ($_GET["select"] as $key => $value)
			{
				$select .= ($i ? " ," : "") . $value;
				$i= true;
			}
		}

		return $select;
	}
	private function index()
	{
		$ClassModel = $this->ClassModel;
		$attributs  = $this->get_attributs();
		$select     = $this->get_select_attributs();

		$conditions_attributs = $this->get_conditions_attributs();
		$has_conditions = count($conditions_attributs) !== 0;

		if($attributs || $has_conditions)
		{
			$scope = $ClassModel::select($select)->where($attributs);

			if($has_conditions)
			{
				$scope->where($conditions_attributs);
			}

			$records = $scope->all();

			if(is_array($records))
			{
				$this->set_response($records);
			}
			else
			{
				$this->return_error("Invalid scope into index method with " . $this->model);
			}
			return true;
		}
		else
		{
			$this->return_error("Empty attributs into index method with " . $this->model);
			return false;
		}
	}
	private function update()
	{
		$ClassModel = $this->ClassModel;
		$attributs  = $this->get_attributs();

		if($attributs)
		{
			$data = $this->get_attributs("POST");

			if($ClassModel::where($attributs)->update_all($data))
			{
				$this->set_response("Record successfully updated");
				return true;
			}
			else
			{
				$this->return_error("Update has failed, attributs: {" . print_r($attributs, 1) . "}, data: {" . print_r($data, 1) . "}, model: " . $this->model);
				return false;
			}
		}
		else
		{
			$this->return_error("Invalid attributs for update with model: " . $this->model);
			return false;
		}

		return true;
	}
	private function create()
	{
		$data = $this->get_attributs("POST");

		$ClassModel = $this->ClassModel;
		if($ClassModel::create($data))
		{
			$this->set_response("Record successfully created");
			return true;
		}else{
			$this->return_error("Invalid attributs for create with model: " . $this->model);
			return false;
		}
	}
	private function delete()
	{
		$ClassModel = $this->ClassModel;
		$attributs  = $this->get_attributs();

		if($attributs)
		{
			$records = $ClassModel::where($attributs)->delete_all();
			if($records)
			{
				$this->set_response("Record successfully deleted");
				return true;
			}
			else
			{
				$this->return_error("Error with action deleted with model " . $this->model);
				return false;
			}
		}
		else
		{
			$this->return_error("Invalid attributs into delete method with " . $this->model);
			return false;
		}
	}
	private function set_response($return_object)
	{
		$response = json_encode($return_object);
		if($response)
		{
			echo $response;
		}
		else
		{
			$this->return_error("Error with return data: " . print_r($return_object, 1));
		}
		return true;
	}
}
?>