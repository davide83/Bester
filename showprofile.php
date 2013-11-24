<?php
/* showprofile.php - BetSter project (22.05.06)
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
	(($user->getStatus() == "administrator") || 
	 ($user->getStatus() == "betmaster"))){
	$mainhtml = file_get_contents("tpl/showprofile.inc");

	$id = htmlspecialchars($_GET['id']);
	$xuser = $db_mapper->getUserById($id);

	// replace the main Title
	$mainhtml = replace("MainTitle", _PROFILE_OF." ".$xuser->getUsername(), $mainhtml);
	$mainhtml = replace("Id", $xuser->getUserId(), $mainhtml);
	$mainhtml = replace("UsernameTitle", _USERNAME, $mainhtml);
	$mainhtml = replace("XUsername", $xuser->getUsername(), $mainhtml);
	$mainhtml = replace("FirstnameTitle", _FIRSTNAME, $mainhtml);
	$mainhtml = replace("Firstname", $xuser->getFirstname(), $mainhtml);
	$mainhtml = replace("LastnameTitle", _LASTNAME, $mainhtml);
	$mainhtml = replace("Lastname", $xuser->getLastname(), $mainhtml);
	$mainhtml = replace("EmailTitle", _EMAIL, $mainhtml);
	$mainhtml = replace("Email", $xuser->getEmail(), $mainhtml);
	$mainhtml = replace("BalanceTitle", _BALANCE, $mainhtml);
	$mainhtml = replace("XBalance", $xuser->getBalance(), $mainhtml);
	$mainhtml = replace("StatusTitle", _STATUS, $mainhtml);
	$mainhtml = replace("Status", $xuser->getStatus(), $mainhtml);


	if ($user->getStatus() == "administrator"){

		if ($xuser->getStatus() == "betmaster") {

			$link1 = getTemplatePart("Link",$mainhtml);
			$link1 = replace("ActionName", _UNSET_BM, $link1);
			$link1 = replace("Action", "ub", $link1);
			$link1 = replace("Id", $xuser->getUserId(), $link1);

			$link2 = getTemplatePart("Link",$mainhtml);
			$link2 = replace("ActionName", _CH_BALANCE, $link2);
			$link2 = replace("Action", "cb", $link2);
			$link2 = replace("Id", $xuser->getUserId(), $link2);

			$links = $link1.$link2;
		}

		elseif ($xuser->getStatus() == "active") {

			$link1 = getTemplatePart("Link",$mainhtml);
			$link1 = replace("ActionName", _SET_BM, $link1);
			$link1 = replace("Action", "sb", $link1);
			$link1 = replace("Id", $xuser->getUserId(), $link1);

			$link2 = getTemplatePart("Link",$mainhtml);
			$link2 = replace("ActionName", _CH_BALANCE, $link2);
			$link2 = replace("Action", "cb", $link2);
			$link2 = replace("Id", $xuser->getUserId(), $link2);

			$link3 = getTemplatePart("Link",$mainhtml);
			$link3 = replace("ActionName", _LOCK_USER, $link3);
			$link3 = replace("Action", "lu", $link3);
			$link3 = replace("Id", $xuser->getUserId(), $link3);

			$link4 = getTemplatePart("Link",$mainhtml);
			$link4 = replace("ActionName", _DELETE_USER, $link4);
			$link4 = replace("Action", "du", $link4);
			$link4 = replace("Id", $xuser->getUserId(), $link4);

			$links = $link1.$link2.$link3.$link4;
		}

		elseif ($xuser->getStatus() == "locked") {

			$link1 = getTemplatePart("Link",$mainhtml);
			$link1 = replace("ActionName", _UNLOCK_USER, $link1);
			$link1 = replace("Action", "uu", $link1);
			$link1 = replace("Id", $xuser->getUserId(), $link1);

			$link2 = getTemplatePart("Link",$mainhtml);
			$link2 = replace("ActionName", _DELETE_USER, $link2);
			$link2 = replace("Action", "du", $link2);
			$link2 = replace("Id", $xuser->getUserId(), $link2);

			$links = $link1.$link2;
		}

		elseif ($xuser->getStatus() == "inactive") {

			$link1 = getTemplatePart("Link",$mainhtml);
			$link1 = replace("ActionName", _DELETE_USER, $link1);
			$link1 = replace("Action", "du", $link1);
			$link1 = replace("Id", $xuser->getUserId(), $link1);

			$links = $link1;
		}

		elseif ($xuser->getStatus() == "administrator") {

			$link1 = getTemplatePart("Link",$mainhtml);
			$link1 = replace("ActionName", _CH_BALANCE, $link1);
			$link1 = replace("Action", "cb", $link1);
			$link1 = replace("Id", $xuser->getUserId(), $link1);

			$links = $link1;
		}
	}

	elseif ($user->getStatus() == "betmaster"){
		if ($xuser->getStatus() == "locked") {
			// 1. link
			$link1 = getTemplatePart("Link",$mainhtml);
			$link1 = replace("ActionName", _UNLOCK_USER, $link1);
			$link1 = replace("Action", "uu", $link1);
			$link1 = replace("Id", $xuser->getUserId(), $link1);

			$links = $link1;
		}

		elseif ($xuser->getStatus() == "active") {
			// 2. link
			$link2 = getTemplatePart("Link",$mainhtml);
			$link2 = replace("ActionName", _LOCK_USER, $link2);
			$link2 = replace("Action", "lu", $link2);
			$link2 = replace("Id", $xuser->getUserId(), $link2);

			$links = $link2;
		}
	}

	$mainhtml = replace("Link", $links, $mainhtml);

	$next_id = $xuser->getUserId()+1;
	$prev_id = $xuser->getUserId()-1;

	$prev_user = $db_mapper->getUserById($prev_id);
	$next_user = $db_mapper->getUserById($next_id);

	if ($xuser->getUserId() > $db_mapper->getFirstUserId()){
		// check if a user with the previous id exists
		while ($prev_user->getUserId() == ""){
			$prev_id--;
			$prev_user = $db_mapper->getUserById($prev_id);
		}
	}

	if ($xuser->getUserId() < $db_mapper->getLastUserId()){
		while ($next_user->getUserId() == ""){
			$next_id++;
			$next_user = $db_mapper->getUserById($next_id);
		}
	}

	// replace the navigation fields
	$mainhtml = replace("Prev", $xuser->getUserId() > $db_mapper->getFirstUserId() ? 
			$prev_id : $db_mapper->getFirstUserId(), 
			$mainhtml);
	$mainhtml = replace("Id", $xuser->getUserId(), $mainhtml);
	$mainhtml = replace("Actual", $xuser->getUserId(), $mainhtml); 
	$mainhtml = replace("LastPage", $db_mapper->getLastUserId(), $mainhtml);

	$mainhtml = replace("Next", $db_mapper->getLastUserId() > $xuser->getUserId() ? 
			$next_id : $xuser->getUserId(), 
			$mainhtml);
	$mainhtml = replace("PrevName", _PREVIOUS, $mainhtml);
	$mainhtml = replace("Edit", _EDIT, $mainhtml);
	$mainhtml = replace("NextName", _NEXT, $mainhtml);

	// TODO: make it more nice
	$mainhtml = replace("BackLink1", '<a href="transactionlist.php?uid='.$id.'">'._USER_TRANSACTION_LIST.'</a>', $mainhtml);
	$mainhtml = replace("BackLink2", '<a href="transactionlist.php">'._ALL_TRANSACTION_LIST.'</a>', $mainhtml);
	$mainhtml = replace("BackLink3", '<a href="userlist.php">'._USER_LIST.'</a>', $mainhtml);
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
