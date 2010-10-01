<?php


//----------------------------
// DATABASE CONFIGURATION
//----------------------------
$db_config = array(
	


	'test' 	=> array(
			'type' 			=> 'mysql',
			'host' 			=> '192.168.100.204',
			'port'			=> 3306,
			'database' 	=> 'dvdpost_test',
			'user' 			=> 'root',
			'password' 	=> '(:melissa:)'
	),
	'production' 		=> array(
			'type' 			=> 'mysql',
			'host' 			=> '192.168.100.204',
			'port'			=> 0,
			'database' 	=> 'dvdpost_test',
			'user' 			=> 'webuser',
			'password' 	=> '3gallfir-'
	)
);


?>
