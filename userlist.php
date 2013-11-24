<?php
/* userlist.php - BetSter project (22.05.06)
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
	 ($user->getStatus() == "betmaster"))) {

	$mainhtml = file_get_contents("tpl/userlist.inc");

	// replace the main Title
	$mainhtml = replace("MainTitle", _USER_LIST, $mainhtml);

	$users = $db_mapper->getAllUsers();
	$size = $bstConfig_user_disp;
	$user_num = count($users);
	if ($user_num >= 1){
		// page number
		if (isset($_GET['pn'])){
			$page_num = htmlspecialchars($_GET['pn']);
			if (!(is_numeric($_GET['pn']))){
				$page_num = 1;
			}
		}
		else {
			$page_num = 1;
		}

		$num_pages = ceil($user_num/$size); 
		if ($num_pages < 1){
			$num_pages = 1;
		}
		if ($page_num >= $num_pages){
			$page_num = $num_pages;
		}
		$start_num = ($page_num * $size) - $size;
		$disp_num = ($page_num * $size);   
		//$disp_num = $user_num;

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
			if ($i < $user_num){
				$user_html_part .= getTemplatePart("User", $mainhtml);
				$user_html_part = replace("Status", $users[$i]->getStatus(), $user_html_part);
				$user_html_part = replace("Id", $users[$i]->getUserId(), $user_html_part);
				$user_html_part = replace("Username", $users[$i]->getUsername(), $user_html_part);
				$user_html_part = replace("Balance", $users[$i]->getBalance(), $user_html_part);      
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
		$nav_area = replace("UserId", "", $nav_area);
		$mainhtml = replace("BackLink1", "", $mainhtml);
		$mainhtml = replace("BackLink2", "", $mainhtml);
	}
	$nav_area = replace("ActualPage", $page_num, $nav_area);
	$nav_area = replace("LastPage", $num_pages, $nav_area);
	$mainhtml = replace("Message1", "", $mainhtml);

	// the head
	$head_area = getTemplatePart("UserHead",$mainhtml);
	$head_area = replace("StatusName", _STATUS, $head_area);
	$head_area = replace("UsernameName", _USERNAME, $head_area);
	$head_area = replace("BalanceName", _BALANCE, $head_area);
	$head_area = replace("MainTitle", _USER_LIST, $head_area);
	$mainhtml = replace("UserHead", $head_area, $mainhtml);
	$mainhtml = replace("User", $user_html_part, $mainhtml);
	$mainhtml = replace("NavArea", $nav_area, $mainhtml);
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
