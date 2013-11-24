<?php
$usermenu = file_get_contents("tpl/usermenu.inc");

if (isset($_POST['username']) && isset($_POST['password'])){
	
	$username = htmlspecialchars($_POST['username']);
	$password = htmlspecialchars($_POST['password']);

	if (($username == "") || ($password == "")){
		$usermenu = replace("ShortNews", _NO_UN_OR_PW, $usermenu);
	}
	else{
		$session->login($username,$password);
		if ($session->getState()){
			$username = $session->getUsername();
			$user = $db_mapper->getUser($username);
		}
		else 
			$usermenu = replace("ShortNews", _WRONG_UN_OR_PW, $usermenu);
	}
}

if ($session->getState()){
	$usermenu = replace("LoginArea","",$usermenu);
	$user_area_part = getTemplatePart("UserArea",$usermenu);
	$user_area_part = replace("Greet", _GREET, $user_area_part);
	$user_area_part = replace("Username", $username, $user_area_part);
	$user_area_part = replace("YourBalance", _YOUR_BALANCE, $user_area_part);
	$user_area_part = replace("Balance", $user->getBalance(), $user_area_part);
	$user_area_part = replace("YourBets", _YOUR_BETS, $user_area_part);
	$user_area_part = replace("YourWins", _YOUR_WINS, $user_area_part);
	$user_area_part = replace("YourProfile", _YOUR_PROFILE, $user_area_part);
	$user_area_part = replace("EditProfile", _EDIT_PROFILE, $user_area_part);
	$usermenu = replace("UserArea", $user_area_part, $usermenu);
}
else {
	$login_area = getTemplatePart("LoginArea", $usermenu);
	$usermenu = replace("UserArea", "", $usermenu);
	$usermenu = replace("LoginArea", $login_area, $usermenu);
	$usermenu = replace("NewAccount", _NEW_ACCOUNT, $usermenu);
	$usermenu = replace("ForgotPwd", _FORGOT_PWD, $usermenu);
	$usermenu = replace("ShortNews", "", $usermenu);
}
?>
