<?php
/* betchecker.php - BetSter project (22.05.06)
 * Copyright (C) 2006  Harry
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


$mainhtml = file_get_contents("tpl/betchecker.inc");

function checkValues($bstConfig_state){

	$pos = htmlspecialchars($_POST['pos']);

	if ($bstConfig_state == "closed")
		return 3;	

	elseif (isset($_POST['pos']) && isset($_POST['credits']))
		$credits = htmlspecialchars($_POST['credits']);			
		if (is_numeric($credits) && (intval($credits) == floatval($credits))){
			return 1;
		}
	elseif (isset($_GET['pos']) && isset($_GET['cd']))
		$credits = htmlspecialchars($_GET['cd']);
		if (is_numeric($credits) && (intval($credits) == floatval($credits))){
			return 2;
		}
	else 
		return 0;
}

function checkBalance($user, $credits) {
	if (($user->getBalance() > 0) && ($user->getBalance() > $credits))
		return true;
}

function checkPossibility($db_mapper, $user, $pos){
	if ($db_mapper->checkBettedPossibility($user->getUserId(), $pos))
		return true;
}

function checkUserStatus($session, $user){
	if (($session->getState()) && 
		!($user->getStatus() == "locked"))
		return true;
	else
		return false;
}


if(checkUserStatus($session, $user)){

	$id_array = explode("#",$pos);
	$bet_id = $id_array[0];
	$bet = $db_mapper->getBet($bet_id);
	$cont_flag = checkValues($bstConfig_state);

	if (($cont_flag > 0) && ($bet->getBetEndTime() > strtotime(time()))){
		if(checkBalance($user, $credits)){
			if(checkPossibility($db_mapper, $user, $pos)){
				// success 
				if ($cont_flag == 1){
					// ask confirmation
					$pos = htmlspecialchars($_POST['pos']);
					$credits = htmlspecialchars($_POST['credits']);
					$session->unlockF5();
					$id_array = explode("#",$pos);
					$bet_id = $id_array[0];
					$possibility_id = $id_array[1];
					$pos = $bet_id."%23".$possibility_id;
					$bet = $db_mapper->getBet($bet_id);
					$quote = $db_mapper->getQuoteFromPosId($possibility_id, $credits);
					$win_credits = round(($quote * $credits), 0); 
					$new_balance = $user->getBalance() - $credits;
					$possibility_name = $db_mapper->getPossibilityNameFromId($possibility_id);
					$mainhtml = replace("Message1", _BET_PROPOSITION, $mainhtml);
					$line1 = getTemplatePart("Text", $mainhtml);
					$line1 = replace("Line", _BET.": ".$bet->getBetTitle(), $line1);
					$line2 = getTemplatePart("Text", $mainhtml);
					$line2 = replace("Line", _POSSIBILITY.": ".$possibility_name.": ".$bet->getBetTitle(), $line2);
					$line3 = getTemplatePart("Text", $mainhtml);
					$line3 = replace("Line", "Credits: ".$credits, $line3);
					$line4 = getTemplatePart("Text", $mainhtml);
					$line4 = replace("Line", _BET_WIN_CASE.$win_credits." Credits" , $line4);
					$line5 = getTemplatePart("Text", $mainhtml);
					$line5 = replace("Line", _BET_BALANCE_AFTER.$new_balance." Credits" , $line5);
					$line6 = getTemplatePart("Text", $mainhtml);
					$line6 = replace("Line", _BET_ASSICURATION , $line6);
					$mainhtml = replace("Text", $line1.$line2.$line3.$line4.$line5.$line6, $mainhtml);
					$mainhtml = replace("Link1", "<a href=\"betchecker.php?pos=$pos&cd=$credits\">"._BET_CONTINUE."</a>" , $mainhtml);
					$mainhtml = replace("Link2", "<a href=\"index.php\">"._BET_DELETE."</a>" , $mainhtml);
				}
				else if ($cont_flag == 2){
					// show confirmation
					$pos = htmlspecialchars($_GET['pos']);
					$credits = htmlspecialchars($_GET['cd']);
					$id_array = explode("#",$pos);
					$bet_id = $id_array[0];
					$pos_id = $id_array[1];
					$bet = $db_mapper->getBet($bet_id);
					$bet->execute($pos_id, $credits, $user);
					$pos_name = $db_mapper->getPossibilityNameFromId($pos_id);
					
					Mailer(_BET_SUB, _BET_MSG."\n"
							._BET.": ".$bet->getBetTitle()."\n"."\n"
							._POSSIBILITY.": ".$pos_name."\n"
							._CREDITS.": ".$credits."\n", $user->getEmail());
					
					$session->lockF5();
					
					$mainhtml = replace("Message1", _BET_ACCEPTED, $mainhtml);
					$mainhtml = replace("Text", "", $mainhtml);
					$mainhtml = replace("Link1", "", $mainhtml);
					$mainhtml = replace("Link2", "<a href=\"index.php\">"._BET_BACK."</a>" , $mainhtml); 
				}
				else if ($cont_flag == 3){
					// office closed or wrong time
					$mainhtml = file_get_contents("tpl/closed.inc");
					$mainhtml = replace("Message1", _BET_OFFICE_CLOSED, $mainhtml);
					$mainhtml = replace("Back", _BACK_HOME, $mainhtml);
				}
			}
			else {
				// wrong possibility
				$id_array = explode("#",$pos);
				$bet_id = $id_array[0];
				$possibility_id = $id_array[1];
				$bet = $db_mapper->getBet($bet_id);
				$possibility_name = $db_mapper->getPossibilityNameFromId($possibility_id);
				$mainhtml = replace("Message1", _BET_PROPOSITION, $mainhtml);
				$line1 = getTemplatePart("Text", $mainhtml);
				$line1 = replace("Line", _BET.": ".$bet->getBetTitle(), $line1);
				$line2 = getTemplatePart("Text", $mainhtml);
				$line2 = replace("Line", _POSSIBILITY.": ".$possibility_name, $line2);
				$line3 = getTemplatePart("Text", $mainhtml);
				$line3 = replace("Line", "Credits: ".$credits, $line3);
				$line4 = getTemplatePart("Text", $mainhtml);
				$line4 = replace("Line", _BET_UNVALID_POSSIBILITY , $line4);
				$mainhtml = replace("Text", $line1.$line2.$line3.$line4, $mainhtml);
				$mainhtml = replace("Link1", "<a href=\"index.php\">"._BET_BACK."</a>" , $mainhtml);
			}	
		}
		else {
			// balance not high enough
			$mainhtml = replace("Message1", _BET_FALSE_INPUTS, $mainhtml);
			$mainhtml = replace("Link1", "<a href=\"index.php\">"._BET_BACK."</a>" , $mainhtml);
		}
	}
	else {
		// wrong values or empty fields
		$mainhtml = replace("Message1", _BET_FALSE_INPUTS, $mainhtml);
		$mainhtml = replace("Link1", "<a href=\"index.php\">"._BET_BACK."</a>" , $mainhtml);
	}
}
else{
	// user not logged in or locked
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
