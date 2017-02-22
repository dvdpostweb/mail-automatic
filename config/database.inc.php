<?php


//----------------------------
// DATABASE CONFIGURATION
//----------------------------
$db_config = array(


//	'development' 	=> array(
//			'type' 			=> 'mysql',
//			'host' 			=> '127.0.0.1',
//			'port'			=> 3306,
//			'database' 	=> 'dvdpost_test',
//			'user' 			=> 'root',
//			'password' 	=> 'root'
//	),
//	'test' 	=> array(
//			'type' 			=> 'mysql',
//			'host' 			=> '192.168.100.204',
//			'port'			=> 3306,
//			'database' 	=> 'dvdpost_test',
//			'user' 			=> 'root',
//			'password' 	=> '(:melissa:)'
//	),
	'production' 		=> array(
			'type' 			=> 'mysql',
			'host' 			=> '192.168.100.204',
			'port'			=> 0,
			'database' 	=> 'dvdpost_be_prod',
			'user' 			=> 'webuser',
			'password' 	=> '3gallfir-'
	)
);


?>
