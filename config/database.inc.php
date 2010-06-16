<?php


//----------------------------
// DATABASE CONFIGURATION
//----------------------------
$db_config = array(
	


	'test' 	=> array(
			'type' 			=> 'mysql',
			'host' 			=> 'matadi',
			'port'			=> 3306,
			'database' 	=> 'dvdpost_test',
			'user' 			=> 'webuser',
			'password' 	=> '3gallfir-'
	),
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
