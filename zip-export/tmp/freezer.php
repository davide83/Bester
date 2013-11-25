<?php
/* freezer.php - BetSter project (22.05.06)
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

	if (is_numeric(htmlspecialchars($_GET['bet'])) && 
		(is_numeric(htmlspecialchars($_GET['pos'])))){
		$bet_id = htmlspecialchars($_GET['bet']);
		$pos_id = htmlspecialchars($_GET['pos']);
		$bet = $db_mapper->getBet($bet_id);
	}

	// he is sure...
	if (htmlspecialchars($_POST['set']) == _SET){

		// TODO: check for errors during freezing
		$mainhtml = file_get_contents("tpl/freezer1.inc");
		$mainhtml = replace("MainTitle", _FREEZE_BET, $mainhtml);
		$mainhtml = replace("Message1", _FREEZE_BET_SUC, $mainhtml);
		$mainhtml = replace("Back", _BET_BACK, $mainhtml);

		$bet = $db_mapper->getBet($bet_id);
		$bet->freeze($pos_id);
		$logger->writeLog($user->getUsername(), _FREEZE_BET_ID.$bet->getBetId());

		// TODO: send emails
		$winners = $db_mapper->getWinningUsers($pos_id);
		$loosers = $db_mapper->getLoosingUsers($bet_id);
		$quote = $db_mapper->getQuoteFromPosId($pos_id, 0);

		// TODO: make it in the Db_Mapper Class
		foreach ($winners as $winner) {

			$user_id = $winner->getUserId();

			$won_credits = $db_mapper->getWonCredits($winner->getUserId(), $pos_id);
			$query = "UPDATE user SET balance = (balance + '$won_credits') WHERE id = '$user_id'";
			mysql_db_query(DBNAME, $query) or die("Ungültige Abfrage: ".mysql_error());

			$query = "INSERT INTO userwins (possibilities_id, user_id, won_credits, quote)
				VALUES ('".$pos_id."', '".$user_id."', '".$won_credits."', '".$quote."');";
			mysql_db_query(DBNAME, $query) or die("Ungültige Abfrage: ".mysql_error());
			$winners_email .= $winner->getEmail().", ";
		}

		foreach($loosers as $looser){
			$loosers_email .= $looser->getEmail().", ";
		}
		$pos_name = $db_mapper->getPossibilityNameFromId($pos_id);
		Mailer(_WIN_SUB, _WIN_MSG."\n"."\n"
				._BET.": ".$bet->getBetTitle()."\n"
				._POSSIBILITY.": ".$pos_name."\n"
				._QUOTE.": ".$quote."\n", $winners_email);
		Mailer(_LOOSE_SUB, _LOOSE_MSG."\n"."\n"
				._BET.": ".$bet->getBetTitle()."\n"
				._POSSIBILITY.": ".$pos_name."\n"
				, $loosers_email);
	}

	elseif ($_POST['cancel'] == _CANCEL){
		header("Location:betlist.php");		
	}

	else {
		$mainhtml = file_get_contents("tpl/freezer.inc");
		$mainhtml = replace("MainTitle", _FREEZE_BET, $mainhtml);
		$mainhtml = replace("Message1", _SURE_FREEZE_BET, $mainhtml);


		// check if bet is past
		if($bet->getBetEndTime() < strftime("%Y-%m-%d %H:%M:%S", time())) {
			// preview
			$mainhtml = replace("CategoryCSS", $bet->getCategoryId(), $mainhtml);
			$mainhtml = replace("CategoryName", $bet->getCategoryName(), $mainhtml);
			$mainhtml = replace("Title", " ".$bet->getBetTitle(), $mainhtml);
			$mainhtml = replace("SubTitle", $bet->getSubtitle(), $mainhtml);
			$mainhtml = replace("Until", _ACTIVE_UNTIL , $mainhtml);
			$mainhtml = replace("Start", strftime("%d/%m/%Y - %H:%M:%S Uhr" ,
						strtotime($bet->getBetStartTime())), $mainhtml);
			$mainhtml = replace("End", strftime("%d/%m/%Y - %H:%M:%S Uhr" ,
						strtotime($bet->getBetEndTime())), $mainhtml);
			$possibilities_html_part = "";

			// which is the right image for the bet
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
			$pos_name = $db_mapper->getPossibilityNameFromId($pos_id);

			$possibilities_html_part .= getTemplatePart("Possibilities", $mainhtml);
			$possibilities_html_part = replace("PossibilityName", $pos_name, $possibilities_html_part);
			$mainhtml = replace("Possibilities", $possibilities_html_part, $mainhtml);

			// buttons
			$mainhtml = replace("ButtonGoValue", _SET, $mainhtml);
			$mainhtml = replace("ButtonGoName", "set", $mainhtml);
			$mainhtml = replace("ButtonDeleteValue", _CANCEL, $mainhtml);
			$mainhtml = replace("ButtonDeleteName", "cancel", $mainhtml);

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
