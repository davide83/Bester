<?php
$xmaintitle = file_get_contents("tpl/title.inc");
$xmaintitle = replace("Title", $bstConfig_main_title, $xmaintitle);
$xmaintitle = replace("SubTitle", $bstConfig_sub_title, $xmaintitle);
?>
