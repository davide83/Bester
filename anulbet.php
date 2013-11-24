<?php
/* anulbet.php - BetSter project (22.05.06)
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

if (($user->getStatus() == "administrator")||
	($user->getStatus() == "betmaster")){

	if ($_POST['delete'] == _DELETE){
		if ((is_numeric($_GET['id']))){
			$id = $_GET['id'];
			// TODO: if no bet is returned
			$bet = $db_mapper->getBet($id);

			// check if bet is actual or ready to be actual in the future
			if((($bet->getBetStartTime() < strftime("%Y-%m-%d %H:%M:%S", time()) &&
							($bet->getBetEndTime() > strftime("%Y-%m-%d %H:%M:%S", time())))) ||
					(($bet->getBetStartTime() > strftime("%Y-%m-%d %H:%M:%S", time()) &&
					  ($bet->getBetEndTime() > strftime("%Y-%m-%d %H:%M:%S", time()))))){

				$mainhtml = file_get_contents("tpl/anulbet1.inc");      	
				$mainhtml = replace("Message1", _SUCC_DELETE_BET, $mainhtml);
				$mainhtml = replace("Back", _BACK_BET_LIST, $mainhtml);
				$pos_ids = $db_mapper->getPossibilitiesIdsOfBet($id);
				$logger->writeLog($user->getUsername(), _BET_DELETE_BEGIN.$id);
				foreach ($pos_ids as $pos_id){
					$xusers = $db_mapper->getUsersOfPossibilities($pos_id);
					foreach ($xusers as $xuser) {
						$credits = $db_mapper->getBettedCredits($xuser->getUserId(), $pos_id);
						$db_mapper->addBalance($xuser->getUserId(), $credits);
						$logger->writeLog($xuser->getUsername(), _CREDITS_ADDED.$credits);
					}
					$db_mapper->deletePossibility($pos_id);
					$logger->writeLog($user->getUsername(), _POSSIBILITY_DELETED.$pos_id);
				}
				$db_mapper->deleteBet($id);
				$logger->writeLog($user->getUsername(), _BET_DELETED.$id);

				// TODO: send emails to users which have betted on this bet
			}
		}
	}
	elseif ($_POST['cancel'] == _CANCEL){
		header("Location:betlist.php");
	}
	else {
		$mainhtml = file_get_contents("tpl/anulbet.inc");

		if ((is_numeric($_GET['id']))){
			$id = $_GET['id'];
			$bet = $db_mapper->getBet($id);

			// check if bet is actual or ready to be actual in the future
			if((($bet->getBetStartTime() < strftime("%Y-%m-%d %H:%M:%S", time()) &&
							($bet->getBetEndTime() > strftime("%Y-%m-%d %H:%M:%S", time())))) ||
					(($bet->getBetStartTime() > strftime("%Y-%m-%d %H:%M:%S", time()) &&
					  ($bet->getBetEndTime() > strftime("%Y-%m-%d %H:%M:%S", time()))))){

				// preview
				$mainhtml = replace("CategoryCSS", $bet->getCategoryId(), $mainhtml);
				$mainhtml = replace("CategoryName", $bet->getCategoryName(), $mainhtml);
				$mainhtml = replace("Title", " ".$bet->getBetTitle(), $mainhtml);
				$mainhtml = replace("SubTitle", $bet->getSubtitle(), $mainhtml);
				$mainhtml = replace("Until", _ACTIVE_UNTIL , $mainhtml);
				$mainhtml = replace("Start", strftime("%d/%m/%Y - %H:%M:%S Uhr" , strtotime($bet->getBetStartTime())), $mainhtml);
				$mainhtml = replace("End", strftime("%d/%m/%Y - %H:%M:%S Uhr" , strtotime($bet->getBetEndTime())), $mainhtml);
				$possibilities_html_part = "";

				// wich is the right image for the bet
				if ($bet->getBetImage() == 1){
					$dir = "images/bet/";
					$image_src = $dir.$bet->getBetId().".jpg";
				}
				else {
					$dir = "images/category/";
					$image_src = $dir.$bet->getCategoryId().".jpg";
				}

				$mainhtml = replace("ImageSrc", $image_src, $mainhtml);
				// replaces the ids, names and quotes of the Possibilities
				$possibilities_names = $bet->getPossibilitiesNames();

				foreach($possibilities_names as $possibility_name){
					$possibilities_html_part .= getTemplatePart("Possibilities", $mainhtml);
					$possibilities_html_part = replace("PossibilityName", $possibility_name, $possibilities_html_part);
				}
				$mainhtml = replace("Possibilities", $possibilities_html_part, $mainhtml);

				// buttons
				$mainhtml = replace("ButtonGoValue", _DELETE, $mainhtml);
				$mainhtml = replace("ButtonGoName", "delete", $mainhtml);
				$mainhtml = replace("ButtonDeleteValue", _CANCEL, $mainhtml);
				$mainhtml = replace("ButtonDeleteName", "cancel", $mainhtml);

				$mainhtml = replace("Message1", _SURE_DELETE_BET, $mainhtml);			
			}
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
