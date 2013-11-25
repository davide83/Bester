<?php
/* deletesite.php - BetSter project (22.05.06)
 * Copyright (C) 2006  Harald Kröll
 * 
 * This program is free software; you can redistribute it and/or modify it 
 * under the terms of the GNU General Public License as published by the Free 
 * Software Foundation; either version 2 of the License, or (at your option) 
 * any later version.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for 
 * more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, write to the Free Software Foundation, Inc., 
 * 51 Franklin St, Fifth Floor, Boston, MA 02110, USA
 */


// includes
require_once("configuration.php");
require_once("functions.inc.php");
require_once("class/User.class.php");
require_once("class/Category.class.php");
require_once("class/Bet.class.php");
require_once("class/Logger.class.php");
require_once("class/Transaction.class.php");
require_once("class/DbMapper.class.php");
require_once("class/Site.class.php");
require_once("class/Session.class.php");
require_once("class/phpmailer.class.php");


// objects
$session = new Session;
$logger = new Logger;
$db_mapper = new DbMapper;
$user = new User("", "", "", "", "", "", "");

if ($session->getState()){
	$username = $session->getUsername();
	$user = $db_mapper->getUser($username);
}

// 1. header
require_once ('modules/header.php');

// 2. catmenu
require_once ('modules/catmenu.php');

// 4. title in the mainlayer
require_once ('modules/title.php');

// 5. mainhtml

if (($session->getState()) && 
	($user->getStatus() == "administrator")) {
	
	// ask
	if (isset($_GET['sid']) && ($_POST['delete'] == "") && ($_POST['cancel'] == "")){
		if (is_numeric(htmlspecialchars($_GET['sid']))){
			$sid = $_GET['sid'];
			$site = $db_mapper->getSite($sid);
			$mainhtml = file_get_contents("tpl/deletesite.inc");
			$mainhtml = replace("MainTitle", $site->GetTitle(), $mainhtml);
			$mainhtml = replace("Text", $site->getText(), $mainhtml);
			$mainhtml = replace("Date", $site->getDate(), $mainhtml);
			$mainhtml = replace("ButtonDelete", _DELETE, $mainhtml);
			$mainhtml = replace("ButtonCancel", _CANCEL, $mainhtml);
			$mainhtml = replace("Message1", _SURE_DEL_SITE, $mainhtml);
		}
		else {
			header("Location:index.php");
		}
	}
	
	// delete TODO: lock F5
	elseif (($_POST['delete'] == _DELETE) && (isset($_GET['sid']))){
		if (is_numeric(htmlspecialchars($_GET['sid']))){
			$db_mapper->deleteSite($_GET['sid']);
			$logger->writeLog($user->getUsername(), _DEL_SITE_ID.$_GET['sid']);
			$mainhtml = file_get_contents("tpl/deletesite1.inc");
			$mainhtml = replace("MainTitle", _DEL_SITE, $mainhtml);
			$mainhtml = replace("Message1", _DEL_SITE_SUC, $mainhtml);
			$mainhtml = replace("Back", _BACK_START, $mainhtml);
		}
		else {
			header("Location:index.php");
		}
	}
}
else {
	header("Location:index.php");
}

// 6. usermenu
require_once ('modules/usermenu.php');

// 3. menu
require_once ('modules/menu.php');

// 7. adminmenu
require_once ('modules/adminmenu.php');

// 8. footer
require_once ('modules/footer.php');

// spit it out
print $header."\n".
      $catmenu."\n".
      $menu."\n".
      $xmaintitle."\n".
      $mainhtml."\n".
      $usermenu."\n".
      $adminmenu."\n".
      $footer;
?>
