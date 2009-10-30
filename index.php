<?php
include('./lib/class/automatic.php');
include('./lib/class/database.php');
include('./lib/class/email.php');
include('./lib/class/mime.php');
include('./config/database.inc.php');

$main = new Automatic($db_config, $argv);
$main->execute();
?>