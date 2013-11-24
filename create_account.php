<?php
/* create_account.php - BetSter project (22.05.06)
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
require_once("class/Mailer.class.php");
require_once("class/Transaction.class.php");
require_once("class/DbMapper.class.php");
require_once("class/Site.class.php");
require_once("class/Session.class.php");
require_once("class/phpmailer.class.php");


// objects
$session = new Session;
$logger = new Logger;
$mailer = new Mailer;
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
$mainhtml = file_get_contents("tpl/create_account.inc");

// in case of a new account submit
//--------------------------------------------------------------------
if (($session->getState() == 0) &&
		isset($_POST['ausername']) &&
		isset($_POST['password1']) &&
		isset($_POST['email']) &&
		isset($_POST['firstname']) &&
		isset($_POST['lastname'])){
			
	$xusername = htmlspecialchars($_POST['ausername']);
	$email = htmlspecialchars($_POST['email']);
	$password1 = htmlspecialchars($_POST['password1']);
	$password2 = htmlspecialchars($_POST['password2']);
	$firstname = htmlspecialchars($_POST['firstname']);
	$lastname = htmlspecialchars($_POST['lastname']);

	if ((empty($xusername) || 
		empty($email) || 
		empty($password1) ||
		empty($password2) ||
		empty($firstname) || 
		empty($lastname)) ||
		((strlen($password1) && strlen($password2)) < 5) || 
		(strlen($xusername) > 8)) {
		$mainhtml = replace("Message1", _FIELDS_INC, $mainhtml);
	}
	else {
		$cmp_user = $db_mapper->getUser($xusername);

		// unequal passwords
		if (!($password1 == $password2)){
			$mainhtml = replace("Message1", _NOT_EQUAL_PWD, $mainhtml); 
		}

		// exists already
		elseif ($cmp_user->getUsername() == $xusername){
			$mainhtml = replace("Message1", _UN_EXISTS, $mainhtml); 
		}

		// illegal names
		elseif (eregi("((root)|(daemon)|(admin)|(sync)|(shutdown)|(halt)|
			(mail)|(news)|(uucp)|(operator)|(games)|(mysql)|(httpd)|(nobody)|
			(dummy)|(www)|(cvs)|(shell)|(ftp)|(irc)|(debian)|(download)|
			(betster)|(anarchotronik)|(betmaster))",
					$xusername)){
			$mainhtml = replace("Message1", _UN_INVALID, $mainhtml);
		}
		elseif (!checkEmail($email)){
			$mainhtml = replace("Message1", _EMAIL_INVALID, $mainhtml);
		}

		elseif ($db_mapper->checkIfEmailInDb($email)){
			$mainhtml = replace("Message1", _EMAIL_EXISTS, $mainhtml);
		}


		// OK
		else {
			$mainhtml = file_get_contents("tpl/create_account2.inc");
			$conf_num = rand(10000,99999);
			$db_mapper->insertUser($xusername, $email, $firstname, $lastname, $password1, "inactive", $conf_num);         
			$db_mapper->setBalance(mysql_insert_id(), $bstConfig_initial_credits);
			$logger->writeLog($xusername, _NEW_ACCOUNT);
			$logger->writeLog($xusername, _NEW_ACCOUNT_BALANCE_SET.$bstConfig_initial_credits);
			$mainhtml = replace("Message1", _INSERTED, $mainhtml);       
			$mainhtml = replace("Back", _BACK_HOME, $mainhtml); 			
			// TODO: captcha
			// Mail with confnum
			$conf_link = $bstConfig_url."confirm_account.php?cn=".$conf_num."&un=".$xusername;
			$mailer->send_email(_ACCOUNT_REG_SUB, _ACCOUNT_REG_MSG.$conf_link."\n\n", $email);
		}
	}
}

if (($session->getState() == 1) &&
		isset($_POST['ausername']) &&
		isset($_POST['password1']) &&
		isset($_POST['email']) &&
		isset($_POST['firstname']) &&
		isset($_POST['lastname'])){
	$mainhtml = replace("Message1", _ALLREADY_LOGGED_IN, $mainhtml);
}


// replace the main title
$mainhtml = replace("MainTitle", _CREATE_ACCOUNT_TITLE, $mainhtml);
$mainhtml = replace("Message1", _FILL_IN, $mainhtml);
$mainhtml = replace("Username", _USERNAME_INPUT, $mainhtml);
$mainhtml = replace("Email", _EMAIL_INPUT, $mainhtml);
$mainhtml = replace("Firstname", _FIRST_NAME_INPUT, $mainhtml);
$mainhtml = replace("Lastname", _LAST_NAME_INPUT, $mainhtml);
$mainhtml = replace("Password1", _PASSWORD_INPUT, $mainhtml);
$mainhtml = replace("Password2", _PASSWORD_REPEAT, $mainhtml);

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
