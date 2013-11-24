<?php
/* transactionlist.php - BetSter project (22.05.06)
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

	$mainhtml = file_get_contents("tpl/transactionlist.inc");
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


	// user id
	if (isset($_GET['uid'])){
		$xuser_id = htmlspecialchars($_GET['uid']);
		if (!(is_numeric($xuser_id))){
			$xuser_id = "";
		}
		else {
			$transactions = $db_mapper->getTransactions($xuser_id);
			$xuser = $db_mapper->getUser($xuser_id);
		}
	}
	else {
		$transactions = $db_mapper->getAllTransactions();
	}
	$size = $bstConfig_trans_disp;
	$trans_num = count($transactions);

	if ($trans_num >= 1){
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

		$num_pages = ceil($trans_num/$size); 
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
		for ($i = $start_num; $i <= $disp_num-1; $i++ ){
			if ($i < $trans_num){
				$winning_possibility = $db_mapper->getWonPossibility($transactions[$i]->getBetId()); // FIXXME
				$trans_html_part .= getTemplatePart("Transactions", $mainhtml);
				$xuser_id = $transactions[$i]->getUserId();
				$xuser = $db_mapper->getUserById($xuser_id);
				$trans_html_part = replace("XUserId", $xuser->getUserId(), $trans_html_part);
				$trans_html_part = replace("XUser", $xuser->getUsername(), $trans_html_part);
				$trans_html_part = replace("BetTitle", $transactions[$i]->getBetTitle(), $trans_html_part);
				if ($winning_possibility == $transactions[$i]->getPossibilityId()){
					$trans_html_part = replace("TransactionsCSS", "transactions-red", $trans_html_part);
				}
				else {
					$trans_html_part = replace("TransactionsCSS", "transactions", $trans_html_part);
				}
				$trans_html_part = replace("PossibilityName", $transactions[$i]->getPossibilityName(), $trans_html_part);
				$trans_html_part = replace("Credits", $transactions[$i]->getCredits(), $trans_html_part);
				$trans_html_part = replace("Time", strftime("%d/%m/%Y - %H:%M:%S Uhr" , strtotime($transactions[$i]->getTime())), $trans_html_part);
			}
		}
		$nav_area = getTemplatePart("NavArea",$mainhtml);

		if ($num_pages <= 1){
			$nav_area = "";
		}
		else {
			$nav_area = replace("NextPage", "pn=".$disp_next_num, $nav_area);
			$nav_area = replace("PrevPage", "pn=".$disp_prev_num, $nav_area);
			if (isset($_GET['uid'])){
				$nav_area = replace("UserId", "&uid=".$xuser_id, $nav_area);
				$mainhtml = replace("BackLink1", '<a href="transactionlist.php">'._ALL_TRANSACTION_LIST.'</a>', $mainhtml);
				$mainhtml = replace("BackLink2", '<a href="showprofile.php?id='.$xuser->getUserId().'">'._PROFILE_OF.'</a>', $mainhtml);
			}
			else {
				$nav_area = replace("UserId", "", $nav_area);
				$mainhtml = replace("BackLink1", "", $mainhtml);
				$mainhtml = replace("BackLink2", "", $mainhtml);
			}
		}
		$nav_area = replace("ActualPage", $page_num, $nav_area);
		$nav_area = replace("LastPage", $num_pages, $nav_area);
		$mainhtml = replace("Message1", "", $mainhtml);

		// the head
		$head_area = getTemplatePart("TransactionsHead",$mainhtml);
		$head_area = replace("UsernameName", _USERNAME, $head_area);
		$head_area = replace("BetName", _BET, $head_area);
		$head_area = replace("PosName", _POS, $head_area);
		$head_area = replace("CreditsName", _CREDITS, $head_area);
		$head_area = replace("DateTime", _DATE_TIME, $head_area);
		$mainhtml = replace("TransactionsHead", $head_area, $mainhtml);
		$mainhtml = replace("Transactions", $trans_html_part, $mainhtml);
		$mainhtml = replace("NavArea", $nav_area, $mainhtml);

	}
	else {
		$mainhtml = replace("Message1", _USER_NO_TRANSACTIONS, $mainhtml);
		$mainhtml = replace("TransactionsHead", "", $mainhtml);
		$mainhtml = replace("Transactions", "", $mainhtml);
		$mainhtml = replace("NavArea", "", $mainhtml);
	}
	if (isset($_GET['uid'])){
		$mainhtml = replace("MainTitle", _TRANSACTIONS_OF_USER.$xuser->getUsername(), $mainhtml);
	}
	else {
		$mainhtml = replace("MainTitle", _TRANSACTION_LIST, $mainhtml);
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
