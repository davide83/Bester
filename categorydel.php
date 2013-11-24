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

if ($user->getStatus() == "administrator") {
	// ask
	if (isset($_GET['id']) && ($_POST['go'] == "") && ($_POST['back'] == "")){
		if (is_numeric(htmlspecialchars($_GET['id']))){
			$id = $_GET['id'];
			$cat = $db_mapper->getCategory($id);
			// preview
			$mainhtml = file_get_contents("tpl/catdel1.inc");

			$mainhtml = replace("Message1", _SURE_DEL_CAT, $mainhtml);
			
			if ($cat->getCategoryImage())
				$mainhtml = replace("CategoryImageSrc", "images/category/".$id.".jpg", $mainhtml);
			else
				$mainhtml = replace("CategoryImageSrc", "images/category/no_image.gif", $mainhtml);
				
			$mainhtml = replace("CategoryName", $cat->getCategoryName(), $mainhtml);
			$mainhtml = replace("CategoryDescription", $cat->getCategoryDescription(), $mainhtml);
			$mainhtml = replace("ButtonGoValue", _GO , $mainhtml);
			$mainhtml = replace("ButtonGoName", "go" , $mainhtml);
			$mainhtml = replace("ButtonBackValue", _BACK, $mainhtml);
			$mainhtml = replace("ButtonBackName", "back" , $mainhtml);
		}
		else {
			header("Location:index.php");
		}
	}

	// delete TODO: lock F5
	elseif (($_POST['go'] == _GO) && (isset($_GET['id']))){
		if (is_numeric(htmlspecialchars($_GET['id']))){
			$mainhtml = file_get_contents("tpl/catdel2.inc");
			if (($db_mapper->deleteCategory($_GET['id']) == true)){
				$logger->writeLog($user->getUsername(), _DEL_CAT_ID.$_GET['id']);
				$mainhtml = replace("Message1", _DEL_CAT_SUC, $mainhtml);
				$mainhtml = replace("Back", _BACK_LIST, $mainhtml);
			}
			else {
				$mainhtml = replace("Message1", _DEL_CAT_FAIL, $mainhtml);
				$mainhtml = replace("Back", _BACK_LIST, $mainhtml);
			}
			$mainhtml = replace("MainTitle", _DEL_CAT, $mainhtml);
		}
		else {
			header("Location:index.php");
		}
	}
	elseif ($_POST['back'] == _BACK){
		header("Location:categorylist.php");
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
