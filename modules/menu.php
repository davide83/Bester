<?php
$menu = file_get_contents("tpl/menu.inc");

/*
if (($session->getState()) && 
	($user->getStatus() == "administrator")){
	$add = getTemplatePart("AddSite", $menu);
	$menu = replace("AddSite", $add, $menu);
}
else {
	$menu = replace("AddSite", "", $menu);
}
*/

$i = 0;
$item = "";
foreach($bstConfig_menu_names as $name){
	$item .= getTemplatePart("FixItem", $menu);
	$item = replace("Name", $name, $item);
	$item = replace("Link", $bstConfig_menu_links[$i], $item);
	$i++;
}


$sites = $db_mapper->getAllSites();

$item2 = "";
foreach($sites as $site){
	$item2 .= getTemplatePart("Item", $menu);
	$item2 = replace("Id", $site->getId(), $item2);
	$item2 = replace("Name", $site->getTitle(), $item2);
	if (($session->getState() == 1) && ($user->getStatus() == "administrator")) {
		$edit = getTemplatePart("EditSite", $item2);
		$edit = replace("Id2", $site->getId(), $edit);
		$delete = getTemplatePart("DeleteSite", $item2);
		$delete = replace("Id2", $site->getId(), $delete);
		$item2 = replace("EditSite", $edit, $item2);
		$item2 = replace("DeleteSite", $delete, $item2);
	}
	else {
		$item2 = replace("EditSite", "", $item2);
		$item2 = replace("DeleteSite", "", $item2);
	}
}

$menu = replace("Item", $item.$item2, $menu);
$menu = replace("Version", $bstConfig_version, $menu);
$menu = replace("BetsterTime", date('g:i:s' ,time()), $menu);
$menu = replace("FixItem", "", $menu);

?>
