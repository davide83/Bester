<?php
/* profile_edit.php - BetSter project (22.05.06)
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

if ($session->getState()){
	$mainhtml = file_get_contents("tpl/profile_edit.inc");

	$username = $session->getUsername();
	$user = $db_mapper->getUser($username);

	// replace the main Title
	$mainhtml = replace("MainTitle", _EDIT_PROFILE, $mainhtml);

	$mainhtml = replace("UsernameTitle", _USERNAME, $mainhtml);
	$mainhtml = replace("Username", $user->getUsername(), $mainhtml);
	$mainhtml = replace("FirstnameTitle", _FIRSTNAME, $mainhtml);
	$mainhtml = replace("Firstname", $user->getFirstname(), $mainhtml);
	$mainhtml = replace("LastnameTitle", _LASTNAME, $mainhtml);
	$mainhtml = replace("Lastname", $user->getLastname(), $mainhtml);
	$mainhtml = replace("EmailTitle", _EMAIL, $mainhtml);
	$mainhtml = replace("Email", $user->getEmail(), $mainhtml);
	$mainhtml = replace("ChangeEmail", _EDIT, $mainhtml);
	$mainhtml = replace("BalanceTitle", _BALANCE, $mainhtml);
	$mainhtml = replace("Balance", $user->getBalance(), $mainhtml);
	$mainhtml = replace("StatusTitle", _STATUS, $mainhtml);
	$mainhtml = replace("ChangePwd", _CHANGE_PWD, $mainhtml);
	$mainhtml = replace("OldPwd", _OLD_PWD, $mainhtml);
	$mainhtml = replace("NewPwd", _NEW_PWD, $mainhtml);
	$mainhtml = replace("NewPwdRep", _NEW_PWD_REP, $mainhtml);
	$mainhtml = replace("Status", $user->getStatus(), $mainhtml);

	$mainhtml = replace("ButtonInsertName", "insert1", $mainhtml);
	$mainhtml = replace("ButtonInsertValue", _EDIT, $mainhtml);
	$mainhtml = replace("ButtonDeleteValue", _DELETE, $mainhtml);

	// print $db_mapper->checkIfUserInDB($session->getUsername(), "baidl");
	if (htmlspecialchars($_POST['insert1']) == _EDIT){
		$password_old = $_POST['pwd_old'];
		$password1 = $_POST['pwd1'];
		$password2 = $_POST['pwd2'];
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$email = $_POST['email'];
		if (($password_old != "") &&
				($db_mapper->checkIfUserInDB($session->getUsername(), $password_old)) &&
				($firstname != "") &&
				($lastname != "")) {
			// the user doesn't want to hava a new password
			if (($password1 == "") &&
				($password2 == "")){
				// update the rest of the fields
				$message = "correct";
				$password = $password_old;
			}

			// the user inserted the new password in only one field
			elseif (($password1 != "") ||
					($password2 != "")){
				//
				$message = "pwd_missing";
			}
			// the user want's to have a new password
			elseif (($password1 != "") &&
					($password2 != "")){
				if ($password1 != $password2){
					$message = "pwd_diff";
				}
				else {

					if (!(strlen($password) >= 5)){
						$message = "pwd_short";
					}
					else {
						$message = "correct";
					} 
					// insert new password

				}
			}
		}
		else {
			$message = "incomplete";
		} 
		switch ($message){
			case "pwd_diff":
				$mainhtml = replace("Message1", _PWD_DIFF, $mainhtml);
			case "pwd_short": 
				$mainhtml = replace("Message1", _PWD_SHORT, $mainhtml);
			case "pwd_missing": 
				$mainhtml = replace("Message1", _PWD_MISSING, $mainhtml);
			case "incomplete":
				$mainhtml = replace("Message1", _PWD_SHORT, $mainhtml);
			case "correct":
				$db_mapper->updateUser($session->getUsername(), $firstname, $lastname, $password1);
			$logger->writeLog($username, _PROFILE_EDITED);
			$mainhtml = file_get_contents("tpl/profile.inc");
			$mainhtml = replace("Message1", _INPUTS_CORRECT, $mainhtml);	
			$user = $db_mapper->getUser($username);

			// replace the main Title
			$mainhtml = replace("MainTitle", _YOUR_PROFILE, $mainhtml);

			$mainhtml = replace("UsernameTitle", _USERNAME, $mainhtml);
			$mainhtml = replace("Username", $user->getUsername(), $mainhtml);
			$mainhtml = replace("FirstnameTitle", _FIRSTNAME, $mainhtml);
			$mainhtml = replace("Firstname", $user->getFirstname(), $mainhtml);
			$mainhtml = replace("LastnameTitle", _LASTNAME, $mainhtml);
			$mainhtml = replace("Lastname", $user->getLastname(), $mainhtml);
			$mainhtml = replace("EmailTitle", _EMAIL, $mainhtml);
			$mainhtml = replace("Email", $user->getEmail(), $mainhtml);
			$mainhtml = replace("ChangeEmail", _EDIT, $mainhtml);
			$mainhtml = replace("BalanceTitle", _BALANCE, $mainhtml);
			$mainhtml = replace("Balance", $user->getBalance(), $mainhtml);
			$mainhtml = replace("StatusTitle", _STATUS, $mainhtml);
			$mainhtml = replace("Status", $user->getStatus(), $mainhtml);
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
