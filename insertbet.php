<?php
/* insertbet.php - script to insert a new bet - BetSter project (22.05.06)
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
if (($user->getStatus() == "administrator") || 
	($user->getStatus() == "betmaster")){
	
	if (!isset($_POST['go1']))
		$_POST['go1'] = "";

	if (!isset($_POST['go2']))
		$_POST['go2'] = "";

	if (!isset($_POST['go3']))
		$_POST['go3'] = "";

	if (!isset($_POST['go4']))
		$_POST['go4'] = "";	

	if (!isset($_POST['back2']))
		$_POST['back2'] = "";	
	
	if (!isset($_POST['back3']))
		$_POST['back3'] = "";	

	if (!isset($_POST['back4']))
		$_POST['back4'] = "";	
	
	if (!isset($_POST['continue2']))
		$_POST['continue2'] = "";	
	
	if (!isset($_POST['continue3']))
		$_POST['continue3'] = "";	
	
	if (!isset($_POST['continue4']))
		$_POST['continue4'] = "";	
	
	if (!isset($_POST['delete1']))
		$_POST['delete1'] = "";	
		
	if (!isset($_POST['delete2']))
		$_POST['delete2'] = "";		
	
	if (!isset($_POST['delete3']))
		$_POST['delete3'] = "";	
	
	if (!isset($_POST['delete4']))
		$_POST['delete4'] = "";	

	if (!isset($_POST['delete_def']))
		$_POST['delete_def'] = "";	
	
	// after the first go "click"
	if ($_POST['go1'] == _GO){
		$new_bet = unserialize($_SESSION['newbet']);
		$new_bet->setBetId(rand(1,9999));
		$ver_flag = false;
		
		// verify textual inputs
		if ((strlen($_POST['title']) >= MIN_BET_TITLE_LENGTH) &&
				preg_match('/\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}/', $_POST['startdate']) &&
				preg_match('/\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}/', $_POST['enddate']) &&
				strtotime($_POST['startdate']) > time() &&
				strtotime($_POST['enddate']) > strtotime($_POST['startdate']) &&
				($_POST['posnr'] >= 2) && ($_POST['posnr'] < MAX_BET_POS_AMOUNT))
				$ver_flag = true;
					
		// check image
		if ($_FILES['betimage']['name'] != "") {
			$new_bet->setBetImage();
			
			$dir = 'images/bet/tmp/';
			$image_name = $new_bet->getBetId();
			
			$filename = $_FILES['betimage']['name'];
			$filetype = $_FILES['betimage']['type'];
			$filesize = $_FILES['betimage']['size'];
			
			if(($filesize < MAX_IMAGE_FILESIZE) && 
				($filetype == "image/jpeg") &&
				move_uploaded_file($_FILES['betimage']['tmp_name'],	$dir.$filename)){
					$image_name = $new_bet->getBetId();
					resizeAndSaveImage($dir, $filename, $image_name.".jpg");
			}
			else
				$ver_flag == false;
		}
		
		if ($ver_flag){
			$mainhtml = file_get_contents("tpl/insertbet2.inc");
	
			
			$new_bet->setBetTitle($_POST['title']);
			$new_bet->setSubtitle($_POST['subtitle']);
			$new_bet->setBetStartTime($_POST['startdate']);
			$new_bet->setBetEndTime($_POST['enddate']);
	
			$pos_array = "";
			if (count($new_bet->getPossibilitiesNames()) == $_POST['posnr']){
				$pos_array = $new_bet->getPossibilitiesNames();
			}
			elseif (count($new_bet->getPossibilitiesNames()) <> $_POST['posnr']){
				$ex_pos_array = $new_bet->getPossibilitiesNames();
				for ($i = 1; $i <= $_POST['posnr']; $i++){
					$pos_array[$i - 1] = $ex_pos_array[$i - 1];
				}
			}
	
			$i = 0;
			$pos_part = "";
			foreach ($pos_array as $pos_name){
				$pos_part .= getTemplatePart("Possibilities", $mainhtml);
				$pos_part = replace("Pos", _POSSIBILITY." ".($i+1), $pos_part); 
				$pos_part = replace("PosName", "pos".$i, $pos_part); 
				$pos_part = replace("PosValue", $pos_name, $pos_part); 
				$i++;
			}
			$new_bet->setPossibilitiesNames($pos_array);
	
			$_SESSION['newbet'] = serialize($new_bet);
	
			$mainhtml = replace("Possibilities", $pos_part, $mainhtml);
	
			$mainhtml = replace("MainTitle", _INSERT_BET_MAIN_TITLE2, $mainhtml);
			$mainhtml = replace("Message1", _INSERT_BET_MESSAGE2, $mainhtml);
	
			// buttons
			$mainhtml = replace("ButtonGoName", "go2", $mainhtml);
			$mainhtml = replace("ButtonGoValue", _GO , $mainhtml);
			$mainhtml = replace("ButtonBackName", "back2" , $mainhtml);
			$mainhtml = replace("ButtonBackValue", _BACK , $mainhtml);
			$mainhtml = replace("ButtonDeleteName", "delete2" , $mainhtml);
			$mainhtml = replace("ButtonDeleteValue", _DELETE , $mainhtml);

		}
		// if the inputs are wrong or incomplete
		else {
			$mainhtml = file_get_contents("tpl/insertbet1.inc");
			// show the blank form
			$mainhtml = replace("MainTitle", _INSERT_BET_MAIN_TITLE1, $mainhtml);
			$mainhtml = replace("Message1", _INSERT_BET_FALSE, $mainhtml);
			$mainhtml = replace("Title", _INSERT_BET_TITLE, $mainhtml);
			$mainhtml = replace("SubTitle", _INSERT_BET_SUBTITLE, $mainhtml);
			$mainhtml = replace("StartDate", _INSERT_BET_STARTDATE, $mainhtml);
			$mainhtml = replace("EndDate", _INSERT_BET_ENDDATE, $mainhtml);
			$mainhtml = replace("BetImage", _INSERT_BET_IMAGE, $mainhtml);
			$mainhtml = replace("PosNr", _INSERT_BET_POS_NR, $mainhtml);


			// fill select-menu
			$select_item = "";
			for ($k = 2; $k <= MAX_BET_POS_AMOUNT; $k++){

				$select_item .= getTemplatePart("SelectItem", $mainhtml);
				
				if ($k == $_POST['posnr'])
					$select_item = replace("ItemSelected", "selected=\"selected\"", $select_item);
				else
					$select_item = replace("ItemSelected", "", $select_item);
					
				$select_item = replace("SelectItemValue", $k, $select_item);
				$select_item = replace("SelectItemName", $k, $select_item);
			}
			
			$mainhtml = replace("SelectItem", $select_item, $mainhtml);
			
			// replace the values of the input fields with nothing
			$mainhtml = replace("InsTitle", $_POST['title'], $mainhtml);
			$mainhtml = replace("InsSubTitle", $_POST['subtitle'], $mainhtml);
			$mainhtml = replace("InsStartDate", $_POST['startdate'], $mainhtml);
			$mainhtml = replace("InsEndDate", $_POST['enddate'], $mainhtml);
			$mainhtml = replace("InsBetImage", "", $mainhtml);
			$mainhtml = replace("InsPosNr", $_POST['posnr'], $mainhtml);

			// replace the names and values for the buttons
			$mainhtml = replace("ButtonGoName", "go1", $mainhtml);
			$mainhtml = replace("ButtonGoValue", _GO , $mainhtml);
			$mainhtml = replace("ButtonDeleteName", "delete1" , $mainhtml);
			$mainhtml = replace("ButtonDeleteValue", _DELETE , $mainhtml);
		}
	}

	// after the second go click (choose category)
	elseif ($_POST['go2'] == _GO){

		// add the possibilities to the new bet object   
		$new_bet = unserialize($_SESSION['newbet']);
		$pos_array = $new_bet->getPossibilitiesNames();
		$i = 0;
		
		// foreach ($pos_array as &$pos_name){
		foreach ($pos_array as $pos_name){
			if ($_POST['pos'.$i] != ""){
				$new_pos_array[$i] = $_POST['pos'.$i];
				$i++;
			}
		}

		if((count(array_unique($new_pos_array)) >= 2)){
			$new_bet->setPossibilitiesNames(array_unique($new_pos_array)); 
			$_SESSION['newbet'] = serialize($new_bet);

			$mainhtml = file_get_contents("tpl/insertbet3.inc");
			$mainhtml = replace("MainTitle", _INSERT_BET_MAIN_TITLE3, $mainhtml);
			$mainhtml = replace("Message1", _INSERT_BET_MESSAGE3, $mainhtml);
			$mainhtml = replace("Message2", _INSERT_BET_MESSAGE_NEW_CAT, $mainhtml);

			$categories = $db_mapper->getAllCategories();

			// display categories
			$cat_html_part = "";
			foreach($categories as $category) {
				$cat_html_part .= getTemplatePart("Categories", $mainhtml);
				$cat_html_part = replace("CategoryID", $category->getCategoryId(), $cat_html_part);
				
				if ($category->getCategoryImage())
					$cat_html_part = replace("CategoryImageSrc", "images/category/".$category->getCategoryId().".jpg", $cat_html_part);
				else
					$cat_html_part = replace("CategoryImageSrc", "images/category/no_image.gif", $cat_html_part);
				
				$cat_html_part = replace("CategoryName", $category->getCategoryName(), $cat_html_part);
				$cat_html_part = replace("CategoryDescription", $category->getCategoryDescription(), $cat_html_part);
			}

			$mainhtml = replace("Categories", $cat_html_part, $mainhtml);

			// replace the names and values for the buttons
			$mainhtml = replace("ButtonGoName", "go3", $mainhtml);
			$mainhtml = replace("ButtonGoValue", _GO , $mainhtml);
			$mainhtml = replace("ButtonBackName", "back3" , $mainhtml);
			$mainhtml = replace("ButtonBackValue", _BACK , $mainhtml);
			$mainhtml = replace("ButtonDeleteName", "delete3" , $mainhtml);
			$mainhtml = replace("ButtonDeleteValue", _DELETE , $mainhtml);
		}

		else {
			$mainhtml = file_get_contents("tpl/insertbet2.inc");
			$i = 0;
			foreach ($pos_array as $pos_name){
				$pos_part .= getTemplatePart("Possibilities", $mainhtml);
				$pos_part = replace("Pos", _POSSIBILITY." ".($i+1), $pos_part); 
				$pos_part = replace("PosName", "pos".$i, $pos_part); 
				$pos_part = replace("PosValue", $pos_name, $pos_part); 
				$i++;
			}
			$new_bet->setPossibilitiesNames($pos_array);

			$_SESSION['newbet'] = serialize($new_bet);

			$mainhtml = replace("Possibilities", $pos_part, $mainhtml);

			$mainhtml = replace("MainTitle", _INSERT_BET_MAIN_TITLE2, $mainhtml);
			$mainhtml = replace("Message1", _INSERT_BET_FALSE_POS, $mainhtml);

			// replace the names and values for the buttons
			$mainhtml = replace("ButtonGoName", "go2", $mainhtml);
			$mainhtml = replace("ButtonGoValue", _GO , $mainhtml);
			$mainhtml = replace("ButtonBackName", "back2" , $mainhtml);
			$mainhtml = replace("ButtonBackValue", _BACK , $mainhtml);
			$mainhtml = replace("ButtonDeleteName", "delete2" , $mainhtml);
			$mainhtml = replace("ButtonDeleteValue", _DELETE , $mainhtml);
		}
	}

	// after the third go click (check inputs)
	elseif ($_POST['go3'] == _GO){

		$new_bet = unserialize($_SESSION['newbet']);

		// unlock F5
		$session->unlockF5();

		if (!isset($_POST['category'] ))
			$_POST['category'] = "";
			
		if (!($_POST['category'] == "")){
			
			$new_bet = unserialize($_SESSION['newbet']);
			$new_bet->setCategoryId($_POST['category']);

			$category = $db_mapper->getCategory($_POST['category']);
			$cat_name = $category->getCategoryName();
			
			$new_bet->setCategoryName($cat_name);
			$new_bet->setCategoryId($_POST['category']);
			$_SESSION['newbet'] = serialize($new_bet);
	
			// preview the bet
			$mainhtml = file_get_contents("tpl/insertbet4.inc");

			$mainhtml = replace("CategoryName", $new_bet->getCategoryName(), $mainhtml);
			$mainhtml = replace("Title", " ".$new_bet->getBetTitle(), $mainhtml);
			$mainhtml = replace("SubTitle", $new_bet->getSubtitle(), $mainhtml);
			$mainhtml = replace("Until", _ACTIVE_UNTIL , $mainhtml);
			$mainhtml = replace("Start", strftime("%d/%m/%Y - %H:%M:%S Uhr" , strtotime($new_bet->getBetStartTime())), $mainhtml);
			$mainhtml = replace("End", strftime("%d/%m/%Y - %H:%M:%S Uhr" , strtotime($new_bet->getBetEndTime())), $mainhtml);
			$possibilities_html_part = "";
	
			// the right image for the bet
			if ($new_bet->getBetImage()){
				$dir = "/images/bet/tmp/";
				$image_src = $dir.$new_bet->getBetId().".jpg";
			}
			else {
				$dir = "/images/category/";
				if ($category->getCategoryImage())
					$image_src = $dir.$category->getCategoryId().".jpg";
				else
					$image_src = $dir."no_image.gif";
			}
	
			$mainhtml = replace("ImageSrc", $image_src, $mainhtml);
	
			// replaces the ids and names of the Possibilities
			$possibilities_names = $new_bet->getPossibilitiesNames();
			foreach($possibilities_names as $possibility_name){
				$possibilities_html_part .= getTemplatePart("Possibilities", $mainhtml);
				$possibilities_html_part = replace("PossibilityName", $possibility_name, $possibilities_html_part);
			}
			$mainhtml = replace("Possibilities", $possibilities_html_part, $mainhtml);
			$mainhtml = replace("Message1", _INSERT_BET_MESSAGE4, $mainhtml);
			
						// replace the names and values for the buttons
			$mainhtml = replace("ButtonGoName", "go4", $mainhtml);
			$mainhtml = replace("ButtonGoValue", _GO , $mainhtml);
			$mainhtml = replace("ButtonBackName", "back4" , $mainhtml);
			$mainhtml = replace("ButtonBackValue", _BACK , $mainhtml);
			$mainhtml = replace("ButtonDeleteName", "delete4" , $mainhtml);
			$mainhtml = replace("ButtonDeleteValue", _DELETE , $mainhtml);
		
		}
		// no category selected (error)
		else {
			$mainhtml = file_get_contents("tpl/insertbet3.inc");
			$mainhtml = replace("MainTitle", _INSERT_BET_MAIN_TITLE3, $mainhtml);
			$mainhtml = replace("Message1", _INSERT_BET_CHOOSE_CAT, $mainhtml);
			
			$categories = $db_mapper->getAllCategories();

			// display categories
			$cat_html_part = "";
			foreach($categories as $category) {
				$cat_html_part .= getTemplatePart("Categories", $mainhtml);
				$cat_html_part = replace("CategoryID", $category->getCategoryId(), $cat_html_part);
				
				if ($category->getCategoryImage())
					$cat_html_part = replace("CategoryImageSrc", "images/category/".$category->getCategoryId().".jpg", $cat_html_part);
				else
					$cat_html_part = replace("CategoryImageSrc", "images/category/no_image.gif", $cat_html_part);
				
				$cat_html_part = replace("CategoryName", $category->getCategoryName(), $cat_html_part);
				$cat_html_part = replace("CategoryDescription", $category->getCategoryDescription(), $cat_html_part);
			}

			$mainhtml = replace("Categories", $cat_html_part, $mainhtml);

			// replace the names and values for the buttons
			$mainhtml = replace("ButtonGoName", "go3", $mainhtml);
			$mainhtml = replace("ButtonGoValue", _GO , $mainhtml);
			$mainhtml = replace("ButtonBackName", "back3" , $mainhtml);
			$mainhtml = replace("ButtonBackValue", _BACK , $mainhtml);
			$mainhtml = replace("ButtonDeleteName", "delete3" , $mainhtml);
			$mainhtml = replace("ButtonDeleteValue", _DELETE , $mainhtml);
			
		}
	}

	// show inserted bet
	elseif (($_POST['go4'] == _GO) && ($session->getF5() == "unlocked")){

		$session->lockF5();

		$new_bet = unserialize($_SESSION['newbet']);

		$new_bet->setBetAutorId($user->getUserId());
		$new_bet_id = $db_mapper->insertBet($new_bet);

		if ($new_bet->getBetImage() == 1){
			$file = 'images/bet/tmp/'.$new_bet->getBetId().".jpg";
			$newfile = 'images/bet/'.$new_bet_id.".jpg";
			if (!copy($file, $newfile)) {
				echo "failed to copy $file...\n";
			}
			unlink($file);
		}
		// preview
		$new_bet = $db_mapper->getBet($new_bet_id);

		$mainhtml = file_get_contents("tpl/insertbet5.inc");

		$mainhtml = replace("MainTitle", _INSERT_BET_MAIN_TITLE5, $mainhtml);
		$mainhtml = replace("Message1", _INSERT_BET_VISIBLE_SOON, $mainhtml);
		//$mainhtml = replace("Message2", _INSERT_BET_MESSAGE_NEW_CAT, $mainhtml);

		$mainhtml = replace("CategoryName", $new_bet->getCategoryName(), $mainhtml);
		$mainhtml = replace("Title", " ".$new_bet->getBetTitle(), $mainhtml);
		$mainhtml = replace("SubTitle", $new_bet->getSubtitle(), $mainhtml);
		$mainhtml = replace("Until", _ACTIVE_UNTIL , $mainhtml);
		$mainhtml = replace("Start", strftime("%d/%m/%Y - %H:%M:%S Uhr" , strtotime($new_bet->getBetStartTime())), $mainhtml);
		$mainhtml = replace("End", strftime("%d/%m/%Y - %H:%M:%S Uhr" , strtotime($new_bet->getBetEndTime())), $mainhtml);
		$possibilities_html_part = "";

		// which is the right image for the bet
		if ($new_bet->getBetImage() == 1){
			$dir = "images/bet/";
			$image_src = $dir.$new_bet->getBetId().".jpg";
		}
		else {
			$cat = $db_mapper->getCategory($new_bet->getCategoryId());
			$dir = "images/category/";
			if ($cat->getCategoryImage())
				$image_src = $dir.$new_bet->getCategoryId().".jpg";
			else
				$image_src = $dir."no_image.gif";
		}

		$mainhtml = replace("ImageSrc", $image_src, $mainhtml);
		// replaces the ids, names and quotes of the Possibilities
		$possibilities_names = $new_bet->getPossibilitiesNames();

		foreach($possibilities_names as $possibility_name){
			$possibilities_html_part .= getTemplatePart("Possibilities", $mainhtml);
			$possibilities_html_part = replace("PossibilityName", $possibility_name, $possibilities_html_part);
		}
		$mainhtml = replace("Possibilities", $possibilities_html_part, $mainhtml);
		$mainhtml = replace("BackHome", _BACK_HOME, $mainhtml);

		// clear the bet from the session
		$new_bet = new Bet("", "", "",  "", "", "", "", "", "", "", "", "", "");
		$_SESSION['newbet'] = serialize($new_bet);

		$logger->writeLog($username, _BET_INSERTED);
	}

	// edit title, subtitle and amount of possibilities again
	elseif (($_POST['back2'] == _BACK) ||
			($_POST['continue2'] == _CONTINUE_BET)){
		$mainhtml = file_get_contents("tpl/insertbet1.inc");
		$mainhtml = replace("MainTitle", _INSERT_BET_MAIN_TITLE1, $mainhtml);
		$mainhtml = replace("Message1", _INSERT_BET_MESSAGE1, $mainhtml);
		$mainhtml = replace("Title", _INSERT_BET_TITLE, $mainhtml);
		$mainhtml = replace("SubTitle", _INSERT_BET_SUBTITLE, $mainhtml);
		$mainhtml = replace("StartDate", _INSERT_BET_STARTDATE, $mainhtml);
		$mainhtml = replace("EndDate", _INSERT_BET_ENDDATE, $mainhtml);
		$mainhtml = replace("BetImage", _INSERT_BET_IMAGE, $mainhtml);
		$mainhtml = replace("PosNr", _INSERT_BET_POS_NR, $mainhtml);
		$mainhtml = replace("ButtonGoName", "go1", $mainhtml);
		$mainhtml = replace("ButtonGoValue", _GO , $mainhtml);
		$mainhtml = replace("ButtonDeleteName", "delete1" , $mainhtml);
		$mainhtml = replace("ButtonDeleteValue", _DELETE , $mainhtml);

		$new_bet = unserialize($_SESSION['newbet']);

		$mainhtml = replace("InsTitle", $new_bet->getBetTitle(), $mainhtml);
		$mainhtml = replace("InsSubTitle", $new_bet->getSubtitle() , $mainhtml);
		$mainhtml = replace("InsStartDate", $new_bet->getBetStartTime() , $mainhtml);
		$mainhtml = replace("InsEndDate", $new_bet->getBetEndTime(), $mainhtml);
		$mainhtml = replace("InsBetImage", "", $mainhtml);
		
		$pos_amount = count($new_bet->getPossibilitiesIds());
		// fill select-menu
		$select_item = "";
		for ($k = 2; $k <= MAX_BET_POS_AMOUNT; $k++){

			$select_item .= getTemplatePart("SelectItem", $mainhtml);
			
			if ($k == $pos_amount)
				$select_item = replace("ItemSelected", "selected=\"selected\"", $select_item);
			else
				$select_item = replace("ItemSelected", "", $select_item);
				
			$select_item = replace("SelectItemValue", $k, $select_item);
			$select_item = replace("SelectItemName", $k, $select_item);
		}
		
		$mainhtml = replace("SelectItem", $select_item, $mainhtml);
	}

	// edit possibilities again
	elseif (($_POST['back3'] == _BACK) ||
			($_POST['continue3'] == _CONTINUE_BET)){
		$mainhtml = file_get_contents("tpl/insertbet2.inc");

		$new_bet = unserialize($_SESSION['newbet']);
		$pos_array = $new_bet->getPossibilitiesNames(); 

		$i = 0;	
		$pos_part = "";
		foreach ($pos_array as $pos_name){
			$pos_part .= getTemplatePart("Possibilities", $mainhtml);
			$pos_part = replace("Pos", _POSSIBILITY." ".($i+1), $pos_part); 
			$pos_part = replace("PosName", "pos".$i, $pos_part); 
			$pos_part = replace("PosValue", $pos_name, $pos_part); 
			$i++;
		}

		$mainhtml = replace("Possibilities", $pos_part, $mainhtml);
		$mainhtml = replace("MainTitle", _INSERT_BET_MAIN_TITLE2, $mainhtml);
		$mainhtml = replace("Message1", _INSERT_BET_MESSAGE2, $mainhtml);
		$mainhtml = replace("ButtonGoName", "go2", $mainhtml);
		$mainhtml = replace("ButtonGoValue", _GO , $mainhtml);
		$mainhtml = replace("ButtonBackName", "back2" , $mainhtml);
		$mainhtml = replace("ButtonBackValue", _BACK , $mainhtml);
		$mainhtml = replace("ButtonDeleteName", "delete2" , $mainhtml);
		$mainhtml = replace("ButtonDeleteValue", _DELETE , $mainhtml);

	}

	// choose category again
	elseif (($_POST['back4'] == _BACK) ||
			($_POST['continue4'] == _CONTINUE_BET)){

		$new_bet = unserialize($_SESSION['newbet']);

		$mainhtml = file_get_contents("tpl/insertbet3.inc");
		$mainhtml = replace("MainTitle", _INSERT_BET_MAIN_TITLE3, $mainhtml);
		$mainhtml = replace("Message1", _INSERT_BET_FALSE, $mainhtml);
		$mainhtml = replace("Message2", _INSERT_BET_MESSAGE_NEW_CAT, $mainhtml);

		$categories = $db_mapper->getAllCategories();

		// display categories
		foreach($categories as $category) {
			$cat_html_part .= getTemplatePart("Categories", $mainhtml);
			$cat_html_part = replace("CategoryID", $category->getCategoryId(), $cat_html_part);
			
			if ($category->getCategoryImage())
				$cat_html_part = replace("CategoryImageSrc", "images/category/".$category->getCategoryId().".jpg", $cat_html_part);
			else
				$cat_html_part = replace("CategoryImageSrc", "images/category/no_image.gif", $cat_html_part);
			
			$cat_html_part = replace("CategoryName", $category->getCategoryName(), $cat_html_part);
			$cat_html_part = replace("CategoryDescription", $category->getCategoryDescription(), $cat_html_part);
		}

		$mainhtml = replace("SelectItem", $select_item, $mainhtml);

		$mainhtml = replace("Categories", $cat_html_part, $mainhtml);

		// replace the names and values for the buttons
		$mainhtml = replace("ButtonGoName", "go3", $mainhtml);
		$mainhtml = replace("ButtonGoValue", _GO , $mainhtml);
		$mainhtml = replace("ButtonBackName", "back3" , $mainhtml);
		$mainhtml = replace("ButtonBackValue", _BACK , $mainhtml);
		$mainhtml = replace("ButtonDeleteName", "delete3" , $mainhtml);
		$mainhtml = replace("ButtonDeleteValue", _DELETE , $mainhtml);

	}

	elseif (($_POST['delete1'] == _DELETE) ||
			($_POST['delete_def'] == _DELETE_BET)){
		$mainhtml = file_get_contents("tpl/insertbet1.inc");
		$mainhtml = replace("MainTitle", _INSERT_BET_MAIN_TITLE1, $mainhtml);
		$mainhtml = replace("Message1", _INSERT_BET_MESSAGE1, $mainhtml);
		$mainhtml = replace("Title", _INSERT_BET_TITLE, $mainhtml);
		$mainhtml = replace("SubTitle", _INSERT_BET_SUBTITLE, $mainhtml);
		$mainhtml = replace("StartDate", _INSERT_BET_STARTDATE, $mainhtml);
		$mainhtml = replace("EndDate", _INSERT_BET_ENDDATE, $mainhtml);
		$mainhtml = replace("BetImage", _INSERT_BET_IMAGE, $mainhtml);
		$mainhtml = replace("PosNr", _INSERT_BET_POS_NR, $mainhtml);
		$mainhtml = replace("ButtonGoName", "go1", $mainhtml);
		$mainhtml = replace("ButtonGoValue", _GO , $mainhtml);
		$mainhtml = replace("ButtonDeleteValue", _DELETE , $mainhtml);

		$new_bet = unserialize($_SESSION['newbet']);
		
		// fill select-menu
		$select_item = "";
		for ($k = 2; $k <= MAX_BET_POS_AMOUNT; $k++){

			$select_item .= getTemplatePart("SelectItem", $mainhtml);
			
			if ($k == DEFAULT_POS_AMOUNT)
				$select_item = replace("ItemSelected", "selected=\"selected\"", $select_item);
			else
				$select_item = replace("ItemSelected", "", $select_item);
				
			$select_item = replace("SelectItemValue", $k, $select_item);
			$select_item = replace("SelectItemName", $k, $select_item);
		}
		
		$mainhtml = replace("SelectItem", $select_item, $mainhtml);

		$mainhtml = replace("InsTitle", "", $mainhtml);
		$mainhtml = replace("InsSubTitle", "", $mainhtml);
		$mainhtml = replace("InsStartDate", "", $mainhtml);
		$mainhtml = replace("InsEndDate", "", $mainhtml);
		$mainhtml = replace("InsBetImage", "", $mainhtml);
		$mainhtml = replace("InsPosNr", "", $mainhtml);
	}

	elseif ($_POST['delete2'] == _DELETE){
		$mainhtml = file_get_contents("tpl/insertbet_del.inc");
		$mainhtml = replace("MainTitle", _INSERT_BET_DELETE, $mainhtml);
		$mainhtml = replace("Message1", _INSERT_BET_ASK_DELETE, $mainhtml);
		$mainhtml = replace("ButtonDeleteValue", _DELETE_BET, $mainhtml);
		$mainhtml = replace("ButtonDeleteName", "delete_def", $mainhtml);
		$mainhtml = replace("ButtonContinueValue", _CONTINUE_BET, $mainhtml);
		$mainhtml = replace("ButtonContinueName", "continue2", $mainhtml);
	}

	elseif ($_POST['delete3'] == _DELETE){
		$mainhtml = file_get_contents("tpl/insertbet_del.inc");
		$mainhtml = replace("MainTitle", _INSERT_BET_DELETE, $mainhtml);
		$mainhtml = replace("Message1", _INSERT_BET_ASK_DELETE, $mainhtml);
		$mainhtml = replace("ButtonDeleteValue", _DELETE_BET, $mainhtml);
		$mainhtml = replace("ButtonDeleteName", "delete_def", $mainhtml);
		$mainhtml = replace("ButtonContinueValue", _CONTINUE_BET, $mainhtml);
		$mainhtml = replace("ButtonContinueName", "continue3", $mainhtml);
	}

	elseif ($_POST['delete4'] == _DELETE){
		$mainhtml = file_get_contents("tpl/insertbet_del.inc");
		$mainhtml = replace("MainTitle", _INSERT_BET_DELETE, $mainhtml);
		$mainhtml = replace("Message1", _INSERT_BET_ASK_DELETE, $mainhtml);
		$mainhtml = replace("ButtonDeleteValue", _DELETE_BET, $mainhtml);
		$mainhtml = replace("ButtonDeleteName", "delete_def", $mainhtml);
		$mainhtml = replace("ButtonContinueValue", _CONTINUE_BET, $mainhtml);
		$mainhtml = replace("ButtonContinueName", "continue4", $mainhtml);
	}

	// if the user visits the site for the first time
	else {
		
		$categories = $db_mapper->getAllCategories();

		if (count($categories) >= 1){

			$new_bet = new Bet("", "", "",  "", "", "", "", "", "", "", "", "", "");
			$_SESSION['newbet'] = serialize($new_bet);
	
	
			$mainhtml = file_get_contents("tpl/insertbet1.inc");
			// show the blank form
			$mainhtml = replace("MainTitle", _INSERT_BET_MAIN_TITLE1, $mainhtml);
			$mainhtml = replace("Message1", _INSERT_BET_MESSAGE1, $mainhtml);
			$mainhtml = replace("Title", _INSERT_BET_TITLE, $mainhtml);
			$mainhtml = replace("SubTitle", _INSERT_BET_SUBTITLE, $mainhtml);
			$mainhtml = replace("StartDate", _INSERT_BET_STARTDATE, $mainhtml);
			$mainhtml = replace("EndDate", _INSERT_BET_ENDDATE, $mainhtml);
			$mainhtml = replace("BetImage", _INSERT_BET_IMAGE, $mainhtml);
			$mainhtml = replace("PosNr", _INSERT_BET_POS_NR, $mainhtml);
			
			// fill select-menu
			$select_item = "";
			for ($k = 2; $k <= MAX_BET_POS_AMOUNT; $k++){

				$select_item .= getTemplatePart("SelectItem", $mainhtml);
				
				if ($k == DEFAULT_POS_AMOUNT)
					$select_item = replace("ItemSelected", "selected=\"selected\"", $select_item);
				else
					$select_item = replace("ItemSelected", "", $select_item);
					
				$select_item = replace("SelectItemValue", $k, $select_item);
				$select_item = replace("SelectItemName", $k, $select_item);
			}
			
			$mainhtml = replace("SelectItem", $select_item, $mainhtml);
	
			// replace the values of the input fields with nothing
			$mainhtml = replace("InsTitle", "", $mainhtml);
			$mainhtml = replace("InsSubTitle", "", $mainhtml);
			$mainhtml = replace("InsStartDate", "", $mainhtml);
			$mainhtml = replace("InsEndDate", "", $mainhtml);
			$mainhtml = replace("InsBetImage", "", $mainhtml);
			$mainhtml = replace("InsPosNr", "", $mainhtml);
	
			$mainhtml = replace("ButtonGoName", "go1", $mainhtml);
			$mainhtml = replace("ButtonGoValue", _GO , $mainhtml);
			$mainhtml = replace("ButtonDeleteName", "delete1" , $mainhtml);
			$mainhtml = replace("ButtonDeleteValue", _DELETE , $mainhtml);
			$mainhtml = replace("CategoriesTitle", _INSERT_BET_CATTITLE, $mainhtml);
		}
		// no category exists :-(
		else {
			$mainhtml = file_get_contents("tpl/insertbet0.inc");
			$mainhtml = replace("MainTitle", _INSERT_BET_MAIN_TITLE1, $mainhtml);
			$mainhtml = replace("Message1", _INSERT_BET_NO_CAT, $mainhtml);
			$mainhtml = replace("InsertCategory", _INSERT_CAT_TITLE1, $mainhtml);
		}
	}
	}
	else {
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
