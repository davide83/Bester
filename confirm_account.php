<?php
/* confirm_account.php - BetSter project (22.05.06)
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
$mainhtml = file_get_contents("tpl/confirm_account.inc");
$mainhtml = replace("MainTitle", _CONFIRM_ACCOUNT_TITLE, $mainhtml);
$mainhtml = replace("Back", _BACK_HOME, $mainhtml);

if (($session->getState() == 0) && 
	isset($_GET['cn']) && 
	isset($_GET['un'])) {
	
	$conf_num = $_GET['cn'];
	$xusername = $_GET['un'];
	// verify the conf_num in the database
	// activate the account or delete it
	$xuser = $db_mapper->getUser($xusername);
	$conf_num_db = $db_mapper->getUserConfNum($xusername);
	if ($conf_num_db == $conf_num) {
		$xuser->activate();
		$logger->writeLog($xusername, _USER_ACTIVATED);
		$mainhtml = replace("Message1", _CONFIRM_ACCOUNT_SUC, $mainhtml);
	}
	else {
		$mainhtml = replace("Message1", _CONFIRM_ACCOUNT_FAIL, $mainhtml);
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
