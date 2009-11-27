<?php
include('./includes/classes/automatic.php');
include('./includes/classes/database.php');
include('./includes/classes/email.php');
include('./includes/classes/mime.php');
include('./config/database.inc.php');

$main = new Automatic($db_config, $argv);
$main->execute();
?>