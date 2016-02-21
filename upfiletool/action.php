<?php

$response 	= array();

function set_global_error($error_txt)
{
	global $config, $method, $id_config, $id_file, $id_record;

	$response 				= array();
	$response['error'] 		= true;
	$response['global'] 	= true;
	$error_txt			   .= "\n";
	$error_txt			   .= "\n method: "    . $method;
	$error_txt			   .= "\n config: "    . $config;
	$error_txt			   .= "\n id_config: " . $id_config;
	$error_txt			   .= "\n id_file: "   . $id_file;
	$error_txt			   .= "\n id_record: " . $id_record;
	$response['error_txt']	= $error_txt;

	return $response;
}

if(!is_admin())
{
	$response = set_global_error('Erreur: Session non valide');
}
else
{


	Cache::set_instance();
	MySQL::set_instance();

	if(!MySQL::is_connect())
	{
		$include = "inc/page_mysql_error.php";
		$website_errors = true;
	}

	new FastReqInstance($fast_req_config);
	
	
	$config    = isset($_POST['config'])           ? $_POST['config']      :  false;
	$method    = isset($_POST['method'])           ? $_POST['method']      :  false;
	$id_config = (int)isset($_POST['identconfig']) ? $_POST['identconfig'] :  0;
	$id_file   = (int)(isset($_POST['identfile']) && $_POST['identfile']  != 'false')  ? $_POST['identfile']   :  0;
	$id_record = (int)isset($_POST['identrecord']) ? $_POST['identrecord'] :  0;

	if($config)
	{
		$upfiletool = new Files($config,$id_config,$id_file,$id_record);
		if($upfiletool->config_find)
		{
			switch ($method)
			{
				case 'render_global_view':
					$response = $upfiletool->renderGlobalView();
				break;

				case 'render_list_view':
					$response = $upfiletool->renderListView();
				break;

				case 'render_list_and_edit_view':
					$response = $upfiletool->renderListAndEditView();
				break;

				case 'render_edit_view':
					$response = $upfiletool->renderEditView();
				break;
				
				default:
					$response = set_global_error("Erreur: Action non définie");
				break;
			}
			if(!$response)
			{
				$response = set_global_error("Erreur: Execution sur methode non valide");
			}
		}
		else
		{
			set_global_error("Erreur: Aucune configuration définie pour ce config");
		}

	}
	else
	{
		set_global_error("Erreur: Le config est non défini");
	}

}


echo json_encode($response);
?>