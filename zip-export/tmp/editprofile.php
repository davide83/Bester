<?php
/* editprofile.php - BetSter project (22.05.06)
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

	$id = htmlspecialchars($_GET['id']);
	$ac = htmlspecialchars($_GET['ac']);
	$acd = htmlspecialchars($_GET['acd']);

	if (is_numeric($id)){
		$xuser = $db_mapper->getUserById($id);
	}

	if ($user->getStatus() == "administrator") {

		if ($ac != "") {
			$mainhtml = file_get_contents("tpl/editprofile1.inc");
			$a_menu = getTemplatePart("AdminMenu",$mainhtml);
			
			switch ($ac) {
				// unset betmaster
				case "ub":
					if ($xuser->getStatus() == "betmaster") {
						$msg = _ASK_UNSET_BM;
						$a_menu = replace("Ac0", "ub_d", $a_menu);
					}
					else {
						$msg = _USER_NO_BM;
					}
				break;
				// set betmaster
				case "sb":
					if ($xuser->getStatus() == "active") {
						$msg = _ASK_SET_BM;
						$a_menu = replace("Ac0", "sb_d", $a_menu);
					}
					else {
						$msg = _USER_INACTIVE;
					}
				break;
				// unlock user
				case "uu":
					if ($xuser->getStatus() == "locked") {
						$msg = _ASK_UNLOCK_USER;
						$a_menu = replace("Ac0", "uu_d", $a_menu);
					}
					else {
						$msg = _USER_NOT_LOCKED;
					}
				break;
				// lock user
				case "lu":
					if ($xuser->getStatus() == "active") {
						$msg = _ASK_LOCK_USER;
						$a_menu = replace("Ac0", "lu_d", $a_menu);
					}
					else {
						$msg = _USER_INACTIVE;
					}		    	
				break;
				// delete user
				case "du":
					if (($xuser->getStatus() == "active") ||
							($xuser->getStatus() == "inactive") ||
							($xuser->getStatus() == "locked")){
						$msg = _ASK_DELETE_USER;
						$a_menu = replace("Ac0", "du_d", $a_menu);
					}
					else {
						$msg = _FAIL_DELETE;
						$a_menu = replace("Ac0", "du_d", $a_menu);
					}		
				break;
				// change balance
				case "cb":
					if (($xuser->getStatus() == "active") || 
							($xuser->getStatus() == "betmaster") || 
							($xuser->getStatus() == "administrator")){
						// show form
						$mainhtml = file_get_contents("tpl/changebalance.inc");
						$mainhtml = replace("ActualBalance", _ACTUAL_BALANCE, $mainhtml);
						$mainhtml = replace("NewBalance", _NEW_BALANCE, $mainhtml);
						$mainhtml = replace("Edit", _EDIT, $mainhtml);
						$mainhtml = replace("Delete", _DELETE, $mainhtml);
						$mainhtml = replace("ActualBalanceValue", $xuser->getBalance(), $mainhtml);
						$msg = _CH_BALANCE;
						// show disabled form for confirmation
						if ($_POST['chbal'] == _EDIT){
							if (is_numeric($_POST['balance'])){
								$mainhtml = file_get_contents("tpl/changebalance2.inc");
								$mainhtml = replace("ActualBalance", _ACTUAL_BALANCE, $mainhtml);
								$mainhtml = replace("NewBalance", _NEW_BALANCE, $mainhtml);
								$mainhtml = replace("NewBalanceValue", $_POST['balance'], $mainhtml);
								$mainhtml = replace("Edit", _EDIT, $mainhtml);
								$mainhtml = replace("Delete", _DELETE, $mainhtml);
								$mainhtml = replace("ActualBalanceValue", $xuser->getBalance(), $mainhtml);
								$msg = _ASK_CH_BALANCE;
							}
							else {
								$msg = _FAIL_CH_BALANCE;
							}
						}
						// show sucessful output
						if ($_POST['chbald'] == _EDIT){ 
							if (is_numeric($_POST['balance']) && (($xuser->getStatus() == "active") || 
										($xuser->getStatus() == "betmaster") || 
										($xuser->getStatus() == "administrator"))){
								$mainhtml = file_get_contents("tpl/editprofile2.inc");
								$xuser->setBalance($_POST['balance']);
								$logger->writeLog($user->getUsername(), _BALANCE_CHANGED.$xuser->getUsername());
								$msg = _SUC_CH_BALANCE;
								$mainhtml = replace("Id", $id, $mainhtml);
								$mainhtml = replace("Back", _BACK_PROFILE, $mainhtml);
							}
							else {
								$msg = _FAIL_CH_BALANCE;
							}
						}
						// on a termination
						if (($_POST['chbal'] == _DELETE) || ($_POST['chbald'] == _DELETE)){
							header("Location:showprofile.php?id=$id");
						}
					}
					else {
						$msg = _USER_INACTIVE;
						$mainhtml = file_get_contents("tpl/editprofile1.inc");
						$mainhtml = replace("AdminMenu", "", $mainhtml);
						$mainhtml = replace("Back", _BACK_USERLIST, $mainhtml);
					}
				break;
				default:
				$msg = _FAIL_EXECUTE_OPERATION;
				$mainhtml = file_get_contents("tpl/editprofile1.inc");
				$mainhtml = replace("AdminMenu", "", $mainhtml);
				$mainhtml = replace("Back", _BACK_USERLIST, $mainhtml);
			}
			$a_menu = replace("Id", $id, $a_menu);
			$a_menu = replace("Yes", _YES, $a_menu);
			$a_menu = replace("No", _NO, $a_menu);
			$mainhtml = replace("AdminMenu", $a_menu, $mainhtml);
			$mainhtml = replace("Back", _BACK_USERLIST, $mainhtml);
		}

		// after the confirmation
		elseif ($acd != "") {
			$mainhtml = file_get_contents("tpl/editprofile2.inc");
			if (!($xuser->getStatus() == "inactive")){
				switch ($acd){
					case "ub_d":
						if ($xuser->getStatus() == "betmaster") {
							$xuser->activate();
							$logger->writeLog($user->getUsername(), _BM_UNSET_FROM.$xuser->getUsername());
							$msg = _SUC_UNSET_BM;
						}
						else {
							$msg = _USER_NO_BM;
						}
					break;
					case "sb_d":
						if ($xuser->getStatus() == "active") {
							$xuser->betmaster();
							$logger->writeLog($user->getUsername(), _BM_SET_FROM.$xuser->getUsername());
							$msg = _SUC_SET_BM;
						}
						else {
							$msg = _USER_INACTIVE;
						}
					break;
					case "uu_d":
						if ($xuser->getStatus() == "locked") {
							$xuser->activate();
							$logger->writeLog($user->getUsername(), _USER_ACTIVATED.$xuser->getUsername());
							$msg = _SUC_UNLOCK_USER;
						}
						else{
							$msg = _USER_NOT_LOCKED;
						}
					break;
					case "lu_d":
						if ($xuser->getStatus() == "active") {
							$xuser->lock();
							$logger->writeLog($user->getUsername(), _USER_LOCKED.$xuser->getUsername());
							$msg = _SUC_LOCK_USER;
						}
						else {
							$msg = _USER_INACTIVE;
						}
					break;
					case "du_d":
						if (($user->getUserId() != $xuser->getUserId()) &&
								(($xuser->getStatus() == "inactive") || 
								 ($xuser->getStatus() == "active") ||
								 ($xuser->getStatus() == "locked"))) {
							//$xuser->deactivate();
							$db_mapper->deleteUser($id);
							$logger->writeLog($user->getUsername(), _USER_DELETED.$xuser->getUsername());
							$msg = _SUC_DELETE_USER;
							$mainhtml = file_get_contents("tpl/editprofile1.inc");
							$mainhtml = replace("AdminMenu", "", $mainhtml);
							$mainhtml = replace("Back", _BACK_USERLIST, $mainhtml);
						}
						else {
							$mainhtml = file_get_contents("tpl/editprofile2.inc");
							$msg = _FAIL_DELETE_USER;
							$mainhtml = replace("Back", _BACK_PROFILE, $mainhtml);
						}
					break;
					default:
					$msg = _FAIL_EXECUTE_OPERATION;
					$mainhtml = file_get_contents("tpl/editprofile1.inc");
					$mainhtml = replace("AdminMenu", "", $mainhtml);
					$mainhtml = replace("Back", _BACK_USERLIST, $mainhtml);
				}
			}
			else {
				$msg = _FAIL_INACTIVE_USER;
			}
			$mainhtml = replace("Id", $id, $mainhtml);
			$mainhtml = replace("Back", _BACK_PROFILE, $mainhtml);
		}
		$mainhtml = replace("Message1", $msg, $mainhtml);				
		$mainhtml = replace("MainTitle", _PROFILE_EDIT_OF." ".$xuser->getUsername(), $mainhtml);
	}
	
	elseif ($user->getStatus() == "betmaster") {
		if ($ac != "") {
			$mainhtml = file_get_contents("tpl/editprofile1.inc");
			$b_menu = getTemplatePart("AdminMenu",$mainhtml);

			switch ($ac) {
				case "lu":
					if ($xuser->getStatus() == "active"){
						$msg = _ASK_LOCK_USER;
						$b_menu = replace("Ac0", "lu_d", $b_menu);
					}
					else{
						$msg = _USER_INACTIVE;
					}
				break;
				case "uu":
					if ($xuser->getStatus() == "locked"){
						$msg = _ASK_UNLOCK_USER;
						$b_menu = replace("Ac0", "uu_d", $b_menu);
					}
					else{
						$msg = _USER_NOT_LOCKED;
					}
				break;
				default:
				$msg = _FAIL_EXECUTE_OPERATION;
				$mainhtml = file_get_contents("tpl/editprofile1.inc");
				$mainhtml = replace("AdminMenu", "", $mainhtml);
				$mainhtml = replace("Back", _BACK_USERLIST, $mainhtml);
			}
			$b_menu = replace("Id", $id, $b_menu);
			$b_menu = replace("Yes", _YES, $b_menu);
			$b_menu = replace("No", _NO, $b_menu);
			$mainhtml = replace("AdminMenu", $b_menu, $mainhtml);
			$mainhtml = replace("Back", _BACK_USERLIST, $mainhtml);
		}
		// after the confirmation
		elseif ($acd != "") {
			$mainhtml = file_get_contents("tpl/editprofile2.inc");
			switch ($acd){
				case "lu_d":
					if ($xuser->getStatus() == "active"){
						$xuser->lock();
						$logger->writeLog($user->getUsername(), _USER_LOCKED.$xuser->getUsername());
						$msg = _SUC_LOCK_USER;
					}
					else {
						$msg = _USER_INACTIVE;
					}
				break;
				case "uu_d":
					if ($xuser->getStatus() == "locked"){
						$xuser->activate();
						$logger->writeLog($user->getUsername(), _USER_UNLOCKED.$xuser->getUsername());
						$msg = _SUC_UNLOCK_USER;
					}
					else {
						$msg = _USER_NOT_LOCKED;
					}
				break;
				default:
				$msg = _FAIL_EXECUTE_OPERATION;
				$mainhtml = file_get_contents("tpl/editprofile1.inc");
				$mainhtml = replace("AdminMenu", "", $mainhtml);
				$mainhtml = replace("Back", _BACK_USERLIST, $mainhtml);				
			}
			$mainhtml = replace("Id", $id, $mainhtml);
			$mainhtml = replace("Back", _BACK_PROFILE, $mainhtml);
		}
		$mainhtml = replace("Message1", $msg, $mainhtml);				
		$mainhtml = replace("MainTitle", _PROFILE_EDIT_OF." ".$xuser->getUsername(), $mainhtml);
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
