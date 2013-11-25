<?php
/* mywins.php - BetSter project (22.05.06)
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
$mainhtml = file_get_contents("tpl/mywins.inc");

// replace the main Title
$mainhtml = replace("MainTitle", _MY_WINS, $mainhtml);

// in case of a logged in user
// replace the login fields with the user in case of a logged in user menu or reverse
//------------------------------------------------------------------------------------
if ($session->getState()){
	$user = $db_mapper->getUser($session->getUsername());

	$wins = $db_mapper->getWins($user->getUserId());
	$size = $bstConfig_wins_disp;
	$wins_num = count($wins);

	if ($wins_num >= 1){
		// page number
		if (isset($_GET['pn'])){;
			$page_num = htmlspecialchars($_GET['pn']);
			if (!(is_numeric($_GET['pn']))){
				$page_num = 1;
			}
		}
		else {
			$page_num = 1;
		}

		$num_pages = ceil($wins_num/$size); 
		if ($num_pages < 1){
			$num_pages = 1;
		}
		if ($page_num >= $num_pages){
			$page_num = $num_pages;
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

		$mainhtml = replace("BetTitle", _BET, $mainhtml);
		$mainhtml = replace("PosTitle", _POSSIBILITY, $mainhtml);
		$mainhtml = replace("CreditsTitle", _CREDITS, $mainhtml);
		$mainhtml = replace("TimeTitle", _DATE_TIME, $mainhtml);

		for ($i = $start_num; $i <= $disp_num-1; $i++ ){
			if ($i < $wins_num){
				$wins_html_part .= getTemplatePart("Wins",$mainhtml);
				$wins_html_part = replace("BetName", $wins[$i]->getBetTitle(), $wins_html_part);
				$wins_html_part = replace("PossibilityName", $wins[$i]->getPossibilityName(), $wins_html_part);
				$wins_html_part = replace("Quote", $wins[$i]->getQuote(), $wins_html_part);
				$wins_html_part = replace("Credits", $wins[$i]->getCredits(), $wins_html_part);
			}
		}
		$nav_area = getTemplatePart("NavArea",$mainhtml);

		if ($num_pages <= 1){
			$nav_area = "";
		}
		else {
			$nav_area = replace("NextPage", "pn=".$disp_next_num, $nav_area);
			$nav_area = replace("PrevPage", "pn=".$disp_prev_num, $nav_area);
		}

	}
	$nav_area = replace("ActualPage", $page_num, $nav_area);
	$nav_area = replace("LastPage", $num_pages, $nav_area);
	$mainhtml = replace("Message1", "", $mainhtml);
	$mainhtml = replace("NavArea", $nav_html_part, $mainhtml);
	$mainhtml = replace("Wins", $wins_html_part, $mainhtml);

}
else {
	$mainhtml = replace("Message1", _USER_NO_WINS, $mainhtml);
	$mainhtml = replace("winsactionsHead", "", $mainhtml);
	$mainhtml = replace("winsactions", "", $mainhtml);
	$mainhtml = replace("NavArea", "", $mainhtml);
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
