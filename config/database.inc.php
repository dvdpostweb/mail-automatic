<?php


//----------------------------
// DATABASE CONFIGURATION
//----------------------------
$db_config = array(
	


	'test' 	=> array(
			'type' 			=> 'mysql',
			'host' 			=> 'binga',
			'port'			=> 3306,
			'database' 	=> 'dvdpost_be_prod',
			'user' 			=> 'test_webuser',
			'password' 	=> 'd0mosol0'
	),
	'production' 		=> array(
			'type' 			=> 'mysql',
			'host' 			=> 'matadi',
			'port'			=> 0,
			'database' 	=> 'dvdpost_be_prod',
			'user' 			=> 'root',
			'password' 	=> '(:melissa:)'
	)
);


?>
