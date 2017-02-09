<?php


//----------------------------
// DATABASE CONFIGURATION
//----------------------------
$db_config = array(
	


	'test' 	=> array(
			'type' 			=> 'mysql',
			'host' 			=> '192.168.100.204',
			'port'			=> 3306,
			'database' 	=> 'plush_staging',
			'user' 			=> 'root',
			'password' 	=> '(:melissa:)'
	),
	'production' 		=> array(
			'type' 			=> 'mysql',
			'host' 			=> '192.168.100.204',
			'port'			=> 0,
			'database' 	=> 'plush_production',
			'user' 			=> 'webuser',
			'password' 	=> '3gallfir-'
	)
);


?>
