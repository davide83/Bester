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

	$ac = htmlspecialchars(htmlspecialchars($_GET['ac']));

	// after the first "go"-click
	if ($_POST['go1'] == _GO){
		
		$cat = new Category("", "", "", "", "");
		
		$filename = $_FILES['newcategoryimage']['name'];
		$filetype = $_FILES['newcategoryimage']['type'];
		$filesize = $_FILES['newcategoryimage']['size'];
		$dir = 'images/category/tmp/';

		$ver_flag = true;

		// check the category name
		if (!(strlen(htmlspecialchars($_POST['newcategoryname'])) >= MIN_CAT_NAME_LENGTH) &&
			!(strlen(htmlspecialchars($_POST['newcategoryname'])) <= MAX_CAT_NAME_LENGTH))
			$ver_flag == false;
			
		if (!(strlen(htmlspecialchars($_POST['newcategorydesc'])) >= MIN_CAT_DESC_LENGTH) &&
			!(strlen(htmlspecialchars($_POST['newcategorydesc'])) <= MAX_CAT_DESC_LENGTH))
			$ver_flag == false;
			
		// check the image
		if ($filename != ""){
				if(($filesize < MAX_IMAGE_FILESIZE) && 
					($filetype == "image/jpeg") &&
					move_uploaded_file($_FILES['newcategoryimage']['tmp_name'],
					$dir.$_FILES['newcategoryimage']['name'])){
					$image_name = rand(1000,9999);
					$mainhtml = replace("CategoryImageSrc", "images/category/tmp/".$image_name.".jpg", $mainhtml);
					resizeAndSaveImage($dir, $filename, $image_name.".jpg");
					$cat->setCategoryImage("1");
				}
				else
					$ver_flag = false;		
		}
		
		// correct inputs -> preview
		if ($ver_flag){
		
			$cat->setCategoryName($_POST['newcategoryname']);	
			$cat->setCategoryDescription($_POST['newcategorydesc']);
			
			$mainhtml = file_get_contents("tpl/insertcat2.inc");
			
			if ($cat->getCategoryImage())
				$mainhtml = replace("CategoryImageSrc", $dir.$image_name.".jpg", $mainhtml);
			else {
				$mainhtml = replace("CategoryImageSrc", "images/category/no_image.gif", $mainhtml);
				$cat->setCategoryImage(0);
			}
			
			$msg = _CATEGORY_INPUTS_RIGHT;

			$mainhtml = replace("CategoryName", $cat->getCategoryName(), $mainhtml);
			$mainhtml = replace("CategoryDescription", $cat->getCategoryDescription(), $mainhtml);

			$mainhtml = replace("ButtonGoValue", _GO , $mainhtml);
			$mainhtml = replace("ButtonGoName", "go2" , $mainhtml);
			$mainhtml = replace("ButtonDeleteValue", _DELETE , $mainhtml);
			$mainhtml = replace("ButtonBackValue", _BACK, $mainhtml);
			$mainhtml = replace("ButtonDeleteName", "del" , $mainhtml);
			$mainhtml = replace("ButtonBackName", "back2" , $mainhtml);
			
			$_SESSION['newcat'] = serialize($cat);
			$_SESSION['imgname'] = serialize($image_name);
		}
		// wrong inputs
		else {
			$msg = _INSERT_CAT_WRONG_INPUTS;

			$mainhtml = file_get_contents("tpl/insertcat1.inc");
			$mainhtml = replace("ButtonGoName", "go1", $mainhtml);
			$mainhtml = replace("ButtonGoValue", _GO , $mainhtml);
			$mainhtml = replace("ButtonDeleteName", "delete1" , $mainhtml);
			$mainhtml = replace("ButtonDeleteValue", _DELETE , $mainhtml);
			$mainhtml = replace("CategoryName", _INSERT_BET_NEW_CATNAME, $mainhtml);
			$mainhtml = replace("InsCategoryName", $_POST['newcategoryname'], $mainhtml);
			$mainhtml = replace("CategoryDesc", _INSERT_BET_NEW_CATDESC, $mainhtml);
			$mainhtml = replace("InsCategoryDesc", $_POST['newcategorydesc'], $mainhtml);
			$mainhtml = replace("CategoryImage", _INSERT_BET_NEW_CATIMAGE, $mainhtml);
			$mainhtml = replace("MainTitle", _INSERT_CAT_TITLE1, $mainhtml);
			
		}
	}
	
	// after the 2nd "go"-click
	elseif ($_POST['go2'] == _GO){
		
		// take from session
		$cat = unserialize($_SESSION['newcat']);
		$image_name = unserialize($_SESSION['imgname']);

		$db_mapper->insertCategory($cat);
		
		$mainhtml = file_get_contents("tpl/insertcat3.inc");
		
		if ($cat->getCategoryImage()){
			$file = 'images/category/tmp/'.$image_name.".jpg";
			$newfile = 'images/category/'.mysql_insert_id().".jpg";
			
			if (!copy($file, $newfile)) {
				error_catcher();
			}
			unlink($file);
		
			$mainhtml = replace("CategoryImageSrc", "images/category/".mysql_insert_id().".jpg", $mainhtml);
		
		}
		else {
			$mainhtml = replace("CategoryImageSrc", "images/category/no_image.gif", $mainhtml);
		}
		
		$mainhtml = replace("CategoryID", $cat->getCategoryID(), $mainhtml);
		$mainhtml = replace("CategoryName", $cat->getCategoryName(), $mainhtml);
		$mainhtml = replace("CategoryDescription", $cat->getCategoryDescription(), $mainhtml);
		$mainhtml = replace("Link", "", $mainhtml);
		$mainhtml = replace("BackList", _BACK_LIST, $mainhtml);

		$logger->writeLog($username, _CATEGORY_INSERTED);
		$msg = _INSERT_CAT_SUCCESS;
		unset($_SESSION['new_cat']);
	}

	elseif ($_POST['del'] == _DELETE){
		$image_name = unserialize($_SESSION['imgname']);
		$file = 'images/category/tmp/'.$image_name.".jpg";
		if (strlen($image_name) > 0)
			unlink($file);
		unset($cat);
		header("Location:categorylist.php");
	}

	elseif ($_POST['back2'] == _BACK){

		$cat = unserialize($_SESSION['newcat']);
		$mainhtml = file_get_contents("tpl/insertcat1.inc");
		// show the blank form
		$mainhtml = replace("MainTitle", _INSERT_CAT_TITLE1, $mainhtml);

		$mainhtml = replace("CategoryName", _INSERT_BET_NEW_CATNAME, $mainhtml);
		$mainhtml = replace("InsCategoryName", $cat->getCategoryName(), $mainhtml);
		$mainhtml = replace("CategoryDesc", _INSERT_BET_NEW_CATDESC, $mainhtml);
		$mainhtml = replace("InsCategoryDesc", $cat->getCategoryDescription(), $mainhtml);
		$mainhtml = replace("CategoryImage", _INSERT_BET_NEW_CATIMAGE, $mainhtml);

		$mainhtml = replace("ButtonGoValue", _GO , $mainhtml);
		$mainhtml = replace("ButtonGoName", "go1" , $mainhtml);
		$mainhtml = replace("ButtonDeleteValue", _DELETE , $mainhtml);
		$mainhtml = replace("ButtonDeleteName", "del" , $mainhtml);
	}
	// if the user visits the site for the first time
	else {

		if (isset($_SESSION['new_cat'])){
			$cat = unserialize($_SESSION['newcat']);
		}
		else{
			$cat = new Category("", "", "","",0);
		}
		$mainhtml = file_get_contents("tpl/insertcat1.inc");
		// show the blank form
		$mainhtml = replace("MainTitle", _INSERT_CAT_TITLE1, $mainhtml);

		$mainhtml = replace("CategoryName", _INSERT_BET_NEW_CATNAME, $mainhtml);
		$mainhtml = replace("InsCategoryName", $cat->getCategoryName(), $mainhtml);
		$mainhtml = replace("CategoryDesc", _INSERT_BET_NEW_CATDESC, $mainhtml);
		$mainhtml = replace("InsCategoryDesc", $cat->getCategoryDescription(), $mainhtml);
		$mainhtml = replace("CategoryImage", _INSERT_BET_NEW_CATIMAGE, $mainhtml);

		$mainhtml = replace("ButtonGoValue", _GO , $mainhtml);
		$mainhtml = replace("ButtonGoName", "go1" , $mainhtml);
		$mainhtml = replace("ButtonDeleteValue", _DELETE , $mainhtml);
		$mainhtml = replace("ButtonDeleteName", "del" , $mainhtml);
	}
	$mainhtml = replace("Message1", $msg, $mainhtml);
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
