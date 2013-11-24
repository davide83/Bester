<?php
/*
 * Created on 03.09.2005
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
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
	($user->getStatus() == "administrator") ||
	($user->getStatus() == "betmaster")) {

	$mainhtml = file_get_contents("tpl/categoryedit.inc");
	$mainhtml = replace("MainTitle", _CATEGORY_EDIT, $mainhtml);


	$id = htmlspecialchars($_GET['id']);
	$action = htmlspecialchars($_GET['ac']);

	$cat = $db_mapper->getCategory($id);

	$mainhtml = replace("NewCategoryName", _INSERT_BET_NEW_CATNAME, $mainhtml);
	$mainhtml = replace("InsNewCategoryName", $cat->getCategoryName(), $mainhtml);
	$mainhtml = replace("NewCategoryDesc", _INSERT_BET_NEW_CATDESC, $mainhtml);
	$mainhtml = replace("InsNewCategoryDesc", $cat->getCategoryDescription(), $mainhtml);
	$mainhtml = replace("NewCategoryImage", _INSERT_BET_NEW_CATIMAGE, $mainhtml);

	$mainhtml = replace("ButtonGoValue", _EDIT , $mainhtml);
	$mainhtml = replace("ButtonDeleteValue", _DELETE , $mainhtml);
	$mainhtml = replace("BackList", _BACK_LIST, $mainhtml);

	// first step
	if ($_POST['edit'] == _EDIT){
		$dir = 'images/category/tmp/';
		$filename = $_FILES['newcategoryimage']['name'];
		$filetype = $_FILES['newcategoryimage']['type'];
		$filesize = $_FILES['newcategoryimage']['size'];
		

		if ((strlen($_POST['newcategoryname']) >= MIN_CAT_NAME_LENGTH)){
			
			$cat->setCategoryName($_POST['newcategoryname']);
			$cat->setCategoryDescription($_POST['newcategorydesc']);
			
			$msg = _CATEGORY_INPUTS_RIGHT;
			$preview = 1;
						
			// new image
			if ($filename != ""){
				$image_name = rand(1000,9999);
				if(($filesize < MAX_IMAGE_FILESIZE) && 
						($filetype == "image/jpeg") &&
						move_uploaded_file($_FILES['newcategoryimage']['tmp_name'],
							$dir.$_FILES['newcategoryimage']['name'])){ 
					resizeAndSaveImage($dir, $filename, $image_name.".jpg");
					$cat->setCategoryImage(1);
				}
				else {
					$msg = _CATEGORY_IMG_FALSE;
					$preview = 0;
				}
			}
		}

		// preview
		if ($preview == 1) {

			// preview
			$mainhtml = file_get_contents("tpl/category_preview.inc");
			$mainhtml = replace("CategoryID", $cat->getCategoryID(), $mainhtml);

			// new image
			if ($filename != ""){
				$mainhtml = replace("CategoryImageSrc", "images/category/tmp/".$image_name.".jpg", $mainhtml);
			}
			// show old image
			elseif ($cat->getCategoryImage()) {
				$mainhtml = replace("CategoryImageSrc", "images/category/".$cat->getCategoryId().".jpg", $mainhtml);
			}
			// has no image
			else {
				$mainhtml = replace("CategoryImageSrc", "images/category/no_image.gif", $mainhtml);
			}

			$mainhtml = replace("CategoryName", $cat->getCategoryName(), $mainhtml);
			$mainhtml = replace("CategoryDescription", $cat->getCategoryDescription(), $mainhtml);

			$link1 = getTemplatePart("Link", $mainhtml);
			$link1 = replace("Action", "ins", $link1);
			$link1 = replace("ActionName", _STORE, $link1);
			$link1 = replace("CategoryId", $cat->getCategoryId(), $link1);

			$link2 = getTemplatePart("Link", $mainhtml);
			$link2 = replace("Action", "del", $link2);
			$link2 = replace("ActionName", _DELETE, $link2);
			$link2 = replace("CategoryId", $cat->getCategoryId(), $link2);

			$link3 = getTemplatePart("Link", $mainhtml);
			$link3 = replace("Action", "edit", $link3);
			$link3 = replace("ActionName", _REEDIT, $link3);
			$link3 = replace("CategoryId", $cat->getCategoryId(), $link3);

			$links = $link3.$link2.$link1;

			$mainhtml = replace("Link", $links, $mainhtml);

			// store in session
			$_SESSION['newcat'] = serialize($cat);
			$_SESSION['imgname'] = serialize($image_name);
		}
		else {
			$msg = _CATEGORY_INPUTS_FALSE;
		}
	}

	// confirmation step
	elseif ($action != "") {
		switch ($action) {
			case "ins": {
				// take from session
				$cat = unserialize($_SESSION['newcat']);
				$image_name = unserialize($_SESSION['imgname']);
				
				$mainhtml = file_get_contents("tpl/category_preview.inc");
				
				// new image
				if ($image_name >= 1000){
					$file = 'images/category/tmp/'.$image_name.".jpg";
					$newfile = 'images/category/'.$cat->getCategoryId().".jpg";
					if (!copy($file, $newfile)) {
						$msg = _COPY_ERROR;
					}
					else {
						$db_mapper->updateCategory($cat);
						unlink($file);
						$logger->writeLog($user->getUsername(), _CATEGORY_EDITED);
						$msg = _SUC_CATEGORY_EDIT;
						$mainhtml = replace("CategoryImageSrc", "images/category/".$cat->getCategoryid().".jpg", $mainhtml);
					}
				}
				// old image remains
				else {
					$db_mapper->updateCategory($cat);
					$logger->writeLog($user->getUsername(), _CATEGORY_EDITED);
					$msg = _SUC_CATEGORY_EDIT;
					
					if ($cat->getCategoryImage())
						$mainhtml = replace("CategoryImageSrc", "images/category/".$cat->getCategoryid().".jpg", $mainhtml);
					else
						$mainhtml = replace("CategoryImageSrc", "images/category/no_image.gif", $mainhtml);
				}
				
				$mainhtml = replace("Message1", $msg, $mainhtml);
				$mainhtml = replace("CategoryID", $cat->getCategoryID(), $mainhtml);
				$mainhtml = replace("CategoryName", $cat->getCategoryName(), $mainhtml);
				$mainhtml = replace("CategoryDescription", $cat->getCategoryDescription(), $mainhtml);
				$mainhtml = replace("Link", "", $mainhtml);
				$mainhtml = replace("BackList", _BACK_LIST, $mainhtml);
				break;
			}
			case "del" : {
				$image_name = unserialize($_SESSION['imagename']);
				// delete the file
				$file = 'images/category/tmp/'.$image_name.".jpg";
				unlink($file);
				unset($cat);
			}
			case "edit": {
				break;
			}
		}
	}
	$mainhtml = replace("Message1", $msg, $mainhtml);
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
