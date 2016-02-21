<?php
/*
	$this->global_configs
	[
		configs (relation string to model, module, page...)
		[
			id_config (todo :  set a key name)
			[
				'multiple'	=> 'multiple', // or none
				'name'		=> 'cover',
				'records' 	=> 0,
				'type' 		=> 'image', // or anything else
				'format' 	=> 'jpg',
				'sizes' 	=> [
					'small'		=> ['x' => 100, 'y' => 150],
					'big'		=> ['x' => 200, 'y' => 300]
				]
			],
			[
				(...)
			]
		],
		[
			(...)
		]
	]

	single object config => $this->configs[$this->id_config]
*/

class Files
{
	public   $config_find = false;
	public   $error;
	public   $config;
	public   $id_config;
	public   $configs;
	public   $id_record;
	private  $global_configs = [
		'home' =>
		[
			[
				'id'		=> 'mea',
				'multiple'	=> 'none',
				'name'		=> 'Illustrations',
				'records' 	=> 0,
				'type' 		=> 'image',
				'format' 	=> 'jpg',
				'sizes' 	=> [
					'standart'	=> ['x' => 700, 'y' => 700]
				]
			]
		]
	];

	function __construct($config,$id_config=0,$id_file=0,$id_record=0)
	{
		$this->configs   = isset($this->global_configs[$config]) ? $this->global_configs[$config] : false;
		$this->id_record = $id_record;
		$this->id_config = $id_config;
		$this->id_file   = $id_file;

		if($this->configs)
		{
			$this->config_find = true;
			$this->config = $config;
		}
		else
		{
			$this->config_find = false;
		}
	}
	function renderBtOpen()
	{
		if($this->config_find)
		{
			echo '<button ident="upfiletool" identmethod="render_global_view" config="'.$this->config.'" identconfig="0" identrecord="'.$this->id_record.'">Editer les médias</button>';
		}
	}
	function renderGlobalView()
	{
		if($this->config_find)
		{
			return array(
				'config' 			=> $this->config,
				'identrecord' 		=> $this->id_record,
				'method'            => 'renderGlobalView',
				'configs' 			=> $this->configs,
				'render_list_view'  => (isset($this->configs[$this->id_config]['multiple']) && 
										$this->configs[$this->id_config]['multiple'] === 'multiple') ? 
										$this->renderListView() : false,
				'render_edit_view'  => $this->renderEditView()
			);
		}
		return false;
	}
	function renderListAndEditView()
	{
		if($this->config_find)
		{
			return array(
				'config' 			=> $this->config,
				'identrecord' 		=> $this->id_record,
				'method'            => 'renderListAndEditView',
				'configs' 			=> $this->configs,
				'render_list_view'  => $this->renderListView(),
				'render_edit_view'  => $this->renderEditView()
			);
		}
		return false;
	}
	function renderListView()
	{
		if($this->config_find)
		{
			$files = UpFiles::where([
				"config" => $this->config,
				"id_config" => $this->id_config ,
				"id_record" => $this->id_record
			])->order("ordre ASC, id DESC")->all();

			return array(
				'config'      	   => $this->config,
				'identrecord' 	   => $this->id_record,
				'method'      	   => 'renderListView',
				'identconfig'      => $this->id_config,
				'files'       	   => $files
			);
		}
		return false;
	}
	function renderEditView()
	{
		$multiple = isset($this->configs[$this->id_config]['multiple']) ? 
					$this->configs[$this->id_config]['multiple'] : false;

		if(!$this->id_file || $this->id_file == 'false')
		{
			$first_file    = $this->getFirstFile();
			$this->id_file = $first_file ? $first_file->id : ($multiple == 'multiple' ? 0 : $this->addORreplaceFile(false,true));
		}
		if($this->id_file !== false)
		{
			$obj_file = $this->getFile();

			return array(
				'config'      => $this->config,
				'identrecord' => $this->id_record,
				'identconfig' => $this->id_config,
				'method'      => 'renderEditView',
				'identfile'   => $this->id_file,
				'multiple' 	  => $multiple
			);
		}
		return false;
	}
	function addORreplaceFile($data=false,$force_add=false)
	{

		$id_file     = $force_add ? 'NULL' : $this->id_file;
		if(!$id_file)
		{
			$first_file    = $this->getFirstFile();
			$id_file = $first_file ? $first_file->id : 'NULL';
		}

		$create = UpFiles::replace([
			"id"          => $id_file,
			"etat"        => $data ? $data['etat'] : 0,
			"name"        => $data ? $data['name'] : 'Fichier vide',
			"description" => $data ? $data['description'] : '',
			"version"     => $data ? $data['version'] : 0,
			"ordre"       => $data ? $data['ordre'] : 0
		]);

		if($create)
		{
			if($id_file == 'NULL')
			{
				$this->id_file = $create;
			}
			return $this->id_file;
		}

		return false;
	}
	function addFile($data=false)
	{
		return $this->addORreplaceFile(false,true);
	}
	function removeFile($data=false)
	{
		dbtool::getRequete("DELETE FROM files WHERE id = $this->id_file LIMIT 1");

		$dir       = 'up' . '/' . $this->config . '/' . $this->id_record . '/' . $this->id_file . '/';
		if(is_dir($dir))
		{
			clearDir($dir);
		}
	}
	function getFirstFile()
	{
		return UpFiles::where([
			"config" => $this->config,
			"id_config" => $this->id_config ,
			"id_record" => $this->id_record
		])->order("ordre ASC, id DESC")->first();
	}
	function getRandFile($LIMIT = 1, $SELECT = '*', $WHERE = 'etat = 1')
	{
		return UpFiles::where([
			"config" => $this->config,
			"id_config" => $this->id_config ,
			"id_record" => $this->id_record
		])->order("RAND()")->first();
	}
	function getFile()
	{
		return UpFiles::where([
			"id" => $this->id_file
		])->first();
	}
	function getFiles($ORDER = 'id DESC', $SELECT = '*', $WHERE = 'etat = 1')
	{
		return UpFiles::where([
			"config" => $this->config,
			"id_config" => $this->id_config ,
			"id_record" => $this->id_record
		])->where($WHERE)->all();
	}
	function upFileVersion()
	{
		dbtool::getRequete("UPDATE files SET version = version + 1 WHERE id = $this->id_file LIMIT 1");
	}
	function upFile()
	{
		$tmp_name = $_FILES["file"]["tmp_name"];
		
		if (!isset($_FILES["file"]) || !is_uploaded_file($tmp_name) || $_FILES["file"]["error"] != 0)
		{
			$this->error = "Erreur: upload invalide";
			return false;
		}
		
		$dir  	   = $this->makeDirs();
		if($dir)
		{
			$path_file = $dir.$this->id_file.'.'.$this->configs[$this->id_config]['format'];

			if(!move_uploaded_file($tmp_name, $path_file))
			{
				$this->error = "Erreur: L'upload a échoué.";
				return false;
			}

			if($this->configs[$this->id_config]['type'] == 'image')
			{
				$this->upFileVersion();
			}
			else
			{
				$this->upFileVersion();
			}
		}
	}
	function getSrcFile($file=false)
	{
		return INDEX_GLOBAL . $this->getPathFile($file) . '?v=' . $file->version;
	}
	function getPathFile($file=false)
	{
		return $this->getDirFile($file->id) . $file->id .'.'. $this->configs[$this->id_config]['format'];
	}
	function getDirFile($file_id = false)
	{
		return 'up' . '/' . $this->config . '/' . $this->id_record . '/' . ($file_id ? $file_id : $this->id_file) . '/';
	}
	function makeDirs()
	{
		$dir = $this->getDirFile();

		if(!is_dir('up/' . $this->config))
		{
			if(!mkdir('up/' . $this->config))
			{
				$this->error = "Erreur: creation du repertoire config";
				return false;
			}
		}
		if(!is_dir('up/' . $this->config . '/' . $this->id_record))
		{
			if(!mkdir('up/' . $this->config . '/' . $this->id_record))
			{
				$this->error = "Erreur: creation du repertoire id_record";
				return false;
			}
		}
		if(!is_dir($dir))
		{
			if(!mkdir($dir))
			{
				$this->error = "Erreur: creation du repertoire id_file";
				return false;
			}
		}
		return $dir;
	}
	function buildImg($imgBuild,$img,$width, $height)
	{
		if($imgBuild['x'])
		{
			$target_width  = $imgBuild['x'];
		}
		else
		{
			$target_width  = ($imgBuild['y'] / $height) * $width;
		}
		if($imgBuild['y'])
		{
			$target_height = $imgBuild['y'];
		}
		else
		{
			$target_height = ($imgBuild['x'] / $width) * $height;
		}

		$target_ratio = $target_width / $target_height;
		$img_ratio    = $width / $height;
		
		if ($target_ratio > $img_ratio) {
			$new_height = $target_height;
			$new_width  = $img_ratio * $target_height;
		} else {
			$new_height = $target_width / $img_ratio;
			$new_width  = $target_width;
		}
		if ($new_height > $target_height) {
			$new_height = $target_height;
		}
		if ($new_width > $target_width) {
			$new_height = $target_width;
		}
		if($imgBuild['color']) 			// MODE RECADRAGE SUR FOND COLORE
		{
			$new_img = ImageCreateTrueColor($target_width, $target_height);
			$color = imagecolorallocate($new_img, $imgBuild['color'][0], $imgBuild['color'][1], $imgBuild['color'][2]);
			
			
			if (!@imagefilledrectangle($new_img, 0, 0, $target_width-1, $target_height-1,$color)) {	// Fill the image white
				$return = array(false,"ERROR:Could not fill new image");
			}
			else
			if (!@imagecopyresampled($new_img, $img, ($target_width-$new_width)/2, ($target_height-$new_height)/2, 0, 0, $new_width, $new_height, $width, $height)) {
				$return = array(false,"ERROR:Could not resize image");
			}else{
				$return = array(true,$new_img);
			}
		}
		else 							// MODE CADRER AU MAX POSSIBLE DE HAUTEUR ET LARGEUR
		{
			$new_img = ImageCreateTrueColor($new_width, $new_height);
			
			if (!@imagefilledrectangle($new_img, 0, 0, $new_width-1, $new_height-1,$color)) {	// Fill the image white
				$return = array(false,"ERROR:Could not fill new image");
			}
			else
			if (!@imagecopyresampled($new_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height)) {
				$return = array(false,"ERROR:Could not resize image");
			}else{
				$return = array(true,$new_img);
			}
			
		}
		if($return[0]) // SI PAS D'ERREURS
		{
			$photoFrame = $new_img;
			$return = array(true,$photoFrame);
		}
		
		return $return;
	}
}

?>