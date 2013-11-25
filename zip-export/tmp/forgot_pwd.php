<?php
/* forgot_pwd.php - BetSter project (22.05.06)
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
$mainhtml = file_get_contents("tpl/forgot_pwd.inc");

if (($session->getState() == 0) &&
		isset($_POST['ausername']) &&
		isset($_POST['email'])) {
	// Daten prüfen
	$xusername = $_POST['ausername'];
	$email = $_POST['email'];

	if (empty($xusername) ||  empty($email)) {
		$mainhtml = replace("Message1", _FIELDS_INC, $mainhtml);
	}
	else {
		if (!checkEmail($email)){
			$mainhtml = replace("Message1", _EMAIL_INVALID, $mainhtml);
		}
		elseif ($db_mapper->checkIfEmailInDb($email)){
			$mainhtml = file_get_contents("tpl/forgot_pwd2.inc");
			$xuser = $db_mapper->getUser($xusername);

			// ok
			if ($xuser->getEmail() == $email){
				$conf_num = $db_mapper->getUserConfNum($xusername);
				$conf_link = $bstConfig_url."forgot_pwd.php?cn=".$conf_num."&un=".$xusername;
				Mailer(_FORGOT_PWD_SUB, _FORGOT_PWD_MSG."\n\n".$conf_link."\n", $email);	
				$logger->writeLog($xusername, _FORGOT_PWD);
				$mainhtml = replace("Message1", _FORGOT_PWD_MAIL_SENT, $mainhtml);       
				$mainhtml = replace("Back", _BACK_HOME, $mainhtml); 
			}
		}
	}
}

// in case of a confirmation from a link sent in a mail
if (($session->getState() == 0) && isset($_GET['cn']) && isset($_GET['un'])) {
	$mainhtml = file_get_contents("tpl/forgot_pwd2.inc");
	$mainhtml = replace("Message1", _FORGOT_PWD_MAIL2_SENT, $mainhtml);       
	$mainhtml = replace("Back", _BACK_HOME, $mainhtml); 
	$conf_num = $_GET['cn'];
	$xusername = $_GET['un'];

	$xuser = $db_mapper->getUser($xusername);
	$email = $xuser->getEmail();
	$conf_num_db = $db_mapper->getUserConfNum($xusername);
	if ($conf_num_db == $conf_num) {
		$rand_pwd = rand(10000,99999);
		$db_mapper->updateUserPwd($rand_pwd, $xuser->getUsername());
		Mailer(_NEW_PWD_SUB, _NEW_PWD_MSG."\n\n".$rand_pwd, $email);
	}
	else {
		$mainhtml = replace("Message1", _CONFIRM_ACCOUNT_FAIL, $mainhtml);
	}
}

if ($session->getState()){
	header("Location:index.php");
}


// replace the main title
$mainhtml = replace("MainTitle", _FORGOT_PWD_TITLE, $mainhtml);
$mainhtml = replace("Message1", _FILL_IN_PWD, $mainhtml);
$mainhtml = replace("Username", _USERNAME, $mainhtml);
$mainhtml = replace("Email", _EMAIL_INPUT, $mainhtml);

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
