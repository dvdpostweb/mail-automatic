<?php


//----------------------------
// DATABASE CONFIGURATION
//----------------------------
$db_config = array(


//	'development' 	=> array(
//			'type' 			=> 'mysql',
//			'host' 			=> '127.0.0.1',
//			'port'			=> 3306,
//			'database' 	=> 'plush_development',
//			'user' 			=> 'root',
//			'password' 	=> 'root'
//	),
//	'test' 	=> array(
//			'type' 			=> 'mysql',
//			'host' 			=> '192.168.100.204',
//			'port'			=> 3306,
//			'database' 	=> 'plush_staging',
//			'user' 			=> 'root',
//			'password' 	=> '(:melissa:)'
//	),
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
