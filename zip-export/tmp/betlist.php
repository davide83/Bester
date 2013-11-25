<?php
/* betlist.php - BetSter project (22.05.06)
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

$session_state = $session->getState();
if ($session_state == 1){
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
if (($user->getStatus() == "administrator") || ($user->getStatus() == "betmaster")){
	$bets = $db_mapper->getAllBets();

	if ((isset($_GET['bet']) && isset($_GET['pos'])) &&
			is_numeric(htmlspecialchars($_GET['bet'])) && 
			is_numeric(htmlspecialchars($_GET['pos']))){
		$bet_id = $_GET['bet'];
		$pos_id = $_GET['pos'];

		$mainhtml = file_get_contents("template/freezer.inc");
	}
	else {

		$mainhtml = file_get_contents("tpl/betlist.inc");

		// replace the login fields with the user in case of a logged in user menu or reverse
		if ($session_state == 1){

			// general replacement of the user area
			$user = $db_mapper->getUser($session->getUsername());
			$mainhtml = replace("LoginArea","",$mainhtml);
			$user_area_part = getTemplatePart("UserArea",$mainhtml);
			$user_area_part = replace("Greet", _GREET, $user_area_part);
			$user_area_part = replace("Username", $username, $user_area_part);
			$user_area_part = replace("YourBalance", _YOUR_BALANCE, $user_area_part);
			$user_area_part = replace("Balance", $user->getBalance(), $user_area_part);
			$user_area_part = replace("YourBets", _YOUR_BETS, $user_area_part);
			$user_area_part = replace("YourProfile", 
					"<p><a href=\"profile.php\">".
					_YOUR_PROFILE."</a></p>", $user_area_part);
			$user_area_part = replace("EditProfile", 
					"<p><a href=\"profile_edit.php\">"
					._EDIT_PROFILE."</a></p>", $user_area_part);
			if (($user->getStatus() == "betmaster") || ($user->getStatus() == "administrator")){
				$user_area_part = replace("CreateBet", "<p><a href=\"insertbet.php\">"._INSERT_BET."</a></p>", $user_area_part);
			}
			else {
				$user_area_part = replace("CreateBet", "", $user_area_part);
			}
			$mainhtml = replace("UserArea", $user_area_part, $mainhtml);
		}
		// in case of a not logged in user
		else {
			$mainhtml = replace("UserArea","", $mainhtml);
		}
		$size = $bstConfig_bet_disp;
		$bet_num = count($bets);


		if (isset($_GET['pn'])){
			$page_num = htmlspecialchars($_GET['pn']);
			if (!(is_numeric($_GET['pn']))){
				$page_num = 1;
			}
		}
		else {
			$page_num = 1;
		}

		$num_pages = ceil($bet_num/$size);
		if ($page_num >= $num_pages){
			$page_num = $num_pages;
		}
		if ($page_num <= 1){
			$page_num = 1;
		}    
		$start_num = ($page_num * $size) - $size;
		$disp_num = ($page_num * $size);   
		if ($page_num == 1) {
			$disp_prev_num = 1; 
			$disp_next_num = $page_num+1;
		}
		elseif ($page_num == $num_pages){
			$disp_next_num = $num_pages;
			$disp_prev_num = $page_num-1;
		}
		else {
			$disp_next_num = $page_num+1;
			$disp_prev_num = $page_num-1;
		}

		// Read out the active Bets and replace the templates
		for ($i = $start_num; $i <= $disp_num-1; $i++ ){
			if ($i < $bet_num){
				if ($bets[$i]->getBetImage() == 1){
					$dir = "/images/bet/";
					$image_src = $dir.$bets[$i]->getBetId().".jpg";
				}
				else {
					$dir = "/images/category/";
					$cat = $db_mapper->getCategory($bets[$i]->getCategoryId());

					if ($cat->getCategoryImage())
						$image_src = $dir.$bets[$i]->getCategoryId().".jpg";
					else
						$image_src = $dir."no_image.gif";
				}

				$bet_html_part .= getTemplatePart("Bets", $mainhtml);
				$bet_html_part = replace("ImageSrc", $image_src ,$bet_html_part);
				$bet_html_part = replace("CategoryName", $bets[$i]->getCategoryName(), $bet_html_part);
				$bet_html_part = replace("BetNr", $bets[$i]->getBetId().". ", $bet_html_part);
				$bet_html_part = replace("Title", " ".$bets[$i]->getBetTitle(), $bet_html_part);
				$bet_html_part = replace("Start", strftime("%d/%m/%Y - %H:%M:%S" , strtotime($bets[$i]->getBetStartTime())), $bet_html_part);
				$bet_html_part = replace("End", strftime("%d/%m/%Y - %H:%M:%S" , strtotime($bets[$i]->getBetEndTime())), $bet_html_part);
				$bet_html_part = replace("SubTitle", $bets[$i]->getSubtitle(), $bet_html_part);
				$bet_html_part = replace("From", _FROM, $bet_html_part);
				$bet_html_part = replace("Until", _UNTIL, $bet_html_part);
				$possibilities_html_part = "";

				// replaces the ids, names and quotes of the Possibilities and the freeze area for the betmaster
				$winning_possibility = $db_mapper->getWonPossibility($bets[$i]->getBetId());

				// TODO: maybe make it better 
				if(($bets[$i]->getBetStartTime() < strftime("%Y-%m-%d %H:%M:%S", time()) &&
							($bets[$i]->getBetEndTime() > strftime("%Y-%m-%d %H:%M:%S", time())))){
					$bet_is_actual = true;
				}
				else $bet_is_actual = false;

				if($bets[$i]->getBetEndTime() < strftime("%Y-%m-%d %H:%M:%S", time())){
					$bet_is_past = true;
				}
				else $bet_is_past = false;

				if($bets[$i]->getBetStartTime() > strftime("%Y-%m-%d %H:%M:%S", time())){
					$bet_is_in_future = true;
				}
				else $bet_is_in_future = false;

				// replaces the ids, names and quotes of the Possibilities
				$k = 0;
				$possibilities_names = $bets[$i]->getPossibilitiesNames();
				$possibilities_ids = $bets[$i]->getPossibilitiesIds();
				$possibilities_quotes = $bets[$i]->getPossibilitiesQuotes();
				foreach($possibilities_names as $possibility_name){
					$current_possiblility_quote = " - Quote: ".$possibilities_quotes[$k];
					$possibilities_html_part .= getTemplatePart("Possibilities", $mainhtml);
					$possibilities_html_part = replace("PossibilityID", $possibilities_ids[$k], $possibilities_html_part);
					$possibilities_html_part = replace("PossibilityName", $possibility_name, $possibilities_html_part);
					$possibilities_html_part = replace("PossibilityQuote", " - Quote: ".$possibilities_quotes[$k], $possibilities_html_part);

					if ($possibilities_ids[$k] == $winning_possibility){
						$possibilities_html_part = replace("FreezeArea", "", $possibilities_html_part);
					}
					else {
						$possibilities_html_part = replace("PossibilityCSS", "possibility", $possibilities_html_part);
						// if a betmaster is logged in
						if ($session_state == 1 && (($user->getStatus() == "betmaster") || 
									($user->getStatus() == "administrator"))){
							$freeze_area_part = getTemplatePart("FreezeArea",$possibilities_html_part);
							$freeze_area_part = replace("BetID", $bets[$i]->getBetId(), $freeze_area_part);
							$freeze_area_part = replace("PossibilityID", $current_possibilitiy_id, $freeze_area_part);
							if (($winning_possibility == "") && ($bet_is_past == true)){
								$possibilities_html_part = replace("FreezeArea", $freeze_area_part, $possibilities_html_part);
							}
							else {
								$possibilities_html_part = replace("FreezeArea", "", $possibilities_html_part);
							}
							if (($bet_is_actual == true) || ($bet_is_in_future == true)){	
								$bet_html_part = replace("AnnullateLink", '<a href="anulbet.php?id='.$bets[$i]->getBetId().'"><img src="images/icons/anullate.png" border="0"></a>', $bet_html_part);
							}
							else {
								$bet_html_part = replace("AnnullateLink", "", $bet_html_part);
							}
						}
					}
					$k++;
				}
				$bet_html_part = replace("Possibilities", $possibilities_html_part, $bet_html_part);
			}
		}

		$nav_area = getTemplatePart("NavArea",$mainhtml);
		if ($num_pages <= 1){
			$nav_area = "";
		}
		else {
			$nav_area = replace("FirstPage", 1, $nav_area);
			$nav_area = replace("LastPage", $num_pages, $nav_area);

			$nav_area = replace("NextPage", $disp_next_num, $nav_area);
			$nav_area = replace("PrevPage", $disp_prev_num, $nav_area);

			$nav_area = replace("ActualPage", $page_num, $nav_area);
			$nav_area = replace("LastPage", $num_pages, $nav_area);

		}
		$mainhtml = replace("NavArea", $nav_area, $mainhtml);
		$mainhtml = replace("Bets", $bet_html_part, $mainhtml);

		// replace the main Title
		$mainhtml = replace("NewBet", _INSERT_BET, $mainhtml);
		$mainhtml = replace("MainTitle", _BET_LIST, $mainhtml);
	}
}
else{
	// not logged in 
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
