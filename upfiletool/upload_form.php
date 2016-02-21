<?php

if(!is_admin())
{
	echo 'Erreur: Session non valide.';
	die;
}

Cache::set_instance();
MySQL::set_instance();

if(!MySQL::is_connect())
{
	$include = "inc/page_mysql_error.php";
	$website_errors = true;
}

new FastReqInstance($fast_req_config);

$config    = isset($_GET['config'])           ? $_GET['config']      :  false;
$id_file   = (int)( isset($_GET['identfile']) && $_GET['identfile'] != 'false' ) ? $_GET['identfile']   :  0;
$id_config = (int)isset($_GET['identconfig']) ? $_GET['identconfig'] :  0;
$id_record = (int)isset($_GET['identrecord']) ? $_GET['identrecord'] :  0;

if($_POST && $_POST['id_file'])
{
	$id_file = $_POST['id_file'];
}

$upfiletool = new Files($config,$id_config,$id_file,$id_record);

if(!$upfiletool->config_find)
{
	echo 'Erreur: config non valide.';
	die;
}
else if(!$id_file)
{
	$id_file = $upfiletool->addFile();
}

$upfiletool->id_file = $id_file;
$multiple            = $upfiletool->configs[$upfiletool->id_config]['multiple'];
$push_to_obj_return  = [];

if($_POST)
{
	switch ($_POST['action'])
	{
		case 'post_file':
			$upfiletool->addORreplaceFile($_POST);
			if($_FILES["file"]["tmp_name"])
			{
				$upfiletool->upFile();
			}
			$push_to_obj_return = array(
				'render_list_view'  => $multiple == 'multiple' ? $upfiletool->renderListView() : false,
			);
		break;
		case 'add_file':
			$upfiletool->addFile();
			$push_to_obj_return = array(
				'render_list_view'  => $upfiletool->renderListView()
			);
		break;
		case 'remove_file':
			$upfiletool->removeFile();
			$upfiletool->id_file = 0;

			$push_to_obj_return = array(
				'render_list_view'  => $upfiletool->renderListView(),
				'render_edit_view'  => $upfiletool->renderEditView()
			);
		break;
	}
}

if($upfiletool->id_file && $upfiletool->id_file != 'NULL' && $upfiletool->id_file != 'undefined')
{
	$file = $upfiletool->getFile();
}
else
{
	$file = false;
}

$obj_return = array(
	'config'      => $upfiletool->config,
	'identrecord' => $upfiletool->id_record,
	'method'      => 'iframeComplete'
);
foreach ($push_to_obj_return as $key => $value)
{
	$obj_return[$key] = $value;
}
$obj_return = stripslashes(json_encode($obj_return));

?><!DOCTYPE html>
<html>
	<script type="text/javascript">
		var INDEX        = '<?=INDEX?>';
		var INDEX_GLOBAL = '<?=INDEX_GLOBAL?>';
		var LANG         = '<?=LANG?>';
		var set_dom      = false;
		var domready_for_index;
	</script>
	<link rel="stylesheet" type="text/css" href="<?=INDEX?>css/fcss.php?version=<?=VERSION_CSS?>&version_sprite=<?=VERSION_SPRITE?>&lang=<?=LANG?>" />
	<script type="text/javascript" src="<?=INDEX?>js/fjs.php?version=<?=VERSION_JS?>"></script>
	<script type="text/javascript" src="<?=INDEX?>ocapli/js/files.js"></script>
	<style>
		body,html{overflow:hidden;border:none;background:#FFF}
		#menu_detail div{
			margin:5px 0 0 0;
		}
	</style>
