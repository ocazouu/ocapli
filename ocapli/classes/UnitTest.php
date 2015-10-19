<?php

Class UnitTestTool
{

	private $last_time;
	private $test_methods = [];
	private $unit_model = 'unit_test';

	function __construct()
	{
		return $this;
		// $title = strtr($title,array("->"=>"→"));
	}

	function run_tests()
	{
		if($this->sql_load_provision())
		{
			$test_result = Array("done"=>0,"fail"=>0);
			$start_time = microtime(true);

			foreach ($this->get_test_methods() as $value)
			{
				echo '<div class="test_run">';
				$this->start_test($value);
				$return_test = $this->$value();

				if($return_test["is_true"])
				{
					$test_result["done"] += 1;
					echo '<b class="done">Done.</b>';
				}
				else
				{
					$test_result["fail"] += 1;
					echo '<b class="fail">Fail.</b>';
				}

				echo '<div class="clear"></div>';

				echo $this->show_info_return($return_test["final_return"]);
				
				echo '</div>';
			}


			echo "<div class='test_result " . ($test_result["fail"] > 0 ? "fail" : "done") . "'>" . 
				$this->ellapsed_time($start_time) . 
				print_r($test_result,1) . 
			"</div>";

			$this->sql_unload_provision();
		}
	}

	function ellapsed_time($start_time)
	{
		return "<div class=ellapsed><i>Total ellapsed time: " . (microtime(true) - $start_time) . "</i></div>";
	}
	function start_test($title)
	{
		$title = ucfirst(strtr($title,array("run_test_"=>"","_"=>" ")));
		$this->last_time = microtime(true);

		echo "<hr />";
		echo "<h2>► $title</h2>";
	}

	function get_test_methods()
	{
		$test_methods = array();

		foreach (get_class_methods($this) as $value)
		{
			if(strpos($value, 'run_test_') !== false)
			{
				$test_methods[] = $value;
			}
		}
		return $test_methods;
	}

	function show_info_return($records)
	{
		$return = '';

		$return .= '<div class="test_infos">';

		if(is_array($records))
		{
			$result = count($records);
		}
		else
		{
			$result = $records;
		}

		$return .= '<h4>result(s): (' . gettype($records) . ') ' . $result . '</h4>';

		if(is_array($records))
		{
			$return .= '<div class="records">';
			foreach($records as $record)
			{
				$return .= "<p class='li'><b>•</b>&nbsp; " . print_r($record, 1) . "</p>";
			}
			$return .= '</div>';
		}

		$return .= $this->ellapsed_time($this->last_time);
		$return .= '</div>';

		return $return;
	}

	function sql_load_provision()
	{
		$provision = DbTool::get_requete("DROP TABLE IF EXISTS `unit_test`");
		$provision_b = DbTool::get_requete("DROP TABLE IF EXISTS `conf_lang_b`");

		if(!$provision || !$provision_b)
		{
		  __("Erreur lors de la provision.");
		}
		else
		{

		  $provision = DbTool::get_requete("
		  CREATE TABLE IF NOT EXISTS `unit_test` (
		    `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
		    `etat` tinyint(1) DEFAULT NULL,
		    `config` varchar(20) NOT NULL,
		    `id_config` mediumint(8) unsigned NOT NULL,
		    `id_record` mediumint(8) unsigned NOT NULL,
		    `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
		    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		    `ordre` tinyint(3) unsigned NOT NULL,
		    `name` varchar(20) NOT NULL,
		    `version` smallint(5) unsigned NOT NULL,
		    `description` text NOT NULL,
		    PRIMARY KEY (`id`)
		  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=30 ;
		  ");

		  $provision_b = DbTool::get_requete("
		  	CREATE TABLE IF NOT EXISTS `conf_lang_b` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `string_id` varchar(255) NOT NULL,
			  `fr` varchar(255) NOT NULL,
			  `eng` varchar(255) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=43 ;
		  ");


		  if(!$provision || !$provision_b)
		  {
		    __("Erreur lors de la provision.");
		  }
		  else
		  {

		    $provision = DbTool::get_requete("
			    INSERT INTO `unit_test` (`id`, `etat`, `config`, `id_config`, `id_record`, `created_at`, `updated_at`, `ordre`, `name`, `version`, `description`) VALUES
			    (7, 1, 'home', 0, 0, '2015-05-06 00:16:40', '2015-06-14 15:41:39', 0, '1F1C13', 8, ''),
			    (8, 1, 'home', 2, 0, '2015-05-06 00:17:14', '2015-06-14 15:38:45', 0, 'Illustrated paper 1', 8, ''),
			    (9, 1, 'home', 0, 0, '2015-06-13 13:22:11', '2015-06-14 15:41:32', 0, '66B18B', 12, ''),
			    (10, 1, 'home', 0, 0, '2015-06-13 13:23:04', '2015-06-14 15:41:28', 0, 'D71C20', 11, ''),
			    (11, 1, 'home', 0, 0, '2015-06-13 13:23:35', '2015-06-21 10:23:25', 0, 'F0D329', 8, ''),
			    (12, 1, 'home', 0, 0, '2015-06-13 13:23:59', '2015-06-14 15:41:11', 0, '9CC2DC', 8, ''),
			    (13, 1, 'home', 1, 0, '2015-06-13 13:31:36', '2015-06-14 15:38:30', 0, 'kraft cover 2', 7, ''),
			    (14, 1, 'home', 2, 0, '2015-06-13 13:38:59', '2015-06-14 15:38:40', 0, 'Illustrated paper 2', 7, ''),
			    (15, 1, 'home', 3, 0, '2015-06-13 13:40:51', '2015-06-14 15:39:11', 0, 'White kraft paper', 3, ''),
			    (17, 1, 'home', 3, 0, '2015-06-13 13:41:39', '2015-06-14 15:39:19', 0, 'White kraft paper 2', 4, ''),
			    (18, 1, 'home', 4, 0, '2015-06-13 13:45:32', '2015-06-13 14:05:12', 0, 'Demi page unique', 2, ''),
			    (22, 1, 'home', 5, 0, '2015-06-13 13:58:13', '2015-06-14 15:40:38', 0, 'All types 1', 4, ''),
			    (23, 1, 'home', 5, 0, '2015-06-13 14:03:28', '2015-06-14 15:40:33', 0, 'All types 2', 3, ''),
			    (24, 1, 'home', 5, 0, '2015-06-13 19:52:46', '2015-06-14 15:40:25', 0, 'All types 3', 3, ''),
			    (25, 1, 'home', 5, 0, '2015-06-13 19:52:59', '2015-06-14 15:40:19', 0, 'All types 4', 3, ''),
			    (26, 1, 'home', 5, 0, '2015-06-13 21:10:00', '2015-06-14 15:40:07', 0, 'All types 5', 3, ''),
			    (27, 1, 'home', 5, 0, '2015-06-13 21:10:36', '2015-06-14 15:40:01', 0, 'All types 6', 3, ''),
			    (28, 0, 'home', 0, 2, '2015-06-13 23:34:23', '2015-06-13 23:34:23', 0, 'Fichier vide', 0, ''),
			    (29, 0, 'home', 0, 1, '2015-06-14 16:36:16', '2015-06-14 16:36:16', 0, 'Fichier vide', 0, '');
		    ");

		    $provision_b = DbTool::get_requete("
			  INSERT INTO `conf_lang_b` (`id`, `string_id`, `fr`, `eng`) VALUES
				(1, '2721819029', '5 couleurs de couverture disponibles', 'A traduire'),
				(2, '2757073943', 'Carnet - Le paon', 'A traduire'),
				(3, '1701006645', 'Choisissez votre carnet', 'A traduire'),
				(4, '3436595788', '{x} carnet{s} disponible{s}', 'A traduire'),
				(5, '5178921', 'Descriptif', 'A traduire'),
				(6, '1803855849', 'Reliure cousue', 'A traduire'),
				(7, '2011158142', 'Couverture peinte à la main', 'A traduire'),
				(8, '1666580746', '5 coloris disponibles', 'A traduire'),
				(9, '1906771926', '32 Pages', 'A traduire'),
				(10, '398403877', 'Assemblage de papiers sélectionnés', 'A traduire'),
				(11, '1160918141', 'Série limitée, exemplaire unique', 'A traduire'),
				(12, '253398364', 'Fabriqué en France', 'A traduire'),
				(13, '1042080765', '({x} carnet{s} sélectionné{s})', 'A traduire'),
				(14, '1882409409', 'Ajouter au panier', 'A traduire'),
				(15, '2259804768', 'Retirer du panier', 'A traduire'),
				(16, '3912551174', 'Façonnier de carnet unique', 'A traduire'),
				(17, '1874261005', 'Le carnet', 'A traduire'),
				(18, '772153791', 'Atelier', 'A traduire'),
				(19, '2297474782', 'Votre panier', 'A traduire'),
				(20, '2212487076', 'Contact', 'A traduire'),
				(21, '2169603222', 'Frais de livraisons', 'A traduire'),
				(22, '638223618', 'cgv', 'A traduire'),
				(23, '2815816322', 'frais de livraisons', 'A traduire'),
				(24, '3579231528', 'mode de paiement', 'A traduire'),
				(25, '1453065497', 'points de vente', 'A traduire'),
				(26, '3261711957', 'Mode de paiement', 'A traduire'),
				(27, '508019682', 'Cgv', 'A traduire'),
				(28, '1411584853', 'Nom', 'A traduire'),
				(29, '2808390301', 'E-mail', 'A traduire'),
				(30, '2030045667', 'Message', 'A traduire'),
				(31, '1373095325', 'Envoyer', 'A traduire'),
				(32, '3291370283', 'Enregistrez vos informations de livraisons', 'A traduire'),
				(33, '4121612832', 'Prénom', 'A traduire'),
				(34, '3008055337', 'Société', 'A traduire'),
				(35, '3198193243', 'Téléphone', 'A traduire'),
				(36, '216150410', 'Adresse', 'A traduire'),
				(37, '1344832290', 'Code postal', 'A traduire'),
				(38, '2494403472', 'Pays', 'A traduire'),
				(39, '3326319100', 'Paiement sécurisé via PAYPAL', 'A traduire'),
				(40, '2445908291', 'J''ai lu et j accepte les conditions générales de vente', 'A traduire'),
				(41, '584001627', 'valider', 'A traduire'),
				(42, '3730389895', 'Points de vente', 'A traduire');
		    ");


		    if(!$provision || !$provision_b)
		    {
		      __("Erreur lors de la provision.");
		    }
		    else
		    {
		    	return true;
		    }
		  }
		}

		return false;
	}
	function sql_unload_provision()
	{
		$provision = DbTool::get_requete("DROP TABLE IF EXISTS `unit_test`");
		$provision = DbTool::get_requete("DROP TABLE IF EXISTS `conf_lang_b`");
	}
}


?>