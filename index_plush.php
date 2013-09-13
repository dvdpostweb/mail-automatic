<?php
echo "start\n";
include('./includes/classes/schedule_plush.php');
include('./includes/classes/email_process_plush.php');
include('./includes/classes/message_process_plush.php');
include('./includes/classes/customer_process_plush.php');
include('./includes/classes/database.php');
include("./includes/classes/class.phpmailer.php");
include('./includes/classes/mime.php');
include('./includes/classes/script.php');
include('./config/database_plush.inc.php');
$main = new Schedule($db_config, $argv);
$main->execute();
?>