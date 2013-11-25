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
	
	if (!isset($_POST['preview']))
		$_POST['preview'] = "";
		
	if (!isset($_POST['insert']))
		$_POST['insert'] = "";

	if (!isset($_POST['title']))
		$_POST['title'] = "";
	
	if (!isset($_GET['sid']))
		$_GET['sid'] = "";
	
	if (isset($_GET['sid']) && ($_POST['preview'] == "") && ($_POST['insert'] == "")){
		$sid = htmlspecialchars($_GET['sid']);
		$site = $db_mapper->getSite($sid);
		$mainhtml = file_get_contents("tpl/addsite.inc");
		$mainhtml = replace("MainTitle", $site->GetTitle(), $mainhtml);
		$mainhtml = replace("FieldTitle", $site->GetTitle(), $mainhtml);
		$mainhtml = replace("FieldText", $site->GetText(), $mainhtml);
		$mainhtml = replace("Text", $site->getText(), $mainhtml);
		$mainhtml = replace("Date", $site->getDate(), $mainhtml);
	}
	
	/*
	elseif (($_POST['insert'] == "insert") && (!isset($_GET['sid']))){
		if (isset($_POST['text']) && isset($_POST['title'])){
			$sitetitle = $_POST['title'];
			$text = $_POST['text'];
			$site = new Site("", $sitetitle, $text, $author, $date);
			$db_mapper->insertSite($site);
			$logger->writeLog($user->getUsername(), _SITE_INSERTED.$sitetitle);	
			$mainhtml = file_get_contents("tpl/showsite.inc");
			$mainhtml = replace("MainTitle", $site->GetTitle(), $mainhtml);
			$mainhtml = replace("Text", $site->getText(), $mainhtml);
			$mainhtml = replace("Date", $site->getDate(), $mainhtml);
		}
	}
	*/
	

	elseif (($_POST['insert'] == "insert") && (isset($_GET['sid']))){
		if (isset($_POST['text']) && isset($_POST['title'])){
			$sitetitle = $_POST['title'];
			$text = $_POST['text'];
			$sid = $_GET['sid'];
			$site = new Site($sid, $sitetitle, $text, "", "");
			$db_mapper->updateSite($site);
			$logger->writeLog($user->getUsername(), _SITE_CHANGED.$sitetitle);
			$mainhtml = file_get_contents("tpl/showsite.inc");
			$mainhtml = replace("MainTitle", $site->GetTitle(), $mainhtml);
			$mainhtml = replace("Text", $site->getText(), $mainhtml);
			$mainhtml = replace("Date", $site->getDate(), $mainhtml);
		}
	}
	elseif (($_POST['preview'] == "preview")){
		$sitetitle = $_POST['title'];
		$text = $_POST['text'];	
		$site = new Site("", $sitetitle, $text, $user->getUsername(), "");
		$mainhtml = file_get_contents("tpl/previewsite.inc");
		$mainhtml = replace("MainTitle", $site->GetTitle(), $mainhtml);
		$mainhtml = replace("FieldTitle", $site->GetTitle(), $mainhtml);
		$mainhtml = replace("FieldText", $site->GetText(), $mainhtml);
		$mainhtml = replace("Text", $site->getText(), $mainhtml);
		$mainhtml = replace("Date", $site->getDate(), $mainhtml);
	}
	else {
		$mainhtml = file_get_contents("tpl/addsite.inc");
		$mainhtml = replace("MainTitle", _ADD_SITE, $mainhtml);
		$mainhtml = replace("FieldTitle", "", $mainhtml);
		$mainhtml = replace("FieldText", "", $mainhtml);
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
