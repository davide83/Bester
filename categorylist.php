<?php
/* categorylist.php - BetSter project (22.05.06)
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
$mainhtml = file_get_contents("tpl/categorylist.inc");

// replace the main Title
$mainhtml = replace("MainTitle", _CATEGORY_LIST, $mainhtml);
//$mainhtml = replace("Message1", _CATEGORY_EDIT_INFO, $mainhtml);

// replace the main Title
$mainhtml = replace("MainTitle", _CATEGORY_LIST, $mainhtml);
//$mainhtml = replace("Message1", _CATEGORY_EDIT_INFO, $mainhtml);

$cat_html_part = "";
if (($user->getStatus() == "administrator") ||
	($user->getStatus() == "betmaster")){
	
	$user = $db_mapper->getUser($session->getUsername());

	$categories = $db_mapper->getAllCategories();
	$size = $bstConfig_cat_disp;
	$cat_num = count($categories);

	// page number
	if (isset($_GET["pn"]) && is_numeric(htmlspecialchars($_GET['pn']))) {
		$page_num = htmlspecialchars($_GET['pn']);
	}
	else {
		$page_num = 1;
	}

	$nav_area = "";
	$num_pages = "";
	if ($cat_num >= 1){
		
		// shifting category positions
		if (isset($_GET['dir']) && isset($_GET['id'])){
			if ($_GET['dir'] == "up")
				$db_mapper->shiftCategoryUp(htmlspecialchars($_GET['id']));
			elseif ($_GET['dir'] == "down")
				$db_mapper->shiftCategoryDown(htmlspecialchars($_GET['id']));
		}
	
		// update after shifting	
		$categories = $db_mapper->getAllCategories();
	

		$num_pages = ceil($cat_num/$size); 
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
			if ($i < $cat_num){

				$cat_html_part .= getTemplatePart("Categories", $mainhtml);
				
				// don't show move-up arrow
				if ($i == 0)
					$cat_html_part = replace("CategoryUpImage", "", $cat_html_part);
				else
					$cat_html_part = replace("CategoryUpImage", getTemplatePart("CategoryUpImage", $cat_html_part), $cat_html_part);
				
				// don't show move-down arrow
				if ($i == $cat_num-1)
					$cat_html_part = replace("CategoryDownImage", "", $cat_html_part);
				else 
					$cat_html_part = replace("CategoryDownImage", getTemplatePart("CategoryDownImage", $cat_html_part), $cat_html_part);
				
				$cat_html_part = replace("CategoryID", $categories[$i]->getCategoryID(), $cat_html_part);
				$cat_html_part = replace("CategoryPosition", $categories[$i]->getCategoryPosition(), $cat_html_part);
				
				if ($categories[$i]->getCategoryImage() == 1)
					$cat_html_part = replace("CategoryImageSrc", "images/category/".$categories[$i]->getCategoryId().".jpg", $cat_html_part);
				else
					$cat_html_part = replace("CategoryImageSrc", "images/category/no_image.gif", $cat_html_part);
				
				$cat_html_part = replace("CategoryName", $categories[$i]->getCategoryName(), $cat_html_part);
				$cat_html_part = replace("CategoryDescription", $categories[$i]->getCategoryDescription(), $cat_html_part);
			}
		}
		$mainhtml = replace("Categories", $cat_html_part, $mainhtml);

		$nav_area = getTemplatePart("NavArea",$mainhtml);
		if ($num_pages <= 1){
			$nav_area = "";
		}
		else {
			$nav_area = replace("FirstPage", "pn=1", $nav_area);
			$nav_area = replace("LastPage", "pn=".$num_pages, $nav_area);

			$nav_area = replace("NextPage", "pn=".$disp_next_num, $nav_area);
			$nav_area = replace("PrevPage", "pn=".$disp_prev_num, $nav_area);
		}
		$nav_area = replace("UserId", "", $nav_area);
		$mainhtml = replace("BackLink1", "", $mainhtml);
		$mainhtml = replace("BackLink2", "", $mainhtml);
		


	}
	else {
		$mainhtml = replace("Categories", "", $mainhtml);
	}
	$nav_area = replace("ActualPage", $page_num, $nav_area);
	$nav_area = replace("NumPages", $num_pages, $nav_area);
	$mainhtml = replace("Message1", "", $mainhtml);
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