<head>
</head>
<body>
<div id="document">
<div class="tx_11">
<?php
if($file)
{
	$path_file = $upfiletool->getPathFile($file);
	if(is_file($path_file))
	{
		$filesize = round(filesize($path_file) / 1024) . ' Ko';
	}
	else
	{
		$filesize = '0';
	}

?>
	<div id="tab_one" class="contain_white" style="padding:0;margin:0;width:99%;">
		<div class="form_detail" style="margin:0">
			<form method="post" action="" enctype="multipart/form-data" id="upform">
				<input type="hidden" name="action" value="" />
				<input type="hidden" name="created_at" value="<?=$file->created_at?>" />
				<input type="hidden" name="id_file" value="<?=$file->id?>" />
				<input type="hidden" name="ordre" value="<?=$file->ordre?>" />
				<input type="hidden" name="version" value="<?=$file->version?>" />
				<div class="titre"><div>Etat</div></div>
				<div class="champs ">
					<label><input name="etat" type="radio" value="1"<?=$file->etat  ? 'checked=""' : ''?>> Actif</label>
					<label><input name="etat" type="radio" value="0"<?=!$file->etat ? 'checked=""' : ''?>> Désactivé</label>
				</div>
				<div class="clear"></div>
				<div class="titre"><div>Nom du fichier</div></div>
				<div class="champs"><input type="text" name="name" value="<?=$file->name?>" /></div>
				<div class="clear"></div>
				<div class="titre"><div>Description</div></div>
				<div class="champs"><textarea type="text" name="description"><?=$file->description?></textarea></div>
				<div class="clear"></div>
				<div class="titre"><div>Infos</div></div>
				<div class="champs" style="font-size:10px">
					<div style="margin:0 0 0 5px">Créer le <?=$file->created_at?></div>
					<div style="margin:0 0 0 5px">Modifié le <?=$file->updated_at?></div>
					<div style="margin:0 0 0 5px">Taille : <?=$filesize?> / Version : <?=$file->version?></div>
				</div>
				<input type="file" accept="image/*" name="file" style="padding:20px 0 14px 1%;line-height:20px;" />
				<div class="clear"></div>
				<div style="float:left;margin:10px 0 0 0;max-width:300px">
<?php

	if($filesize != '0')
	{
		$src_file = $upfiletool->getSrcFile($file);
		echo '<span>Fichier '.$upfiletool->configs[$upfiletool->id_config]['type'].' uploadé: </span><a href="' . $src_file . '" target="_blank">Voir</a>';
		if($upfiletool->configs[$upfiletool->id_config]['type'] == 'image')
		{
			echo '<div style="margin:10px 0 0 0"><img src="' . $src_file . '" width="auto" height="auto" style="width:100%;height:auto" /></div>';
		}
	}

?>
				</div>
				<div style="float:right;margin:10px 0 0 0" id="menu_detail">
					<span><?=$upfiletool->error?></span>
<?php 
	if($multiple == 'multiple')
	{
?>
					<div><button id="add_file">Ajouter autre fichier</button></div>
					<div><button id="remove_file">Supprimer le fichier</button></div>
<?php
	}
?>
					<div><button id="post_file">Envoyer</button></div>
				</div>
				<div class="clear"></div>
			</form>
		</div>
	</div>
<?php
}
?>
</div>
</div>
<script type="text/javascript">
	var domready_for_index = (function(){
		if($('post_file')){
			var input_action = $('upform').getElement('input[name=action]');
			$('post_file').addEvent('click',function(ev){
				ev.preventDefault();
				input_action.set('value','post_file');
				$('upform').submit();
			});
		}
		if($('add_file')){
			$('add_file').addEvent('click',function(ev){
				ev.preventDefault();
				input_action.set('value','add_file');
				$('upform').submit();
			});
		}
		if($('remove_file')){
			$('remove_file').addEvent('click',function(ev){
				ev.preventDefault();
				input_action.set('value','remove_file');
				$('upform').submit();
			});
		}
		var obj_return = JSON.decode('<?=$obj_return?>');
		obj_return['framesizey'] = $('document').getScrollSize().y
		window.parent['setFilesIframeComplete'](obj_return);
	});
</script>
</body>
</html>
