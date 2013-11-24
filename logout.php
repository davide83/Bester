<?php
require_once("functions.inc.php");
require_once("class/Logger.class.php");
require_once("class/Session.class.php");
require_once("configuration.php");
include_once ( 'language/'.$bstConfig_lang.'.php' );

$session = new Session;
$session->logout();
header("Location:index.php");
exit();
?>
