<?php
include('./includes/classes/schedule.php');
include('./includes/classes/email_process.php');
include('./includes/classes/database.php');
include("./includes/classes/class.phpmailer.php");
include('./includes/classes/mime.php');
include('./includes/classes/script.php');
include('./config/database.inc.php');

$main = new Schedule($db_config, $argv);
$main->execute();
?>